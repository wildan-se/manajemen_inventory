<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
  public const STATUSES = [
    'draft'       => 'Draft',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'cancelled'   => 'Cancelled',
  ];

  protected $fillable = [
    'wo_number',
    'title',
    'description',
    'warehouse_id',
    'status',
    'planned_start',
    'planned_end',
    'actual_start',
    'actual_end',
    'user_id',
  ];

  protected function casts(): array
  {
    return [
      'planned_start' => 'date',
      'planned_end'   => 'date',
      'actual_start'  => 'date',
      'actual_end'    => 'date',
    ];
  }

  public function warehouse(): BelongsTo
  {
    return $this->belongsTo(Warehouse::class);
  }
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(ProductionOrderItem::class);
  }

  public function inputs(): HasMany
  {
    return $this->hasMany(ProductionOrderItem::class)->where('type', 'input');
  }

  public function outputs(): HasMany
  {
    return $this->hasMany(ProductionOrderItem::class)->where('type', 'output');
  }

  public function getStatusLabelAttribute(): string
  {
    return self::STATUSES[$this->status] ?? $this->status;
  }

  public function getStatusColorAttribute(): string
  {
    return match ($this->status) {
      'draft'       => 'gray',
      'in_progress' => 'blue',
      'completed'   => 'green',
      'cancelled'   => 'red',
      default       => 'gray',
    };
  }
}
