<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
  protected $fillable = ['warehouse_id', 'code', 'name', 'description'];

  public function warehouse(): BelongsTo
  {
    return $this->belongsTo(Warehouse::class);
  }

  public function stocks(): HasMany
  {
    return $this->hasMany(Stock::class);
  }
}
