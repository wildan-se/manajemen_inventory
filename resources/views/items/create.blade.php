@extends('layouts.app')
@section('title', 'Tambah Item')
@section('content')

<x-modal id="item-create" title="Tambah Item Baru" size="md" back-url="{{ route('items.index') }}">
  <form method="POST" action="{{ route('items.store') }}" class="space-y-4">
    @csrf
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Kode Item <span class="modal-req">*</span></label>
        <input type="text" name="code" value="{{ old('code') }}" placeholder="ITM-001" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Nama Item <span class="modal-req">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama barang..." class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Kategori <span class="modal-req">*</span></label>
        <select name="category_id" class="modal-select" required>
          <option value="">— Pilih Kategori —</option>
          @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="modal-field">
        <label class="modal-label">Satuan <span class="modal-req">*</span></label>
        <select name="unit_id" class="modal-select" required>
          <option value="">— Pilih Satuan —</option>
          @foreach($units as $unit)
          <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->abbreviation }})</option>
          @endforeach
        </select>
      </div>
      <div class="modal-field">
        <label class="modal-label">Stok Minimum</label>
        <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', 0) }}" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Stok Maksimum</label>
        <input type="number" step="0.01" name="max_stock" value="{{ old('max_stock', 0) }}" class="modal-input">
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Deskripsi</label>
      <textarea name="description" rows="2" class="modal-textarea" placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
    </div>
    <div class="modal-check-row">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
      <label for="is_active">Aktif</label>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Item</button>
      <a href="{{ route('items.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection