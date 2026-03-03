@extends('layouts.app')
@section('title', 'Mutasi Stok')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Mutasi Stok</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Riwayat keluar masuk barang dari semua gudang</p>
  </div>
  <a href="{{ route('stock-movements.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Buat Mutasi
  </a>
</div>

{{-- Filter --}}
<div class="filter-card">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Tipe Mutasi</label>
      <select name="type" class="filter-input">
        <option value="">Semua Tipe</option>
        @foreach(\App\Models\StockMovement::TYPES as $val => $label)
        <option value="{{ $val }}" {{ request('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Item</label>
      <select name="item_id" class="filter-input">
        <option value="">Semua Item</option>
        @foreach($items as $item)
        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Dari Tanggal</label>
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input">
    </div>
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Sampai Tanggal</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input">
    </div>
    <button type="submit" class="filter-btn">Filter</button>
    <a href="{{ route('stock-movements.index') }}" class="filter-reset">Reset</a>
  </form>
</div>

{{-- Table --}}
<div class="glass-card overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Referensi</th>
        <th class="text-left">Tanggal</th>
        <th class="text-left">Tipe</th>
        <th class="text-left">Item</th>
        <th class="text-right">Qty</th>
        <th class="text-left">Gudang</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($movements as $mov)
      <tr>
        <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $mov->reference_number }}</td>
        <td class="text-xs" style="color:rgba(148,163,184,0.7);">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
        <td>
          <span class="status-badge bg-indigo-50">{{ $mov->type_label }}</span>
        </td>
        <td class="font-medium" style="color:#e2e8f0;">{{ $mov->item->name }}</td>
        <td class="text-right font-semibold" style="color:{{ $mov->quantity > 0 ? '#6ee7b7' : '#fca5a5' }};">
          {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity, 0) }}
        </td>
        <td class="text-xs" style="color:rgba(148,163,184,0.7);">
          {{ $mov->toWarehouse->name ?? '' }}{{ $mov->fromWarehouse && $mov->toWarehouse ? ' ← ' : '' }}{{ $mov->fromWarehouse->name ?? '' }}
        </td>
        <td class="text-center">
          <a href="{{ route('stock-movements.show', $mov) }}" class="btn-action-detail">Detail</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="px-4 py-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
              <svg class="w-6 h-6" style="color:rgba(99,102,241,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
              </svg>
            </div>
            <p class="text-sm" style="color:rgba(148,163,184,0.6);">Belum ada mutasi stok.</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-3" style="border-top:1px solid rgba(255,255,255,0.06);">
    {{ $movements->appends(request()->query())->links() }}
  </div>
</div>

@endsection