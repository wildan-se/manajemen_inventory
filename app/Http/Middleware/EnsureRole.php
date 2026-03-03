<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
    $user = $request->user();

    // Jika user ada TAPI tidak aktif
    if ($user && !$user->is_active) {
      // Log out user
      \Illuminate\Support\Facades\Auth::logout();

      // Invalidate session for security
      $request->session()->invalidate();
      $request->session()->regenerateToken();

      if ($request->expectsJson()) {
        return response()->json(['message' => 'Akses Ditolak. Akun Anda dinonaktifkan.'], 403);
      }

      // Redirect back to login with Toast error message
      return redirect()->route('login')->with('error', 'Akses Ditolak. Akun Anda telah dinonaktifkan oleh Administrator.');
    }

    // Jika guest (biasanya terhandle oleh middleware 'auth', tapi sebagai pengaman tambahan)
    if (!$user) {
      abort(403, 'Your account is inactive or not found.');
    }

    if (!empty($roles) && !$user->hasRole($roles)) {
      abort(403, 'You do not have permission to access this resource.');
    }

    // Proteksi global untuk role 'viewer' (Read Only)
    // Cegah semua request KECUALI method GET dan rute logout (POST)
    if ($user->role === 'viewer' && !$request->isMethod('GET')) {
      if (!$request->routeIs('logout')) {
        abort(403, 'Akses Ditolak. Anda masuk sebagai Viewer (Read-Only) dan tidak dapat memodifikasi data.');
      }
    }

    return $next($request);
  }
}
