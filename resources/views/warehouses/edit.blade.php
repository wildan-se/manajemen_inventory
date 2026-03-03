@extends('layouts.app')
@section('title', 'Edit Gudang')
@section('content')

<x-modal id="wh-edit" title="Edit Gudang — {{ $warehouse->name }}" size="sm" back-url="{{ route('warehouses.index') }}">
  <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" class="space-y-4">
    @csrf @method('PUT')
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Kode <span class="modal-req">*</span></label>
        <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Nama Gudang <span class="modal-req">*</span></label>
        <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" class="modal-input" required>
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Alamat</label>
      <textarea name="address" rows="2" class="modal-textarea">{{ old('address', $warehouse->address) }}</textarea>
    </div>
    <div class="modal-field">
      <label class="modal-label">Deskripsi</label>
      <textarea name="description" rows="2" class="modal-textarea">{{ old('description', $warehouse->description) }}</textarea>
    </div>
    <div class="modal-check-row">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
      <label for="is_active">Aktif</label>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Perubahan</button>
      <a href="{{ route('warehouses.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection