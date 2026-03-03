<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SKBU Inventori') }} — Login</title>
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

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 8px 24px rgba(99,102,241,0.45);">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h1 class="text-xl font-bold" style="color:#e2e8f0;">SKBU Inventori</h1>
            <p class="text-sm mt-1" style="color:rgba(148,163,184,0.6);">Sistem Manajemen Inventori</p>
        </div>

        {{-- Session Error --}}
        @if (session('error'))
        <div class="mb-4 flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm"
            style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#fca5a5;">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl text-sm"
            style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#fca5a5;">
            @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" class="space-y-4" data-turbo="false">
            @csrf

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    placeholder="admin@skbu.com"
                    autocomplete="email" autofocus
                    class="login-input">
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="login-input">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer" style="color:rgba(148,163,184,0.7);font-size:0.8rem;margin:0;">
                    <input type="checkbox" name="remember" class="rounded"
                        style="accent-color:#6366f1;width:14px;height:14px;">
                    Ingat saya
                </label>
                {{--
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs" style="color:rgba(129,140,248,0.8);text-decoration:none;transition:color 0.15s;"
                onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='rgba(129,140,248,0.8)'">
                Lupa password?
                </a>
                @endif
                --}}
            </div>

            <div class="pt-2">
                <button type="submit" class="login-btn">
                    Masuk ke Sistem
                </button>
            </div>
        </form>

        <p class="text-center mt-6 text-xs" style="color:rgba(100,116,139,0.6);">
            &copy; {{ date('Y') }} SKBU — Sistem Inventori Perusahaan
        </p>
    </div>
</body>

</html>