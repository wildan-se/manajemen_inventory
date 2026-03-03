<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionOrderService
{
  public function __construct(protected StockService $stockService) {}

  public function generateWoNumber(): string
  {
    $prefix = 'WO-' . now()->format('Ym') . '-';
    $last   = ProductionOrder::where('wo_number', 'like', $prefix . '%')
      ->orderByDesc('wo_number')
      ->first();

    $seq = $last ? ((int) substr($last->wo_number, -4)) + 1 : 1;
    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
  }

  public function create(array $data, $user): ProductionOrder
  {
    return DB::transaction(function () use ($data, $user) {
      $wo = ProductionOrder::create([
        'wo_number'     => $data['wo_number'],
        'title'         => $data['title'],
        'warehouse_id'  => $data['warehouse_id'],
        'planned_start' => $data['planned_start'],
        'planned_end'   => $data['planned_end'],
        'description'   => $data['description'] ?? null,
        'status'        => 'draft',
        'user_id'       => $user->id,
      ]);

      foreach ($data['inputs'] ?? [] as $input) {
        $wo->items()->create([
          'item_id'  => $input['item_id'],
          'quantity' => $input['quantity'],
          'type'     => 'input',
        ]);
      }

      foreach ($data['outputs'] ?? [] as $output) {
        $wo->items()->create([
          'item_id'  => $output['item_id'],
          'quantity' => $output['quantity'],
          'type'     => 'output',
        ]);
      }

      return $wo;
    });
  }

  public function start(ProductionOrder $wo, int $userId): void
  {
    if ($wo->status !== 'draft') {
      throw new \RuntimeException('Only draft work orders can be started.');
    }

    DB::transaction(function () use ($wo, $userId) {
      // Issue all input materials
      foreach ($wo->inputs as $item) {
        $this->stockService->materialIssue(
          $item->item,
          $wo->warehouse,
          (float) $item->quantity,
          $userId,
          $wo->wo_number,
          "Material issue for WO {$wo->wo_number}"
        );
      }

      $wo->update([
        'status'       => 'in_progress',
        'actual_start' => now()->toDateString(),
      ]);
    });
  }

  public function complete(ProductionOrder $wo, int $userId): void
  {
    if ($wo->status !== 'in_progress') {
      throw new \RuntimeException('Only in-progress work orders can be completed.');
    }

    DB::transaction(function () use ($wo, $userId) {
      // Add all output items to stock
      foreach ($wo->outputs as $item) {
        $this->stockService->goodsReceipt(
          $item->item,
          $wo->warehouse,
          (float) $item->quantity,
          $userId,
          $wo->wo_number,
          "Production output from WO {$wo->wo_number}"
        );
      }

      $wo->update([
        'status'     => 'completed',
        'actual_end' => now()->toDateString(),
      ]);
    });
  }

  public function cancel(ProductionOrder $wo): void
  {
    if (!in_array($wo->status, ['draft', 'in_progress'])) {
      throw new \RuntimeException('Only draft or in-progress work orders can be cancelled.');
    }
    $wo->update(['status' => 'cancelled']);
  }
}
