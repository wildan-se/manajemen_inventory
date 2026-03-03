@extends('layouts.app')
@section('title', 'Tambah Supplier')
@section('content')

<x-modal id="sup-create" title="Tambah Supplier Baru" size="md" back-url="{{ route('suppliers.index') }}">
  <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
    @csrf
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Kode Supplier <span class="modal-req">*</span></label>
        <input type="text" name="code" value="{{ old('code') }}" placeholder="SUP-001" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Nama Supplier <span class="modal-req">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama perusahaan..." class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="supplier@email.com" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xx-xxxx-xxxx" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Contact Person</label>
        <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Nama PIC..." class="modal-input">
      </div>
      <div class="modal-field" style="justify-content:flex-end;">
        <div class="modal-check-row" style="margin-top:auto;padding-top:24px;">
          <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
          <label for="is_active">Aktif</label>
        </div>
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Alamat</label>
      <textarea name="address" rows="2" class="modal-textarea" placeholder="Alamat supplier...">{{ old('address') }}</textarea>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Supplier</button>
      <a href="{{ route('suppliers.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection