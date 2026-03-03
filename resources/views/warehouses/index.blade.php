@extends('layouts.app')
@section('title', 'Gudang')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-center mb-5">
  <div>
    <h2 class="text-base font-semibold text-slate-700">Daftar Gudang</h2>
    <p class="text-xs text-slate-400 mt-0.5">Master data lokasi penyimpanan inventori</p>
  </div>
  <a href="{{ route('warehouses.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Tambah Gudang
  </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Kode</th>
        <th class="text-left">Nama</th>
        <th class="text-left">Alamat</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($warehouses as $w)
      <tr>
        <td class="font-mono text-xs text-slate-500">{{ $w->code }}</td>
        <td class="font-medium">
          <a href="{{ route('warehouses.show', $w) }}"
            class="text-slate-800 hover:text-indigo-600 transition-colors">{{ $w->name }}</a>
        </td>
        <td class="text-slate-500 text-xs">{{ Str::limit($w->address, 50) ?? '-' }}</td>
        <td class="text-center">
          @if($w->is_active)
          <span class="status-badge bg-emerald-50 text-emerald-700">Aktif</span>
          @else
          <span class="status-badge bg-slate-100 text-slate-500">Nonaktif</span>
          @endif
        </td>
        <td class="text-center">
          <div class="flex items-center justify-center gap-1.5">
            <a href="{{ route('warehouses.edit', $w) }}" class="btn-action-edit">Edit</a>
            <form method="POST" action="{{ route('warehouses.destroy', $w) }}"
              class="inline" onsubmit="return confirm('Hapus gudang ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-action-delete">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
          <span class="text-sm">Belum ada gudang.</span>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-4 py-3 border-t border-slate-100">{{ $warehouses->links() }}</div>
</div>

@endsection