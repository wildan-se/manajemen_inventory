<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
  protected $fillable = [
    'purchase_order_id',
    'item_id',
    'quantity_ordered',
    'quantity_received',
    'unit_price',
    'notes',
  ];

  protected function casts(): array
  {
    return [
      'quantity_ordered'  => 'decimal:4',
      'quantity_received' => 'decimal:4',
      'unit_price'        => 'decimal:2',
    ];
  }

  public function purchaseOrder(): BelongsTo
  {
    return $this->belongsTo(PurchaseOrder::class);
  }
  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }

  public function getSubtotalAttribute(): float
  {
    return (float) $this->quantity_ordered * (float) $this->unit_price;
  }
}
