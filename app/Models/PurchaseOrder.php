<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
  public const STATUSES = [
    'draft'              => 'Draft',
    'approved'           => 'Approved',
    'partially_received' => 'Partially Received',
    'received'           => 'Received',
    'cancelled'          => 'Cancelled',
  ];

  protected $fillable = [
    'po_number',
    'supplier_id',
    'warehouse_id',
    'status',
    'order_date',
    'expected_date',
    'notes',
    'approved_by',
    'approved_at',
    'user_id',
  ];

  protected function casts(): array
  {
    return [
      'order_date'    => 'date',
      'expected_date' => 'date',
      'approved_at'   => 'datetime',
    ];
  }

  public function supplier(): BelongsTo
  {
    return $this->belongsTo(Supplier::class);
  }
  public function warehouse(): BelongsTo
  {
    return $this->belongsTo(Warehouse::class);
  }
  public function approver(): BelongsTo
  {
    return $this->belongsTo(User::class, 'approved_by');
  }
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(PurchaseOrderItem::class);
  }

  public function getStatusLabelAttribute(): string
  {
    return self::STATUSES[$this->status] ?? $this->status;
  }

  public function getStatusColorAttribute(): string
  {
    return match ($this->status) {
      'draft'              => 'bg-gray-100 text-gray-600',
      'approved'           => 'bg-blue-100 text-blue-700',
      'partially_received' => 'bg-amber-100 text-amber-700',
      'received'           => 'bg-green-100 text-green-700',
      'cancelled'          => 'bg-red-100 text-red-600',
      default              => 'bg-gray-100 text-gray-600',
    };
  }

  public function getTotalAmountAttribute(): float
  {
    return $this->items->sum(fn($item) => (float) $item->quantity_ordered * (float) $item->unit_price);
  }
}
