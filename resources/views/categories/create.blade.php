@extends('layouts.app')
@section('title', 'Tambah Kategori')
@section('content')

<x-modal id="cat-create" title="Tambah Kategori Baru" size="sm" back-url="{{ route('categories.index') }}">
  <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
    @csrf
    <div class="modal-field">
      <label class="modal-label">Nama Kategori <span class="modal-req">*</span></label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kategori..." class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Deskripsi</label>
      <textarea name="description" rows="3" class="modal-textarea" placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Kategori</button>
      <a href="{{ route('categories.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection