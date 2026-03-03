@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('content')

<x-modal id="user-edit" title="Edit Pengguna — {{ $user->name }}" size="sm" back-url="{{ route('users.index') }}">
  <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
    @csrf @method('PUT')
    <div class="modal-field">
      <label class="modal-label">Nama <span class="modal-req">*</span></label>
      <input type="text" name="name" value="{{ old('name', $user->name) }}" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Email <span class="modal-req">*</span></label>
      <input type="email" name="email" value="{{ old('email', $user->email) }}" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Role <span class="modal-req">*</span></label>
      <select name="role" class="modal-select" required>
        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="inventory_controller" {{ old('role', $user->role) == 'inventory_controller' ? 'selected' : '' }}>Inventory Controller</option>
        <option value="warehouse_operator" {{ old('role', $user->role) == 'warehouse_operator' ? 'selected' : '' }}>Warehouse Operator</option>
        <option value="supervisor" {{ old('role', $user->role) == 'supervisor' ? 'selected' : '' }}>Supervisor / Manager</option>
        <option value="production_staff" {{ old('role', $user->role) == 'production_staff' ? 'selected' : '' }}>Production Staff</option>
      </select>
    </div>
    <div class="modal-field">
      <label class="modal-label">Password Baru <span style="color:rgba(148,163,184,0.5);font-size:0.72rem;">(kosongkan jika tidak diubah)</span></label>
      <input type="password" name="password" placeholder="Biarkan kosong..." class="modal-input">
    </div>
    <div class="modal-field">
      <label class="modal-label">Konfirmasi Password</label>
      <input type="password" name="password_confirmation" placeholder="Ulangi password baru..." class="modal-input">
    </div>
    <div class="modal-check-row">
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
      <label for="is_active">Aktif</label>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Perubahan</button>
      <a href="{{ route('users.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection