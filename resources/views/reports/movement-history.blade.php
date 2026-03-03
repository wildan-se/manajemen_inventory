@extends('layouts.app')
@section('title', 'Riwayat Mutasi Stok')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Riwayat Mutasi Stok</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Laporan lengkap semua pergerakan stok</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('reports.movement-history.csv', request()->query()) }}"
      data-turbo="false"
      target="_blank"
      rel="noopener noreferrer"
      style="display:inline-flex;align-items:center;gap:6px;background:rgba(16,185,129,0.15);color:#6ee7b7;border:1px solid rgba(16,185,129,0.25);padding:7px 14px;border-radius:10px;font-size:0.8rem;font-weight:600;text-decoration:none;transition:all 0.15s;"
      onmouseover="this.style.background='rgba(16,185,129,0.25)'" onmouseout="this.style.background='rgba(16,185,129,0.15)'">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 3v13m-4-4l4 4 4-4" />
      </svg>
      CSV
    </a>
    <a href="{{ route('reports.movement-history.pdf', request()->query()) }}"
      data-turbo="false"
      target="_blank"
      rel="noopener noreferrer"
      style="display:inline-flex;align-items:center;gap:6px;background:rgba(239,68,68,0.12);color:#fca5a5;border:1px solid rgba(239,68,68,0.2);padding:7px 14px;border-radius:10px;font-size:0.8rem;font-weight:600;text-decoration:none;transition:all 0.15s;"
      onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.12)'">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
      </svg>
      PDF
    </a>
  </div>
</div>

{{-- Filter --}}
<div class="filter-card">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
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
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Tipe</label>
      <select name="type" class="filter-input">
        <option value="">Semua</option>
        @foreach(\App\Models\StockMovement::TYPES as $val => $label)
        <option value="{{ $val }}" {{ request('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
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
    <a href="{{ route('reports.movement-history') }}" class="filter-reset">Reset</a>
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
        <th class="text-left">Dari</th>
        <th class="text-left">Ke</th>
        <th class="text-left">Oleh</th>
      </tr>
    </thead>
    <tbody>
      @forelse($movements as $mov)
      <tr>
        <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $mov->reference_number }}</td>
        <td class="text-xs whitespace-nowrap" style="color:rgba(148,163,184,0.7);">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
        <td>
          <span class="status-badge bg-indigo-50">{{ $mov->type_label }}</span>
        </td>
        <td class="font-medium" style="color:#e2e8f0;">{{ $mov->item->name }}</td>
        <td class="text-right font-semibold" style="color:{{ $mov->quantity > 0 ? '#6ee7b7' : '#fca5a5' }};">
          {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity, 2) }}
        </td>
        <td class="text-xs" style="color:rgba(148,163,184,0.6);">{{ $mov->fromWarehouse->name ?? '-' }}</td>
        <td class="text-xs" style="color:rgba(148,163,184,0.6);">{{ $mov->toWarehouse->name ?? '-' }}</td>
        <td class="text-xs" style="color:rgba(148,163,184,0.6);">{{ $mov->user->name ?? '-' }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="px-4 py-12 text-center">
          <p class="text-sm" style="color:rgba(148,163,184,0.6);">Tidak ada data mutasi.</p>
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