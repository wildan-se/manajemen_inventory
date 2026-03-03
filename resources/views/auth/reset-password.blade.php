<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SKBU Inventori') }} — Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #0d0b1e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Background glow orbs */
        body::before {
            content: '';
            position: fixed;
            top: -20%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -20%;
            right: -10%;
            width: 40vw;
            height: 40vw;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.10) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            position: relative;
            z-index: 1;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(99, 102, 241, 0.08);
        }

        .login-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 12px;
            padding: 11px 14px;
            font-size: 0.875rem;
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .login-input::placeholder {
            color: rgba(148, 163, 184, 0.45);
        }

        .login-input:focus {
            border-color: rgba(99, 102, 241, 0.6);
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 12px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 18px rgba(99, 102, 241, 0.4);
            letter-spacing: 0.01em;
            margin-top: 10px;
        }

        .login-btn:hover {
            box-shadow: 0 6px 24px rgba(99, 102, 241, 0.55);
            transform: translateY(-1px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(203, 213, 225, 0.8);
            margin-bottom: 6px;
        }
    </style>
</head>

<body>
    <div class="login-card">

        {{-- Logo & Deskripsi --}}
        <div class="flex flex-col items-center mb-6 text-center">
            <h1 class="text-xl font-bold" style="color:#e2e8f0;">Buat Password Baru</h1>
            <p class="text-sm mt-2 leading-relaxed" style="color:rgba(148,163,184,0.7);">
                Silakan atur password baru untuk akun Anda.
            </p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
        <div class="mb-5 px-4 py-3 rounded-xl text-sm"
            style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#fca5a5;">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                    required autofocus autocomplete="username"
                    class="login-input" readonly style="opacity:0.7; cursor:not-allowed;">
            </div>

            <!-- Password -->
            <div>
                <label for="password">Password Baru</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="login-input" placeholder="Minimal 8 karakter">
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="login-input" placeholder="Masukkan ulang password">
            </div>

            <button type="submit" class="login-btn">
                Simpan Password Baru
            </button>
        </form>

    </div>
</body>

</html>