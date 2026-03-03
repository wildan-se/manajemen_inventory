<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
  public const TYPES = [
    'goods_receipt'     => 'Goods Receipt (Inbound)',
    'material_issue'    => 'Material Issue to Production',
    'stock_transfer'    => 'Stock Transfer',
    'production_output' => 'Production Output',
    'sales_dispatch'    => 'Sales Dispatch (Outbound)',
    'stock_adjustment'  => 'Stock Adjustment',
    'stock_opname'      => 'Stock Opname',
  ];

  protected $fillable = [
    'reference_number',
    'type',
    'item_id',
    'from_warehouse_id',
    'from_location_id',
    'to_warehouse_id',
    'to_location_id',
    'quantity',
    'quantity_before',
    'quantity_after',
    'reference_document',
    'notes',
    'user_id',
  ];

  protected function casts(): array
  {
    return [
      'quantity'        => 'decimal:4',
      'quantity_before' => 'decimal:4',
      'quantity_after'  => 'decimal:4',
    ];
  }

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }
  public function fromWarehouse(): BelongsTo
  {
    return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
  }
  public function fromLocation(): BelongsTo
  {
    return $this->belongsTo(Location::class, 'from_location_id');
  }
  public function toWarehouse(): BelongsTo
  {
    return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
  }
  public function toLocation(): BelongsTo
  {
    return $this->belongsTo(Location::class, 'to_location_id');
  }
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function getTypeLabelAttribute(): string
  {
    return self::TYPES[$this->type] ?? $this->type;
  }
}
