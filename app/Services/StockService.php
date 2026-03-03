<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StockService
{
  /**
   * Generate a unique reference number.
   */
  public function generateReference(string $prefix = 'SM'): string
  {
    $date = now()->format('Ymd');
    do {
      $rand = strtoupper(Str::random(4));
      $ref  = "{$prefix}-{$date}-{$rand}";
    } while (StockMovement::where('reference_number', $ref)->exists());

    return $ref;
  }

  /**
   * Get or create a stock record for item/warehouse/location.
   */
  public function getOrCreateStock(int $itemId, int $warehouseId, ?int $locationId = null): Stock
  {
    return Stock::firstOrCreate(
      ['item_id' => $itemId, 'warehouse_id' => $warehouseId, 'location_id' => $locationId],
      ['quantity' => 0]
    );
  }

  /**
   * Goods Receipt — increases stock at destination warehouse.
   */
  public function goodsReceipt(
    Item $item,
    Warehouse $warehouse,
    float $quantity,
    int $userId,
    string $referenceDocument = '',
    string $notes = '',
    ?Location $location = null
  ): StockMovement {
    return DB::transaction(function () use ($item, $warehouse, $quantity, $userId, $referenceDocument, $notes, $location) {
      $stock  = $this->getOrCreateStock($item->id, $warehouse->id, $location?->id);

      // Lock the correct row and read the freshest quantity inside the transaction
      $stock  = Stock::where('id', $stock->id)->lockForUpdate()->first();
      $before = (float) $stock->quantity;
      $stock->increment('quantity', $quantity);
      $after  = $before + $quantity;

      return StockMovement::create([
        'reference_number'  => $this->generateReference('GR'),
        'type'              => 'goods_receipt',
        'item_id'           => $item->id,
        'to_warehouse_id'   => $warehouse->id,
        'to_location_id'    => $location?->id,
        'quantity'          => $quantity,
        'quantity_before'   => $before,
        'quantity_after'    => $after,
        'reference_document' => $referenceDocument,
        'notes'             => $notes,
        'user_id'           => $userId,
      ]);
    });
  }

  /**
   * Material Issue — decreases stock from source warehouse.
   */
  public function materialIssue(
    Item $item,
    Warehouse $warehouse,
    float $quantity,
    int $userId,
    string $referenceDocument = '',
    string $notes = '',
    ?Location $location = null
  ): StockMovement {
    return DB::transaction(function () use ($item, $warehouse, $quantity, $userId, $referenceDocument, $notes, $location) {
      $stock = Stock::where('item_id', $item->id)
        ->where('warehouse_id', $warehouse->id)
        ->where('location_id', $location?->id)
        ->lockForUpdate()
        ->firstOrFail();

      if ((float) $stock->quantity < $quantity) {
        // M-01: Log detail untuk audit, tampilkan pesan generik ke user
        \Illuminate\Support\Facades\Log::warning('Insufficient stock for material issue', [
          'item_id'     => $item->id,
          'item_name'   => $item->name,
          'warehouse_id' => $warehouse->id,
          'requested'   => $quantity,
          'available'   => $stock->quantity,
          'user_id'     => $userId ?? null,
        ]);
        throw new \RuntimeException('Stok tidak mencukupi. Pastikan ketersediaan stok sebelum melakukan transaksi.');
      }

      $before = (float) $stock->quantity;
      $stock->decrement('quantity', $quantity);
      $after = $before - $quantity;

      return StockMovement::create([
        'reference_number'   => $this->generateReference('MI'),
        'type'               => 'material_issue',
        'item_id'            => $item->id,
        'from_warehouse_id'  => $warehouse->id,
        'from_location_id'   => $location?->id,
        'quantity'           => $quantity,
        'quantity_before'    => $before,
        'quantity_after'     => $after,
        'reference_document' => $referenceDocument,
        'notes'              => $notes,
        'user_id'            => $userId,
      ]);
    });
  }

  /**
   * Stock Transfer — move stock between warehouses/locations.
   */
  public function stockTransfer(
    Item $item,
    Warehouse $fromWarehouse,
    Warehouse $toWarehouse,
    float $quantity,
    int $userId,
    string $notes = '',
    ?Location $fromLocation = null,
    ?Location $toLocation = null
  ): StockMovement {
    return DB::transaction(function () use ($item, $fromWarehouse, $toWarehouse, $quantity, $userId, $notes, $fromLocation, $toLocation) {
      $fromStock = Stock::where('item_id', $item->id)
        ->where('warehouse_id', $fromWarehouse->id)
        ->where('location_id', $fromLocation?->id)
        ->lockForUpdate()
        ->firstOrFail();

      if ((float) $fromStock->quantity < $quantity) {
        \Illuminate\Support\Facades\Log::warning('Insufficient stock for stock transfer', [
          'item_id'          => $item->id,
          'from_warehouse_id' => $fromWarehouse->id,
          'requested'        => $quantity,
          'available'        => $fromStock->quantity,
        ]);
        throw new \RuntimeException('Stok di gudang asal tidak mencukupi untuk transfer.');
      }

      $before = (float) $fromStock->quantity;
      $fromStock->decrement('quantity', $quantity);

      $toStock = $this->getOrCreateStock($item->id, $toWarehouse->id, $toLocation?->id);
      $toStock->increment('quantity', $quantity);

      return StockMovement::create([
        'reference_number'  => $this->generateReference('ST'),
        'type'              => 'stock_transfer',
        'item_id'           => $item->id,
        'from_warehouse_id' => $fromWarehouse->id,
        'from_location_id'  => $fromLocation?->id,
        'to_warehouse_id'   => $toWarehouse->id,
        'to_location_id'    => $toLocation?->id,
        'quantity'          => $quantity,
        'quantity_before'   => $before,
        'quantity_after'    => $before - $quantity,
        'notes'             => $notes,
        'user_id'           => $userId,
      ]);
    });
  }

  /**
   * Unified entry point: dispatch to the appropriate method based on type.
   */
  public function recordMovement(array $data): StockMovement
  {
    $item     = Item::findOrFail($data['item_id']);
    $userId   = (int) $data['user_id'];
    $qty      = (float) $data['quantity'];
    $ref      = $data['reference_number'] ?? '';
    $notes    = $data['notes'] ?? '';

    return match ($data['type']) {
      'goods_receipt', 'production_output' => $this->goodsReceipt(
        $item,
        Warehouse::findOrFail($data['to_warehouse_id']),
        $qty,
        $userId,
        $ref,
        $notes
      ),
      'material_issue', 'sales_dispatch' => $this->materialIssue(
        $item,
        Warehouse::findOrFail($data['from_warehouse_id']),
        $qty,
        $userId,
        $ref,
        $notes
      ),
      'stock_transfer' => $this->stockTransfer(
        $item,
        Warehouse::findOrFail($data['from_warehouse_id']),
        Warehouse::findOrFail($data['to_warehouse_id']),
        $qty,
        $userId,
        $notes
      ),
      'stock_adjustment' => $this->stockAdjustment(
        $item,
        Warehouse::findOrFail($data['to_warehouse_id'] ?? $data['from_warehouse_id']),
        $qty,
        $userId,
        $notes
      ),
      default => throw new \RuntimeException("Unsupported type: {$data['type']}"),
    };
  }

  /**
   * Stock Adjustment — directly adjusts stock to a new quantity.
   */
  public function stockAdjustment(
    Item $item,
    Warehouse $warehouse,
    float $newQuantity,
    int $userId,
    string $notes = '',
    ?Location $location = null
  ): StockMovement {
    return DB::transaction(function () use ($item, $warehouse, $newQuantity, $userId, $notes, $location) {
      $stockRecord = $this->getOrCreateStock($item->id, $warehouse->id, $location?->id);

      // Lock for update to prevent race conditions
      $stock = Stock::where('id', $stockRecord->id)->lockForUpdate()->first();

      $before = (float) $stock->quantity;
      $diff   = $newQuantity - $before;

      $stock->update(['quantity' => $newQuantity]);

      return StockMovement::create([
        'reference_number'  => $this->generateReference('ADJ'),
        'type'              => 'stock_adjustment',
        'item_id'           => $item->id,
        'to_warehouse_id'   => $warehouse->id,
        'to_location_id'    => $location?->id,
        'quantity'          => abs($diff),
        'quantity_before'   => $before,
        'quantity_after'    => $newQuantity,
        'notes'             => $notes,
        'user_id'           => $userId,
      ]);
    });
  }
}
