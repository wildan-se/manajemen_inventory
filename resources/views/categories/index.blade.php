@extends('layouts.app')
@section('title', 'Kategori')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-center mb-5">
  <div>
    <h2 class="text-base font-semibold text-slate-700">Daftar Kategori</h2>
    <p class="text-xs text-slate-400 mt-0.5">Pengelompokan item berdasarkan kategori</p>
  </div>
  <a href="{{ route('categories.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Tambah Kategori
  </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">#</th>
        <th class="text-left">Nama Kategori</th>
        <th class="text-left">Deskripsi</th>
        <th class="text-center">Jumlah Item</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($categories as $cat)
      <tr>
        <td class="text-slate-400">{{ $loop->iteration }}</td>
        <td class="font-medium text-slate-800">{{ $cat->name }}</td>
        <td class="text-slate-500">{{ $cat->description ?? '-' }}</td>
        <td class="text-center">
          <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
            {{ $cat->items_count }}
          </span>
        </td>
        <td class="text-center">
          <div class="flex items-center justify-center gap-1.5">
            <a href="{{ route('categories.edit', $cat) }}" class="btn-action-edit">Edit</a>
            <form method="POST" action="{{ route('categories.destroy', $cat) }}"
              class="inline" onsubmit="return confirm('Hapus kategori ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-action-delete">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="px-4 py-10 text-center text-slate-400">
          <span class="text-sm">Belum ada kategori.</span>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-4 py-3 border-t border-slate-100">{{ $categories->links() }}</div>
</div>

@endsection