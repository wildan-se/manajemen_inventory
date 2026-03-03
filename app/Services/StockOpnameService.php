<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Support\Facades\DB;

class StockOpnameService
{
  public function __construct(protected StockService $stockService) {}

  public function generateReference(): string
  {
    $prefix = 'SO-' . now()->format('Ym') . '-';
    $last   = StockOpname::where('reference_number', 'like', $prefix . '%')
      ->orderByDesc('reference_number')
      ->first();

    $seq = $last ? ((int) substr($last->reference_number, -4)) + 1 : 1;
    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
  }

  public function create(array $data, $user): StockOpname
  {
    return StockOpname::create([
      'reference_number' => $data['reference_number'],
      'warehouse_id'     => $data['warehouse_id'],
      'counted_at'       => $data['counted_at'],
      'notes'            => $data['notes'] ?? null,
      'status'           => 'draft',
      'user_id'          => $user->id,
    ]);
  }

  public function loadStock(StockOpname $opname): StockOpname
  {
    $this->loadSystemQuantities($opname);
    return $opname->fresh()->load('items.item');
  }

  public function saveCount(StockOpname $opname, array $counts): StockOpname
  {
    $this->savePhysicalCount($opname, $counts);
    return $opname->fresh()->load('items.item');
  }

  public function cancel(StockOpname $opname): StockOpname
  {
    if (!in_array($opname->status, ['draft', 'in_progress'])) {
      throw new \RuntimeException('Only draft or in-progress opnames can be cancelled.');
    }
    $opname->update(['status' => 'cancelled']);
    return $opname;
  }

  /**
   * Load current system quantities for all items in the warehouse.
   */
  public function loadSystemQuantities(StockOpname $opname): void
  {
    if ($opname->status !== 'draft') {
      throw new \RuntimeException('Can only load quantities for draft opname.');
    }

    DB::transaction(function () use ($opname) {
      $opname->items()->delete();

      $stocks = Stock::where('warehouse_id', $opname->warehouse_id)
        ->with('item')
        ->get();

      foreach ($stocks as $stock) {
        StockOpnameItem::create([
          'stock_opname_id'   => $opname->id,
          'item_id'           => $stock->item_id,
          'location_id'       => $stock->location_id,
          'system_quantity'   => $stock->quantity,
          'physical_quantity' => $stock->quantity, // default same
          'discrepancy'       => 0,
        ]);
      }

      $opname->update(['status' => 'in_progress']);
    });
  }

  /**
   * Save physical count results.
   */
  public function savePhysicalCount(StockOpname $opname, array $counts): void
  {
    if ($opname->status !== 'in_progress') {
      throw new \RuntimeException('Opname must be in progress to save counts.');
    }

    DB::transaction(function () use ($opname, $counts) {
      foreach ($counts as $itemId => $physQty) {
        $opnameItem = $opname->items()->where('item_id', $itemId)->first();
        if (!$opnameItem) continue;

        $physical    = (float) $physQty;
        $system      = (float) $opnameItem->system_quantity;
        $discrepancy = $physical - $system;

        $opnameItem->update([
          'physical_quantity' => $physical,
          'discrepancy'       => $discrepancy,
        ]);
      }
    });
  }

  /**
   * Complete opname and apply adjustments to stock.
   */
  public function complete(StockOpname $opname, int $userId): void
  {
    if ($opname->status !== 'in_progress') {
      throw new \RuntimeException('Opname must be in progress to complete.');
    }

    DB::transaction(function () use ($opname, $userId) {
      foreach ($opname->items as $opnameItem) {
        if ($opnameItem->discrepancy == 0) continue;

        $this->stockService->stockAdjustment(
          $opnameItem->item,
          $opname->warehouse,
          (float) $opnameItem->physical_quantity,
          $userId,
          "Stock opname adjustment: {$opname->reference_number}",
          $opnameItem->location
        );
      }

      $opname->update(['status' => 'completed']);
    });
  }
}
