<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
  protected $fillable = ['item_id', 'warehouse_id', 'location_id', 'quantity'];

  protected function casts(): array
  {
    return ['quantity' => 'decimal:4'];
  }

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }

  public function warehouse(): BelongsTo
  {
    return $this->belongsTo(Warehouse::class);
  }

  public function location(): BelongsTo
  {
    return $this->belongsTo(Location::class);
  }
}
