<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SKBU Inventori') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="turbo-cache-control" content="no-preview">

    @if(auth()->check() && auth()->user()->role === 'viewer')
    <style>
        /* [Mode Viewer] Sembunyikan semua elemen aksi / penulisan untuk Role Viewer */
        a[href*="/create"],
        a[href*="/edit"],
        button.btn-primary,
        form[method="POST"]:not([action*="logout"]) button[type="submit"],
        .actions-dropdown button[type="submit"],
        .actions-dropdown a {
            display: none !important;
        }
    </style>
    @endif
</head>

<body class="font-sans antialiased" style="background:#0d0b1e; color:#e2e8f0; min-height:100vh;">

    <div class="flex h-screen overflow-hidden" id="app-shell">

        {{-- ═══════════════════════════ SIDEBAR ═══════════════════════════ --}}
        <aside id="sidebar"
            data-turbo-permanent
            style="
                background:linear-gradient(180deg,#130d2e 0%,#0d0b1e 100%);
                border-right:1px solid rgba(255,255,255,0.07);
                display:flex;
                flex-direction:column;
                flex-shrink:0;
                width:240px;
                overflow:hidden;
                transition:width 0.3s cubic-bezier(0.4,0,0.2,1);
                position:relative;
                z-index:20;
            ">

            {{-- Logo + Toggle Button --}}
            <div style="
                display:flex;
                align-items:center;
                justify-content:space-between;
                padding:16px 12px 14px 14px;
                border-bottom:1px solid rgba(255,255,255,0.06);
                flex-shrink:0;
                min-height:60px;
            ">
                {{-- Logo --}}
                <div class="sidebar-logo-full" style="display:flex;align-items:center;gap:9px;overflow:hidden;">
                    <div style="
                        width:32px;height:32px;border-radius:10px;
                        background:linear-gradient(135deg,#6366f1,#8b5cf6);
                        box-shadow:0 4px 14px rgba(99,102,241,0.4);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;
                    ">
                        <svg style="width:16px;height:16px;color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="sidebar-text-block" style="overflow:hidden;transition:opacity 0.2s,width 0.3s;">
                        <span style="font-weight:700;color:white;font-size:0.875rem;display:block;white-space:nowrap;line-height:1.2;">SKBU</span>
                        <span style="font-size:0.7rem;color:rgba(148,163,184,0.6);white-space:nowrap;">Sistem Inventori</span>
                    </div>
                </div>

                {{-- Toggle Button --}}
                <button id="sidebar-toggle"
                    onclick="toggleSidebar()"
                    title="Toggle sidebar"
                    style="
                        width:28px;height:28px;border-radius:8px;border:1px solid rgba(255,255,255,0.09);
                        background:rgba(255,255,255,0.05);
                        color:rgba(148,163,184,0.6);
                        display:flex;align-items:center;justify-content:center;
                        cursor:pointer;flex-shrink:0;
                        transition:all 0.2s ease;
                    "
                    onmouseover="this.style.background='rgba(99,102,241,0.2)';this.style.borderColor='rgba(99,102,241,0.3)';this.style.color='#a5b4fc';"
                    onmouseout="this.style.background='rgba(255,255,255,0.05)';this.style.borderColor='rgba(255,255,255,0.09)';this.style.color='rgba(148,163,184,0.6)';">
                    <svg id="toggle-icon-open" style="width:14px;height:14px;transition:transform 0.3s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M19 19l-7-7 7-7" />
                    </svg>
                    <svg id="toggle-icon-closed" style="width:14px;height:14px;display:none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav id="sidebar-nav" style="flex:1;padding:8px 8px 12px;overflow-y:auto;overflow-x:hidden;" class="sidebar-scroll">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                    class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}"
                    data-tooltip="Dashboard"
                    data-active-segment="/dashboard"
                    data-active-exact="true">
                    <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>

                {{-- Section: Inventori --}}
                <div class="nav-section">
                    <p class="nav-section-label"><span class="nav-section-text">Inventori</span></p>
                    <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Items / Material" data-active-segment="/items">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <span class="nav-label">Items / Material</span>
                    </a>
                    <a href="{{ route('stock-movements.index') }}" class="nav-link {{ request()->routeIs('stock-movements.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Mutasi Stok" data-active-segment="/stock-movements">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                        <span class="nav-label">Mutasi Stok</span>
                    </a>
                    <a href="{{ route('stock-opnames.index') }}" class="nav-link {{ request()->routeIs('stock-opnames.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Stock Opname" data-active-segment="/stock-opnames">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="nav-label">Stock Opname</span>
                    </a>
                </div>

                {{-- Section: Pengadaan --}}
                <div class="nav-section">
                    <p class="nav-section-label"><span class="nav-section-text">Pengadaan</span></p>
                    <a href="{{ route('purchase-orders.index') }}" class="nav-link {{ request()->routeIs('purchase-orders.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Purchase Order" data-active-segment="/purchase-orders">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="nav-label">Purchase Order</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Supplier" data-active-segment="/suppliers">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="nav-label">Supplier</span>
                    </a>
                </div>

                {{-- Section: Produksi --}}
                <div class="nav-section">
                    <p class="nav-section-label"><span class="nav-section-text">Produksi</span></p>
                    <a href="{{ route('production-orders.index') }}" class="nav-link {{ request()->routeIs('production-orders.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Work Order" data-active-segment="/production-orders">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="nav-label">Work Order</span>
                    </a>
                </div>

                {{-- Section: Laporan --}}
                <div class="nav-section">
                    <p class="nav-section-label"><span class="nav-section-text">Laporan</span></p>
                    <a href="{{ route('reports.stock-summary') }}" class="nav-link {{ request()->routeIs('reports.stock-summary') ? 'nav-link-active' : '' }}"
                        data-tooltip="Ringkasan Stok" data-active-segment="/reports/stock-summary" data-active-exact="true">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="nav-label">Ringkasan Stok</span>
                    </a>
                    <a href="{{ route('reports.low-stock') }}" class="nav-link {{ request()->routeIs('reports.low-stock') ? 'nav-link-active' : '' }}"
                        data-tooltip="Stok Minimum" data-active-segment="/reports/low-stock" data-active-exact="true">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="nav-label">Stok Minimum</span>
                    </a>
                    <a href="{{ route('reports.movement-history') }}" class="nav-link {{ request()->routeIs('reports.movement-history') ? 'nav-link-active' : '' }}"
                        data-tooltip="Riwayat Mutasi" data-active-segment="/reports/movement-history" data-active-exact="true">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="nav-label">Riwayat Mutasi</span>
                    </a>
                </div>

                {{-- Section: Master Data --}}
                <div class="nav-section">
                    <p class="nav-section-label"><span class="nav-section-text">Master Data</span></p>
                    <a href="{{ route('warehouses.index') }}" class="nav-link {{ request()->routeIs('warehouses.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Gudang" data-active-segment="/warehouses">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="nav-label">Gudang</span>
                    </a>
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Kategori" data-active-segment="/categories">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span class="nav-label">Kategori</span>
                    </a>
                    <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Satuan (UoM)" data-active-segment="/units">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                        <span class="nav-label">Satuan (UoM)</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'nav-link-active' : '' }}"
                        data-tooltip="Pengguna" data-active-segment="/users">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="nav-label">Pengguna</span>
                    </a>
                    @endif
                </div>

            </nav>

            {{-- User info at bottom --}}
            <div id="sidebar-user" style="
                padding:10px 8px 12px;
                border-top:1px solid rgba(255,255,255,0.06);
                flex-shrink:0;
                overflow:hidden;
            ">
                <div style="display:flex;align-items:center;gap:9px;">
                    <div style="
                        width:32px;height:32px;border-radius:9px;
                        background:linear-gradient(135deg,#6366f1,#8b5cf6);
                        display:flex;align-items:center;justify-content:center;
                        font-size:0.68rem;font-weight:700;color:white;flex-shrink:0;
                        box-shadow:0 2px 8px rgba(99,102,241,0.3);
                    ">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="sidebar-user-info" style="flex:1;min-width:0;overflow:hidden;transition:opacity 0.2s,width 0.3s;">
                        <p style="font-size:0.8rem;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ auth()->user()->name }}
                        </p>
                        <p style="font-size:0.68rem;color:rgba(148,163,184,0.55);white-space:nowrap;margin-top:1px;">
                            {{ auth()->user()->role_label }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="sidebar-user-info" style="flex-shrink:0;overflow:hidden;transition:opacity 0.2s;" data-turbo="false">
                        @csrf
                        <button type="submit" title="Logout"
                            style="
                                width:28px;height:28px;display:flex;align-items:center;justify-content:center;
                                border-radius:8px;border:1px solid rgba(255,255,255,0.08);
                                background:rgba(255,255,255,0.04);
                                color:rgba(148,163,184,0.5);
                                cursor:pointer;transition:all 0.2s;
                            "
                            onmouseover="this.style.background='rgba(239,68,68,0.15)';this.style.borderColor='rgba(239,68,68,0.25)';this.style.color='#fca5a5'"
                            onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.borderColor='rgba(255,255,255,0.08)';this.style.color='rgba(148,163,184,0.5)'">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ═══════════════════════════ MAIN CONTENT ═══════════════════════════ --}}
        <div class="flex-1 flex flex-col overflow-hidden" style="min-width:0;">

            {{-- Top Header --}}
            <header style="background:rgba(13,11,30,0.85);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.06);z-index:10;flex-shrink:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 24px 11px;">
                    <div>
                        <h1 style="font-size:0.9rem;font-weight:600;color:#e2e8f0;margin:0;letter-spacing:-0.01em;">@yield('title', 'Dashboard')</h1>
                        <p style="font-size:0.7rem;margin-top:3px;color:rgba(148,163,184,0.5);">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="
                            font-size:0.72rem;font-weight:500;
                            padding:4px 10px;border-radius:999px;
                            background:rgba(99,102,241,0.12);
                            color:#a5b4fc;
                            border:1px solid rgba(99,102,241,0.2);
                        ">{{ auth()->user()->role_label }}</span>
                    </div>
                </div>
            </header>


            {{-- Page Content --}}
            <main style="flex:1;overflow-y:auto;padding:22px 28px 28px;">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')

    <style>
        /* ── Nav Link Base ── */
        .nav-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 0.79rem;
            font-weight: 500;
            color: rgba(148, 163, 184, 0.75);
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #e2e8f0;
        }

        .nav-link-active {
            background: rgba(99, 102, 241, 0.15) !important;
            color: #a5b4fc !important;
            border-color: rgba(99, 102, 241, 0.22) !important;
        }

        .nav-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .nav-label {
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }

        /* ── Nav Section ── */
        .nav-section {
            padding-top: 18px;
        }

        .nav-section-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(100, 116, 139, 0.5);
            padding: 0 10px 5px;
            display: block;
            overflow: hidden;
        }

        .nav-section-text {
            white-space: nowrap;
            transition: opacity 0.2s;
        }

        /* ── Scrollbar ── */
        .sidebar-scroll::-webkit-scrollbar {
            width: 3px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 2px;
        }

        /* ── Tooltip (only when collapsed) ── */
        .sidebar-collapsed .nav-link[data-tooltip]::after {
            content: attr(data-tooltip);
            position: fixed;
            left: 68px;
            background: rgba(15, 13, 36, 0.97);
            backdrop-filter: blur(12px);
            color: #e2e8f0;
            font-size: 0.78rem;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s, transform 0.15s;
            transform: translateX(-6px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }

        .sidebar-collapsed .nav-link[data-tooltip]:hover::after {
            opacity: 1;
            transform: translateX(0);
        }

        /* ── Collapsed State ── */
        #sidebar.sidebar-collapsed {
            width: 64px !important;
        }

        #sidebar.sidebar-collapsed .nav-label {
            opacity: 0;
            width: 0;
        }

        #sidebar.sidebar-collapsed .nav-section-text {
            opacity: 0;
        }

        #sidebar.sidebar-collapsed .sidebar-text-block {
            opacity: 0;
            width: 0;
        }

        #sidebar.sidebar-collapsed .sidebar-user-info {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        #sidebar.sidebar-collapsed .nav-link {
            justify-content: center;
            padding: 9px 8px;
        }

        #sidebar.sidebar-collapsed .nav-section-label {
            padding: 0 4px 4px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: 4px;
        }

        #sidebar.sidebar-collapsed #sidebar-user>div {
            justify-content: center;
        }

        /* Pulse indicator for active link when collapsed */
        #sidebar.sidebar-collapsed .nav-link-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: #6366f1;
            border-radius: 0 2px 2px 0;
        }
    </style>

    <script>
        // ── Sidebar toggle ──
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isCollapsed);

            // Swap icons
            document.getElementById('toggle-icon-open').style.display = isCollapsed ? 'none' : 'block';
            document.getElementById('toggle-icon-closed').style.display = isCollapsed ? 'block' : 'none';
        }

        // ── Restore state on load ──
        (function() {
            const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            if (collapsed) {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.add('sidebar-collapsed');
                sidebar.style.transition = 'none'; // avoid flash on load
                requestAnimationFrame(() => {
                    sidebar.style.transition = '';
                });
                document.getElementById('toggle-icon-open').style.display = 'none';
                document.getElementById('toggle-icon-closed').style.display = 'block';
            }
        })();

        // ── Keyboard shortcut: [ to toggle ──
        document.addEventListener('keydown', function(e) {
            if (e.key === '[' && !e.ctrlKey && !e.metaKey && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
                toggleSidebar();
            }
        });
    </script>

    {{-- ═══ TOAST NOTIFICATIONS ═══ --}}
    <div id="toast-container">
        @if(session('success'))
        <div class="alert-toast bg-emerald-50" role="alert">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span style="flex:1;line-height:1.4;">{{ session('success') }}</span>
            <button class="toast-close" onclick="dismissToast(this.closest('.alert-toast'))" aria-label="Tutup">
                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="alert-progress" style="background:rgba(16,185,129,0.55);"></div>
        </div>
        @endif
        @if(session('warning'))
        <div class="alert-toast toast-warning" role="alert">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span style="flex:1;line-height:1.4;">{{ session('warning') }}</span>
            <button class="toast-close" onclick="dismissToast(this.closest('.alert-toast'))" aria-label="Tutup">
                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="alert-progress" style="background:rgba(245,158,11,0.55);"></div>
        </div>
        @endif
        @if(session('error'))
        <div class="alert-toast bg-red-50" role="alert">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span style="flex:1;line-height:1.4;">{{ session('error') }}</span>
            <button class="toast-close" onclick="dismissToast(this.closest('.alert-toast'))" aria-label="Tutup">
                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="alert-progress" style="background:rgba(239,68,68,0.55);"></div>
        </div>
        @endif
        @if($errors->any())
        <div class="alert-toast bg-red-50" role="alert">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" clip-rule="evenodd" />
            </svg>
            <div style="flex:1;line-height:1.5;">
                <p style="font-weight:600;margin-bottom:4px;">Terdapat {{ $errors->count() }} kesalahan:</p>
                <ul style="list-style:disc;padding-left:16px;font-size:0.8rem;opacity:0.85;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            <button class="toast-close" onclick="dismissToast(this.closest('.alert-toast'))" aria-label="Tutup">
                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif
    </div>

    <script>
        function dismissToast(el) {
            if (!el || el.classList.contains('toast-hiding')) return;
            el.classList.add('toast-hiding');
            setTimeout(() => el.remove(), 310);
        }

        // Auto-dismiss setelah 5 detik
        document.addEventListener('DOMContentLoaded', () => initToasts());
        document.addEventListener('turbo:load', () => initToasts());

        function initToasts() {
            document.querySelectorAll('#toast-container .alert-toast').forEach((toast, i) => {
                // Tunda dismiss agar tiap toast bisa terbaca (stagger 1.5s per toast)
                const delay = 5000 + (i * 1500);
                setTimeout(() => dismissToast(toast), delay);
            });
        }
    </script>
</body>

</html>