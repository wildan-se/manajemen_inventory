<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
  public function index(Request $request)
  {
    $users = User::when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
      ->orWhere('email', 'like', "%{$request->search}%"))
      ->when($request->role, fn($q) => $q->where('role', $request->role))
      ->latest()->paginate(15)->withQueryString();

    $roles = User::ROLES;
    return view('users.index', compact('users', 'roles'));
  }

  public function create()
  {
    $roles = User::ROLES;
    return view('users.create', compact('roles'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name'      => 'required|string|max:255',
      'email'     => 'required|email|unique:users,email',
      'password'  => ['required', 'confirmed', Password::defaults()],
      'role'      => 'required|in:' . implode(',', array_keys(User::ROLES)),
      'is_active' => 'boolean',
    ]);

    $data['password']  = Hash::make($data['password']);
    $data['is_active'] = $request->boolean('is_active', true);
    User::create($data);

    return redirect()->route('users.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Akun berhasil diaktifkan' : 'Akun berhasil dinonaktifkan');
  }

  public function edit(User $user)
  {
    $roles = User::ROLES;
    return view('users.edit', compact('user', 'roles'));
  }

  public function update(Request $request, User $user)
  {
    $data = $request->validate([
      'name'      => 'required|string|max:255',
      'email'     => 'required|email|unique:users,email,' . $user->id,
      'password'  => ['nullable', 'confirmed', Password::defaults()],
      'role'      => 'required|in:' . implode(',', array_keys(User::ROLES)),
      'is_active' => 'boolean',
    ]);

    if (!empty($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    } else {
      unset($data['password']);
    }

    if ($user->id === auth()->id() && !$request->boolean('is_active')) {
      return back()->with('error', 'You cannot deactivate your own account.');
    }

    $data['is_active'] = $request->boolean('is_active');
    $user->update($data);

    return redirect()->route('users.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Akun berhasil diaktifkan' : 'Akun berhasil dinonaktifkan');
  }

  public function destroy(User $user)
  {
    if ($user->id === auth()->id()) {
      return back()->with('error', 'Cannot delete your own account.');
    }
    $user->delete();
    return redirect()->route('users.index')->with('success', 'User deleted.');
  }
}
