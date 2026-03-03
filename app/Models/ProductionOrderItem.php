<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItem extends Model
{
  protected $fillable = [
    'production_order_id',
    'item_id',
    'quantity',
    'type',
    'notes',
  ];

  protected function casts(): array
  {
    return ['quantity' => 'decimal:4'];
  }

  public function productionOrder(): BelongsTo
  {
    return $this->belongsTo(ProductionOrder::class);
  }
  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }
}
