@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('content')

<x-modal id="user-create" title="Tambah Pengguna Baru" size="sm" back-url="{{ route('users.index') }}">
  <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
    @csrf
    <div class="modal-field">
      <label class="modal-label">Nama <span class="modal-req">*</span></label>
      <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap..." class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Email <span class="modal-req">*</span></label>
      <input type="email" name="email" value="{{ old('email') }}" placeholder="email@domain.com" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Role <span class="modal-req">*</span></label>
      <select name="role" class="modal-select" required>
        <option value="">— Pilih Role —</option>
        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="inventory_controller" {{ old('role') == 'inventory_controller' ? 'selected' : '' }}>Inventory Controller</option>
        <option value="warehouse_operator" {{ old('role') == 'warehouse_operator' ? 'selected' : '' }}>Warehouse Operator</option>
        <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor / Manager</option>
        <option value="production_staff" {{ old('role') == 'production_staff' ? 'selected' : '' }}>Production Staff</option>
      </select>
    </div>
    <div class="modal-field">
      <label class="modal-label">Password <span class="modal-req">*</span></label>
      <input type="password" name="password" placeholder="Min. 8 karakter" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Konfirmasi Password <span class="modal-req">*</span></label>
      <input type="password" name="password_confirmation" placeholder="Ulangi password" class="modal-input" required>
    </div>
    <div class="modal-check-row">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
      <label for="is_active">Aktif</label>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Pengguna</button>
      <a href="{{ route('users.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection