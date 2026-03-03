@extends('layouts.app')
@section('title', 'Edit Item')
@section('content')

<x-modal id="item-edit" title="Edit Item — {{ $item->name }}" size="md" back-url="{{ route('items.index') }}">
  <form method="POST" action="{{ route('items.update', $item) }}" class="space-y-4">
    @csrf @method('PUT')
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Kode Item <span class="modal-req">*</span></label>
        <input type="text" name="code" value="{{ old('code', $item->code) }}" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Nama Item <span class="modal-req">*</span></label>
        <input type="text" name="name" value="{{ old('name', $item->name) }}" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Kategori <span class="modal-req">*</span></label>
        <select name="category_id" class="modal-select" required>
          <option value="">— Pilih Kategori —</option>
          @foreach($categories as $cat)
          <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="modal-field">
        <label class="modal-label">Satuan <span class="modal-req">*</span></label>
        <select name="unit_id" class="modal-select" required>
          <option value="">— Pilih Satuan —</option>
          @foreach($units as $unit)
          <option value="{{ $unit->id }}" {{ old('unit_id', $item->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->name }} ({{ $unit->abbreviation }})</option>
          @endforeach
        </select>
      </div>
      <div class="modal-field">
        <label class="modal-label">Stok Minimum</label>
        <input type="number" step="0.01" name="min_stock" value="{{ old('min_stock', $item->min_stock) }}" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Stok Maksimum</label>
        <input type="number" step="0.01" name="max_stock" value="{{ old('max_stock', $item->max_stock) }}" class="modal-input">
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Deskripsi</label>
      <textarea name="description" rows="2" class="modal-textarea">{{ old('description', $item->description) }}</textarea>
    </div>
    <div class="modal-check-row">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
      <label for="is_active">Aktif</label>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Perubahan</button>
      <a href="{{ route('items.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection