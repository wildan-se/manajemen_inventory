@extends('layouts.app')
@section('title', 'Supplier')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-center mb-5">
  <div>
    <h2 class="text-base font-semibold text-slate-700">Daftar Supplier</h2>
    <p class="text-xs text-slate-400 mt-0.5">Kelola data supplier pengadaan barang</p>
  </div>
  <a href="{{ route('suppliers.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Tambah Supplier
  </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Kode</th>
        <th class="text-left">Nama</th>
        <th class="text-left">Email</th>
        <th class="text-left">Telepon</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($suppliers as $s)
      <tr>
        <td class="font-mono text-xs text-slate-500">{{ $s->code }}</td>
        <td class="font-medium text-slate-800">
          <a href="{{ route('suppliers.show', $s) }}"
            class="hover:text-indigo-600 transition-colors">{{ $s->name }}</a>
        </td>
        <td class="text-slate-500 text-xs">{{ $s->email ?? '-' }}</td>
        <td class="text-slate-500 text-xs">{{ $s->phone ?? '-' }}</td>
        <td class="text-center">
          @if($s->is_active)
          <span class="status-badge bg-emerald-50 text-emerald-700">Aktif</span>
          @else
          <span class="status-badge bg-slate-100 text-slate-500">Nonaktif</span>
          @endif
        </td>
        <td class="text-center">
          <div class="flex items-center justify-center gap-1.5">
            <a href="{{ route('suppliers.edit', $s) }}" class="btn-action-edit">Edit</a>
            <form method="POST" action="{{ route('suppliers.destroy', $s) }}"
              class="inline" onsubmit="return confirm('Hapus supplier ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-action-delete">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="6" class="px-4 py-10 text-center text-slate-400">
          <span class="text-sm">Belum ada supplier.</span>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-4 py-3 border-t border-slate-100">{{ $suppliers->links() }}</div>
</div>

@endsection