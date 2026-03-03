@extends('layouts.app')
@section('title', 'Laporan Stok Rendah')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Laporan Item Stok Rendah</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Item dengan stok di bawah batas minimum</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('reports.low-stock.csv', request()->query()) }}"
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
    <a href="{{ route('reports.low-stock.pdf', request()->query()) }}"
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

@if($items->count())
<div class="flex items-center gap-3 mb-5 px-4 py-3 rounded-xl" style="background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);">
  <svg class="w-5 h-5 flex-shrink-0" style="color:#fcd34d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
  </svg>
  <p class="text-sm" style="color:#fcd34d;">
    <strong>{{ $items->count() }} item</strong> memiliki stok di bawah batas minimum.
  </p>
</div>
@endif

<div class="glass-card overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Kode</th>
        <th class="text-left">Nama Item</th>
        <th class="text-left">Kategori</th>
        <th class="text-right">Total Stok</th>
        <th class="text-right">Stok Min</th>
        <th class="text-right">Kekurangan</th>
        <th class="text-center">Satuan</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $item)
      @php $deficit = $item->min_stock - $item->totalStock(); @endphp
      <tr>
        <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $item->code }}</td>
        <td>
          <a href="{{ route('items.show', $item) }}" class="font-medium" style="color:#e2e8f0;text-decoration:none;transition:color 0.15s;"
            onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#e2e8f0'">
            {{ $item->name }}
          </a>
        </td>
        <td style="color:rgba(148,163,184,0.7);">{{ $item->category->name ?? '-' }}</td>
        <td class="text-right font-semibold" style="color:#fca5a5;">{{ number_format($item->totalStock(), 2) }}</td>
        <td class="text-right" style="color:rgba(148,163,184,0.6);">{{ number_format($item->min_stock, 2) }}</td>
        <td class="text-right font-semibold" style="color:#fcd34d;">{{ number_format($deficit, 2) }}</td>
        <td class="text-center">
          <span class="text-xs px-2 py-0.5 rounded font-medium" style="background:rgba(255,255,255,0.06);color:rgba(203,213,225,0.8);border:1px solid rgba(255,255,255,0.09);">
            {{ $item->unit->abbreviation ?? '-' }}
          </span>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="px-4 py-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15);">
              <svg class="w-6 h-6" style="color:rgba(16,185,129,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p class="text-sm" style="color:rgba(16,185,129,0.7);">Semua item memiliki stok yang cukup ✓</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection