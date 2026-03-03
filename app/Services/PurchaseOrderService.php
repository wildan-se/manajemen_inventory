<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseOrderService
{
  public function __construct(protected StockService $stockService) {}

  public function generatePoNumber(): string
  {
    $prefix = 'PO-' . now()->format('Ym') . '-';
    $last   = PurchaseOrder::where('po_number', 'like', $prefix . '%')
      ->orderByDesc('po_number')
      ->first();

    $seq = $last ? ((int) substr($last->po_number, -4)) + 1 : 1;
    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
  }

  public function create(array $data, $user): PurchaseOrder
  {
    return DB::transaction(function () use ($data, $user) {
      $po = PurchaseOrder::create([
        'po_number'    => $data['po_number'],
        'supplier_id'  => $data['supplier_id'],
        'warehouse_id' => $data['warehouse_id'],
        'expected_date' => $data['expected_date'],
        'notes'        => $data['notes'] ?? null,
        'status'       => 'draft',
        'order_date'   => now()->toDateString(),
        'user_id'      => $user->id,
      ]);

      foreach ($data['items'] as $item) {
        PurchaseOrderItem::create([
          'purchase_order_id' => $po->id,
          'item_id'           => $item['item_id'],
          'quantity_ordered'  => $item['quantity'],
          'quantity_received' => 0,
          'unit_price'        => $item['unit_price'],
        ]);
      }

      return $po;
    });
  }

  public function approve(PurchaseOrder $po, int $approverUserId): void
  {
    DB::transaction(function () use ($po, $approverUserId) {
      // Lock row untuk mencegah race condition double-approve
      $fresh = PurchaseOrder::lockForUpdate()->findOrFail($po->id);

      if ($fresh->status !== 'draft') {
        throw new \RuntimeException('Hanya purchase order berstatus draft yang dapat disetujui.');
      }

      // H-02: Segregation of duties — pembuat PO tidak boleh approve sendiri
      if ($fresh->user_id === $approverUserId) {
        throw new \RuntimeException('Pembuat PO tidak dapat menyetujui PO miliknya sendiri.');
      }

      $fresh->update([
        'status'      => 'approved',
        'approved_by' => $approverUserId,
        'approved_at' => now(),
      ]);

      // Catat audit log
      \Illuminate\Support\Facades\Log::info('PO Approved', [
        'po_id'     => $fresh->id,
        'po_number' => $fresh->po_number,
        'approver'  => $approverUserId,
        'ip'        => request()->ip(),
      ]);
    });
  }

  public function receive(PurchaseOrder $po, array $receivedItems, int $userId): void
  {
    if (!in_array($po->status, ['approved', 'partially_received'])) {
      throw new \RuntimeException('Purchase order must be approved before receiving.');
    }

    DB::transaction(function () use ($po, $receivedItems, $userId) {
      $allReceived = true;

      foreach ($receivedItems as $poItemId => $data) {
        $qty = (float) ($data['quantity'] ?? 0);
        if ($qty <= 0) {
          continue;
        }

        /** @var PurchaseOrderItem $poItem */
        $poItem = $po->items()->where('id', $poItemId)->firstOrFail();
        $remaining = (float) $poItem->quantity_ordered - (float) $poItem->quantity_received;

        if ($qty > $remaining) {
          throw new \RuntimeException("Received quantity exceeds ordered quantity for item #{$poItem->item->name}.");
        }

        $poItem->increment('quantity_received', $qty);

        $this->stockService->goodsReceipt(
          $poItem->item,
          $po->warehouse,
          $qty,
          $userId,
          $po->po_number,
          "Received from PO {$po->po_number}"
        );

        if ((float) $poItem->fresh()->quantity_received < (float) $poItem->quantity_ordered) {
          $allReceived = false;
        }
      }

      $newStatus = $allReceived ? 'received' : 'partially_received';
      $po->update(['status' => $newStatus]);
    });
  }

  public function cancel(PurchaseOrder $po): void
  {
    if (!in_array($po->status, ['draft', 'approved'])) {
      throw new \RuntimeException('Only draft or approved POs can be cancelled.');
    }
    $po->update(['status' => 'cancelled']);
  }
}
