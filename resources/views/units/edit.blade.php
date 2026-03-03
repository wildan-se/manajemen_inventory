@extends('layouts.app')
@section('title', 'Edit Satuan')
@section('content')

<x-modal id="unit-edit" title="Edit Satuan — {{ $unit->name }}" size="sm" back-url="{{ route('units.index') }}">
  <form method="POST" action="{{ route('units.update', $unit) }}" class="space-y-4">
    @csrf @method('PUT')
    <div class="modal-field">
      <label class="modal-label">Nama Satuan <span class="modal-req">*</span></label>
      <input type="text" name="name" value="{{ old('name', $unit->name) }}" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Singkatan <span class="modal-req">*</span></label>
      <input type="text" name="abbreviation" value="{{ old('abbreviation', $unit->abbreviation) }}" class="modal-input" required>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Perubahan</button>
      <a href="{{ route('units.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection