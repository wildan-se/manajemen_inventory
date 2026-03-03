@extends('layouts.app')
@section('title', 'Item / Barang')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Daftar Item</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Kelola semua material dan barang inventori</p>
  </div>
  <a href="{{ route('items.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Tambah Item
  </a>
</div>

{{-- Filter --}}
<div class="filter-card">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Cari Item</label>
      <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="w-3.5 h-3.5" style="color:rgba(148,163,184,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
          </svg>
        </span>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode / Nama..."
          class="filter-input pl-8 w-52">
      </div>
    </div>
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Kategori</label>
      <select name="category_id" class="filter-input">
        <option value="">Semua Kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Status</label>
      <select name="is_active" class="filter-input">
        <option value="">Semua Status</option>
        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>
    <button type="submit" class="filter-btn">Filter</button>
    <a href="{{ route('items.index') }}" class="filter-reset">Reset</a>
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
        <th class="text-center">Satuan</th>
        <th class="text-right">Total Stok</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $item)
      <tr>
        <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $item->code }}</td>
        <td>
          <div>
            <a href="{{ route('items.show', $item) }}" class="font-medium" style="color:#e2e8f0;text-decoration:none;transition:color 0.15s;"
              onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#e2e8f0'">
              {{ $item->name }}
            </a>
            @if($item->isBelowMinStock())
            <span class="ml-1.5 inline-flex items-center gap-1 text-xs px-1.5 py-0.5 rounded font-medium"
              style="background:rgba(239,68,68,0.12);color:#fca5a5;border:1px solid rgba(239,68,68,0.2);">
              Stok Rendah
            </span>
            @endif
          </div>
        </td>
        <td style="color:rgba(148,163,184,0.75);">{{ $item->category->name ?? '-' }}</td>
        <td class="text-center">
          <span class="text-xs px-2 py-0.5 rounded font-medium"
            style="background:rgba(255,255,255,0.06);color:rgba(203,213,225,0.8);border:1px solid rgba(255,255,255,0.09);">
            {{ $item->unit->abbreviation ?? '-' }}
          </span>
        </td>
        <td class="text-right font-semibold" style="color:{{ $item->isBelowMinStock() ? '#fca5a5' : 'rgba(226,232,240,0.9)' }};">
          {{ number_format($item->totalStock(), 0) }}
        </td>
        <td class="text-center">
          @if($item->is_active)
          <span class="status-badge bg-emerald-50">Aktif</span>
          @else
          <span class="status-badge bg-slate-100">Nonaktif</span>
          @endif
        </td>
        <td class="text-center">
          <div class="flex items-center justify-center gap-1.5">
            <a href="{{ route('items.edit', $item) }}" class="btn-action-edit">Edit</a>
            <form method="POST" action="{{ route('items.destroy', $item) }}"
              class="inline" onsubmit="return confirm('Hapus item ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-action-delete">Hapus</button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="7" class="px-4 py-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
              style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
              <svg class="w-6 h-6" style="color:rgba(99,102,241,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <p class="text-sm" style="color:rgba(148,163,184,0.6);">Belum ada item.</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-3" style="border-top:1px solid rgba(255,255,255,0.06);">
    {{ $items->appends(request()->query())->links() }}
  </div>
</div>

@endsection