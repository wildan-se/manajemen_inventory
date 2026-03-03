@extends('layouts.app')
@section('title', 'Laporan Ringkasan Stok')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Laporan Ringkasan Stok</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Kondisi stok seluruh item di semua gudang</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('reports.stock-summary.csv', request()->query()) }}"
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
    <a href="{{ route('reports.stock-summary.pdf', request()->query()) }}"
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
  <form method="GET" class="flex gap-3 items-end">
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Gudang</label>
      <select name="warehouse_id" class="filter-input">
        <option value="">Semua Gudang</option>
        @foreach($warehouses as $wh)
        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="filter-btn">Filter</button>
    <a href="{{ route('reports.stock-summary') }}" class="filter-reset">Reset</a>
  </form>
</div>

{{-- Table --}}
<div class="glass-card overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Kode</th>
        <th class="text-left">Nama Item</th>
        <th class="text-left">Kategori</th>
        <th class="text-left">Gudang</th>
        <th class="text-right">Stok</th>
        <th class="text-right">Min</th>
        <th class="text-center">Satuan</th>
        <th class="text-center">Kondisi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($stocks as $stock)
      <tr>
        <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $stock->item->code }}</td>
        <td class="font-medium" style="color:#e2e8f0;">{{ $stock->item->name }}</td>
        <td style="color:rgba(148,163,184,0.7);">{{ $stock->item->category->name ?? '-' }}</td>
        <td style="color:rgba(148,163,184,0.7);">{{ $stock->warehouse->name }}</td>
        <td class="text-right font-semibold" style="color:{{ $stock->quantity <= $stock->item->min_stock ? '#fca5a5' : 'rgba(226,232,240,0.9)' }};">
          {{ number_format($stock->quantity, 2) }}
        </td>
        <td class="text-right" style="color:rgba(148,163,184,0.55);">{{ number_format($stock->item->min_stock, 2) }}</td>
        <td class="text-center">
          <span class="text-xs px-2 py-0.5 rounded font-medium" style="background:rgba(255,255,255,0.06);color:rgba(203,213,225,0.8);border:1px solid rgba(255,255,255,0.09);">
            {{ $stock->item->unit->abbreviation ?? '-' }}
          </span>
        </td>
        <td class="text-center">
          @if($stock->quantity <= $stock->item->min_stock)
            <span class="status-badge bg-red-50">Rendah</span>
            @else
            <span class="status-badge bg-emerald-50">Normal</span>
            @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="px-4 py-12 text-center">
          <p class="text-sm" style="color:rgba(148,163,184,0.6);">Tidak ada data stok.</p>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-3" style="border-top:1px solid rgba(255,255,255,0.06);">
    {{ $stocks->appends(request()->query())->links() }}
  </div>
</div>

@endsection