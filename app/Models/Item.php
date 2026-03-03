<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
  protected $fillable = [
    'code',
    'name',
    'category_id',
    'unit_id',
    'description',
    'min_stock',
    'max_stock',
    'is_active',
  ];

  protected function casts(): array
  {
    return [
      'min_stock' => 'decimal:4',
      'max_stock'  => 'decimal:4',
      'is_active'  => 'boolean',
    ];
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function unit(): BelongsTo
  {
    return $this->belongsTo(Unit::class);
  }

  public function stocks(): HasMany
  {
    return $this->hasMany(Stock::class);
  }

  public function stockMovements(): HasMany
  {
    return $this->hasMany(StockMovement::class);
  }

  public function totalStock(): float
  {
    return (float) $this->stocks()->sum('quantity');
  }

  public function isBelowMinStock(): bool
  {
    return $this->totalStock() < $this->min_stock;
  }
}
