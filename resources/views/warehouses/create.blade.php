@extends('layouts.app')
@section('title', 'Tambah Gudang')
@section('content')

<x-modal id="wh-create" title="Tambah Gudang Baru" size="sm" back-url="{{ route('warehouses.index') }}">
  <form method="POST" action="{{ route('warehouses.store') }}" class="space-y-4">
    @csrf
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Kode <span class="modal-req">*</span></label>
        <input type="text" name="code" value="{{ old('code') }}" placeholder="WH-01" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Nama Gudang <span class="modal-req">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama gudang..." class="modal-input" required>
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Alamat</label>
      <textarea name="address" rows="2" class="modal-textarea" placeholder="Alamat gudang...">{{ old('address') }}</textarea>
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
      <button type="submit" class="modal-btn-submit">Simpan Gudang</button>
      <a href="{{ route('warehouses.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection