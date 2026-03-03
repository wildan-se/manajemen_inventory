@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
  <div>
    <h2 class="text-base font-semibold" style="color:#e2e8f0;">Manajemen Pengguna</h2>
    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Kelola akun dan hak akses pengguna sistem</p>
  </div>
  <a href="{{ route('users.create') }}" class="btn-primary">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Tambah Pengguna
  </a>
</div>

{{-- Table --}}
<div class="glass-card overflow-hidden">
  <table class="w-full text-sm data-table">
    <thead>
      <tr>
        <th class="text-left">Nama</th>
        <th class="text-left">Email</th>
        <th class="text-center">Role</th>
        <th class="text-center">Status</th>
        <th class="text-center">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
      <tr>
        {{-- Nama --}}
        <td>
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold text-white"
              style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 0 10px rgba(99,102,241,0.3);">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <span class="font-medium" style="color:#e2e8f0;">{{ $user->name }}</span>
          </div>
        </td>

        {{-- Email --}}
        <td style="color:rgba(148,163,184,0.8);">{{ $user->email }}</td>

        {{-- Role badge --}}
        <td class="text-center">
          @php
          $roleStyle = match($user->role) {
          'admin' => ['bg'=>'rgba(139,92,246,0.15)', 'color'=>'#c4b5fd', 'border'=>'rgba(139,92,246,0.25)'],
          'supervisor' => ['bg'=>'rgba(59,130,246,0.15)', 'color'=>'#93c5fd', 'border'=>'rgba(59,130,246,0.25)'],
          'inventory_controller' => ['bg'=>'rgba(99,102,241,0.15)', 'color'=>'#a5b4fc', 'border'=>'rgba(99,102,241,0.25)'],
          'warehouse_operator' => ['bg'=>'rgba(20,184,166,0.15)', 'color'=>'#5eead4', 'border'=>'rgba(20,184,166,0.25)'],
          'production_staff' => ['bg'=>'rgba(249,115,22,0.15)', 'color'=>'#fdba74', 'border'=>'rgba(249,115,22,0.25)'],
          default => ['bg'=>'rgba(255,255,255,0.06)', 'color'=>'rgba(148,163,184,0.9)', 'border'=>'rgba(255,255,255,0.10)'],
          };
          @endphp
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
            style="background:{{ $roleStyle['bg'] }};color:{{ $roleStyle['color'] }};border:1px solid {{ $roleStyle['border'] }};">
            {{ $user->role_label }}
          </span>
        </td>

        {{-- Status --}}
        <td class="text-center">
          @if($user->is_active)
          <span class="status-badge bg-emerald-50">Aktif</span>
          @else
          <span class="status-badge bg-slate-100">Nonaktif</span>
          @endif
        </td>

        {{-- Aksi --}}
        <td class="text-center">
          <div class="flex items-center justify-center gap-1.5">
            <a href="{{ route('users.edit', $user) }}" class="btn-action-edit">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Edit
            </a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('users.destroy', $user) }}"
              class="inline" onsubmit="return confirm('Hapus pengguna ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="btn-action-delete">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
              </button>
            </form>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="px-4 py-12 text-center">
          <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
              style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
              <svg class="w-6 h-6" style="color:rgba(99,102,241,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <p class="text-sm" style="color:rgba(148,163,184,0.6);">Belum ada pengguna.</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>

  @if($users->hasPages())
  <div class="px-5 py-3" style="border-top:1px solid rgba(255,255,255,0.06);">
    {{ $users->links() }}
  </div>
  @endif
</div>

@endsection