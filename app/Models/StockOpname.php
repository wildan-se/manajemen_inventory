<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
  public const STATUSES = [
    'draft'       => 'Draft',
    'in_progress' => 'In Progress',
    'completed'   => 'Completed',
    'cancelled'   => 'Cancelled',
  ];

  protected $fillable = [
    'reference_number',
    'warehouse_id',
    'status',
    'counted_at',
    'notes',
    'user_id',
  ];

  protected function casts(): array
  {
    return ['counted_at' => 'date'];
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
    return $this->hasMany(StockOpnameItem::class);
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
