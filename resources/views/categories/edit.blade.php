@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('content')

<x-modal id="cat-edit" title="Edit Kategori — {{ $category->name }}" size="sm" back-url="{{ route('categories.index') }}">
  <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-4">
    @csrf @method('PUT')
    <div class="modal-field">
      <label class="modal-label">Nama Kategori <span class="modal-req">*</span></label>
      <input type="text" name="name" value="{{ old('name', $category->name) }}" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Deskripsi</label>
      <textarea name="description" rows="3" class="modal-textarea">{{ old('description', $category->description) }}</textarea>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Perubahan</button>
      <a href="{{ route('categories.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection