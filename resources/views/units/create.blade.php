@extends('layouts.app')
@section('title', 'Tambah Satuan')
@section('content')

<x-modal id="unit-create" title="Tambah Satuan Baru" size="sm" back-url="{{ route('units.index') }}">
  <form method="POST" action="{{ route('units.store') }}" class="space-y-4">
    @csrf
    <div class="modal-field">
      <label class="modal-label">Nama Satuan <span class="modal-req">*</span></label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="contoh: Kilogram" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Singkatan <span class="modal-req">*</span></label>
      <input type="text" name="abbreviation" value="{{ old('abbreviation') }}" placeholder="contoh: kg" class="modal-input" required>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Satuan</button>
      <a href="{{ route('units.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection