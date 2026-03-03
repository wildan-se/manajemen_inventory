@extends('layouts.app')
@section('title', 'Stock Opname')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-center mb-5">
  <div>
    <h2 class="text-base font-semibold text-slate-700">Stock Opname</h2>
    <p class="text-xs text-slate-400 mt-0.5">Penghitungan dan penyesuaian stok fisik gudang</p>
  </div>
  <a href="{{ route('stock-opnames.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Buat Opname
  </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Referensi</th>
        <th class="text-left">Gudang</th>
        <th class="text-left">Tanggal</th>
        <th class="text-left">Catatan</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($opnames as $op)
      <tr>
        <td class="font-mono text-xs text-slate-500">{{ $op->reference_number }}</td>
        <td class="font-medium text-slate-800">{{ $op->warehouse->name ?? '-' }}</td>
        <td class="text-slate-500 text-xs">{{ $op->counted_at->format('d/m/Y') }}</td>
        <td class="text-slate-500 text-xs">{{ Str::limit($op->notes, 40) ?? '-' }}</td>
        <td class="text-center">
          @php
          $sc = match($op->status) {
          'draft' => 'bg-slate-100 text-slate-600',
          'in_progress' => 'bg-indigo-50 text-indigo-700',
          'completed' => 'bg-emerald-50 text-emerald-700',
          'cancelled' => 'bg-red-50 text-red-600',
          default => 'bg-slate-100 text-slate-600'
          };
          @endphp
          <span class="status-badge {{ $sc }}">{{ ucfirst($op->status) }}</span>
        </td>
        <td class="text-center">
          <a href="{{ route('stock-opnames.show', $op) }}" class="btn-action-detail">Detail</a>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
          <div class="flex flex-col items-center gap-2">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span class="text-sm">Belum ada stock opname.</span>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-4 py-3 border-t border-slate-100">{{ $opnames->links() }}</div>
</div>

@endsection