<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
  protected $fillable = ['code', 'name', 'address', 'description', 'is_active'];

  protected function casts(): array
  {
    return ['is_active' => 'boolean'];
  }

  public function locations(): HasMany
  {
    return $this->hasMany(Location::class);
  }

  public function stocks(): HasMany
  {
    return $this->hasMany(Stock::class);
  }
}
