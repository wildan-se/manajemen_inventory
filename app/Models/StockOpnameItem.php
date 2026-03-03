<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
  protected $fillable = [
    'stock_opname_id',
    'item_id',
    'location_id',
    'system_quantity',
    'physical_quantity',
    'discrepancy',
    'notes',
  ];

  protected function casts(): array
  {
    return [
      'system_quantity'   => 'decimal:4',
      'physical_quantity' => 'decimal:4',
      'discrepancy'       => 'decimal:4',
    ];
  }

  public function stockOpname(): BelongsTo
  {
    return $this->belongsTo(StockOpname::class);
  }
  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }
  public function location(): BelongsTo
  {
    return $this->belongsTo(Location::class);
  }
}
