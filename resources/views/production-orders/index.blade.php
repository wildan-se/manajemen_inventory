@extends('layouts.app')
@section('title', 'Work Order Produksi')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Work Order Produksi</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Kelola order produksi dan konsumsi material</p>
  </div>
  <a href="{{ route('production-orders.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Buat WO
  </a>
</div>

{{-- Filter --}}
<div class="filter-card">
  <form method="GET" class="flex gap-3 items-end">
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Status</label>
      <select name="status" class="filter-input">
        <option value="">Semua Status</option>
        @foreach(['draft','in_progress','completed','cancelled'] as $s)
        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="filter-btn">Filter</button>
    <a href="{{ route('production-orders.index') }}" class="filter-reset">Reset</a>
  </form>
</div>

{{-- Table --}}
<div class="glass-card overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">No. WO</th>
        <th class="text-left">Deskripsi</th>
        <th class="text-left">Tanggal Mulai</th>
        <th class="text-left">Target Selesai</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($orders as $wo)
      <tr>
        <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $wo->wo_number }}</td>
        <td class="font-medium" style="color:#e2e8f0;">{{ Str::limit($wo->title, 50) }}</td>
        <td class="text-xs" style="color:rgba(148,163,184,0.7);">{{ $wo->planned_start?->format('d/m/Y') ?? '-' }}</td>
        <td class="text-xs" style="color:rgba(148,163,184,0.7);">{{ $wo->planned_end?->format('d/m/Y') ?? '-' }}</td>
        <td class="text-center">
          @php
          $woColor = match($wo->status) {
          'draft' => 'bg-slate-100',
          'in_progress' => 'bg-indigo-50',
          'completed' => 'bg-emerald-50',
          'cancelled' => 'bg-red-50',
          default => 'bg-slate-100'
          };
          @endphp
          <span class="status-badge {{ $woColor }}">{{ ucwords(str_replace('_', ' ', $wo->status)) }}</span>
        </td>
        <td class="text-center">
          <a href="{{ route('production-orders.show', $wo) }}" class="btn-action-detail">Detail</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-4 py-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
              style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
              <svg class="w-6 h-6" style="color:rgba(99,102,241,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              </svg>
            </div>
            <p class="text-sm" style="color:rgba(148,163,184,0.6);">Belum ada work order.</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-3" style="border-top:1px solid rgba(255,255,255,0.06);">
    {{ $orders->appends(request()->query())->links() }}
  </div>
</div>

@endsection