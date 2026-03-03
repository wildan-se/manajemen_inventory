@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('content')

<x-modal id="sup-edit" title="Edit Supplier — {{ $supplier->name }}" size="md" back-url="{{ route('suppliers.index') }}">
  <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4">
    @csrf @method('PUT')
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Kode Supplier <span class="modal-req">*</span></label>
        <input type="text" name="code" value="{{ old('code', $supplier->code) }}" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Nama Supplier <span class="modal-req">*</span></label>
        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Contact Person</label>
        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="modal-input">
      </div>
      <div class="modal-field" style="justify-content:flex-end;">
        <div class="modal-check-row" style="margin-top:auto;padding-top:24px;">
          <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
          <label for="is_active">Aktif</label>
        </div>
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Alamat</label>
      <textarea name="address" rows="2" class="modal-textarea">{{ old('address', $supplier->address) }}</textarea>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Perubahan</button>
      <a href="{{ route('suppliers.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection