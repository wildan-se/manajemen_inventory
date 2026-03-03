@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total Items --}}
    <div class="glass-card p-5 flex items-center gap-4 glass-stat-indigo hover-lift">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
            style="background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.2);">
            <svg class="w-5 h-5" style="color:#a5b4fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium" style="color:rgba(148,163,184,0.7);">Total Item Aktif</p>
            <p class="text-2xl font-bold mt-0.5" style="color:#e2e8f0;">{{ $totalItems }}</p>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="glass-card p-5 flex items-center gap-4 glass-stat-amber hover-lift">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
            style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.18);">
            <svg class="w-5 h-5" style="color:#fcd34d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium" style="color:rgba(148,163,184,0.7);">Stok di Bawah Minimum</p>
            <p class="text-2xl font-bold mt-0.5" style="color:#fcd34d;">{{ $lowStockItems->count() }}</p>
        </div>
    </div>

    {{-- Pending POs --}}
    <div class="glass-card p-5 flex items-center gap-4 glass-stat-violet hover-lift">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
            style="background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.18);">
            <svg class="w-5 h-5" style="color:#c4b5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium" style="color:rgba(148,163,184,0.7);">PO Pending</p>
            <p class="text-2xl font-bold mt-0.5" style="color:#c4b5fd;">{{ $pendingPOs }}</p>
        </div>
    </div>

    {{-- Active WOs --}}
    <div class="glass-card p-5 flex items-center gap-4 glass-stat-emerald hover-lift">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
            style="background:rgba(16,185,129,0.10);border:1px solid rgba(16,185,129,0.16);">
            <svg class="w-5 h-5" style="color:#6ee7b7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium" style="color:rgba(148,163,184,0.7);">Work Order Aktif</p>
            <p class="text-2xl font-bold mt-0.5" style="color:#6ee7b7;">{{ $pendingWOs }}</p>
        </div>
    </div>
</div>

{{-- ═══ CHART ROW 1: Line (tren 30 hari) + Doughnut (distribusi tipe) ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Chart 1: Line — Tren Mutasi 30 Hari --}}
    <div class="glass-card lg:col-span-2 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                    style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.2);">
                    <svg class="w-3.5 h-3.5" style="color:#a5b4fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:#e2e8f0;">Tren Mutasi Stok</h2>
                    <p class="text-xs" style="color:rgba(148,163,184,0.5);">30 hari terakhir</p>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs" style="color:rgba(148,163,184,0.7);">
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:12px;height:3px;border-radius:2px;background:#6ee7b7;"></span>Inbound
                </span>
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:12px;height:3px;border-radius:2px;background:#f87171;"></span>Outbound
                </span>
            </div>
        </div>
        <div class="px-4 pt-3 pb-2" style="height:240px;">
            <canvas id="chartTrend"></canvas>
        </div>
    </div>

    {{-- Chart 2: Doughnut — Distribusi Tipe Mutasi --}}
    <div class="glass-card overflow-hidden">
        <div class="px-5 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                    style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.2);">
                    <svg class="w-3.5 h-3.5" style="color:#fcd34d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:#e2e8f0;">Distribusi Mutasi</h2>
                    <p class="text-xs" style="color:rgba(148,163,184,0.5);">Bulan {{ now()->translatedFormat('F Y') }}</p>
                </div>
            </div>
        </div>
        <div class="px-4 pt-3 pb-2 flex flex-col items-center justify-center" style="height:240px;">
            <canvas id="chartTypes" style="max-height:170px;"></canvas>
            <p id="chartTypesEmpty" class="text-sm mt-4 hidden" style="color:rgba(148,163,184,0.5);">Belum ada data bulan ini</p>
        </div>
    </div>
</div>

{{-- ═══ CHART ROW 2: Horizontal Bar (top stok) + Grouped Bar (status PO/WO) ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

    {{-- Chart 3: Horizontal Bar — Top 10 Item Stok --}}
    <div class="glass-card overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.2);">
                <svg class="w-3.5 h-3.5" style="color:#6ee7b7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold" style="color:#e2e8f0;">Top 10 Stok Tertinggi</h2>
                <p class="text-xs" style="color:rgba(148,163,184,0.5);">Berdasarkan total stok aktif</p>
            </div>
        </div>
        <div class="px-4 pt-3 pb-2" style="height:300px;">
            <canvas id="chartTopStock"></canvas>
        </div>
    </div>

    {{-- Chart 4: Grouped Bar — Status PO & Work Order --}}
    <div class="glass-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                    style="background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.2);">
                    <svg class="w-3.5 h-3.5" style="color:#c4b5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold" style="color:#e2e8f0;">Status PO & Work Order</h2>
                    <p class="text-xs" style="color:rgba(148,163,184,0.5);">Pipeline procurement & produksi</p>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs" style="color:rgba(148,163,184,0.7);">
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#818cf8;"></span>PO
                </span>
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#34d399;"></span>WO
                </span>
            </div>
        </div>
        <div class="px-4 pt-3 pb-2" style="height:300px;">
            <canvas id="chartOrders"></canvas>
        </div>
    </div>
</div>

{{-- ═══ DATA PANELS: Low Stock + Recent Movements ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Low Stock Warning --}}
    <div class="glass-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                    style="background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.18);">
                    <svg class="w-3.5 h-3.5" style="color:#fcd34d;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h2 class="text-sm font-semibold" style="color:#e2e8f0;">Peringatan Stok Minimum</h2>
            </div>
            <a href="{{ route('reports.low-stock') }}" class="text-xs font-medium"
                style="color:rgba(129,140,248,0.8);transition:color 0.15s;"
                onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='rgba(129,140,248,0.8)'">
                Lihat semua →
            </a>
        </div>
        <div>
            @forelse($lowStockItems->take(6) as $item)
            <div class="flex items-center justify-between px-5 py-3.5" style="border-bottom:1px solid rgba(255,255,255,0.04);">
                <div>
                    <p class="text-sm font-medium" style="color:#e2e8f0;">{{ $item->name }}</p>
                    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">{{ $item->category->name ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold" style="color:#fca5a5;">
                        {{ number_format($item->totalStock(), 2) }} {{ $item->unit->abbreviation }}
                    </p>
                    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.55);">Min: {{ number_format($item->min_stock, 2) }}</p>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" style="color:rgba(16,185,129,0.4);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm" style="color:rgba(148,163,184,0.6);">Semua stok di atas batas minimum ✓</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Recent Movements --}}
    <div class="glass-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                    style="background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.18);">
                    <svg class="w-3.5 h-3.5" style="color:#a5b4fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                </div>
                <h2 class="text-sm font-semibold" style="color:#e2e8f0;">Mutasi Stok Terbaru</h2>
            </div>
            <a href="{{ route('stock-movements.index') }}" class="text-xs font-medium"
                style="color:rgba(129,140,248,0.8);transition:color 0.15s;"
                onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='rgba(129,140,248,0.8)'">
                Lihat semua →
            </a>
        </div>
        <div>
            @forelse($recentMovements as $mv)
            <div class="flex items-center justify-between px-5 py-3.5" style="border-bottom:1px solid rgba(255,255,255,0.04);">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium truncate" style="color:#e2e8f0;">{{ $mv->item->name }}</p>
                    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">
                        {{ $mv->type_label }} &bull; {{ $mv->user->name }}
                    </p>
                </div>
                <div class="text-right ml-4 flex-shrink-0">
                    <p class="text-sm font-semibold" style="color:rgba(226,232,240,0.9);">
                        {{ number_format($mv->quantity, 2) }}
                    </p>
                    <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.5);">{{ $mv->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm" style="color:rgba(148,163,184,0.6);">Belum ada mutasi stok.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══ CHART.JS CDN + Init ═══ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" data-turbo-track="reload"></script>
<script>
    (function initDashboardCharts() {
        if (typeof Chart === 'undefined') return;

        // ── Shared chart defaults (dark theme) ──────────────────────────
        const GRID = 'rgba(255,255,255,0.05)';
        const TICK = 'rgba(148,163,184,0.55)';

        Chart.defaults.color = TICK;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(12,18,38,0.95)';
        Chart.defaults.plugins.tooltip.titleColor = '#e2e8f0';
        Chart.defaults.plugins.tooltip.bodyColor = 'rgba(148,163,184,0.9)';
        Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.08)';
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";

        // ── Data injected from PHP ───────────────────────────────────────
        const trend = @json($chartTrend);
        const types = @json($chartTypes);
        const topStock = @json($chartTopStock);
        const orders = @json($chartOrders);

        // ────────────────────────────────────────────────────────────────
        // CHART 1: Line — Tren Mutasi Stok 30 Hari
        // ────────────────────────────────────────────────────────────────
        const c1 = document.getElementById('chartTrend');
        if (c1) {
            const ctx = c1.getContext('2d');
            const gIn = ctx.createLinearGradient(0, 0, 0, 200);
            gIn.addColorStop(0, 'rgba(110,231,183,0.28)');
            gIn.addColorStop(1, 'rgba(110,231,183,0)');
            const gOut = ctx.createLinearGradient(0, 0, 0, 200);
            gOut.addColorStop(0, 'rgba(248,113,113,0.22)');
            gOut.addColorStop(1, 'rgba(248,113,113,0)');

            new Chart(c1, {
                type: 'line',
                data: {
                    labels: trend.labels,
                    datasets: [{
                            label: 'Inbound',
                            data: trend.inbound,
                            borderColor: '#6ee7b7',
                            backgroundColor: gIn,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#6ee7b7',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        },
                        {
                            label: 'Outbound',
                            data: trend.outbound,
                            borderColor: '#f87171',
                            backgroundColor: gOut,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointHoverBackgroundColor: '#f87171',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2,
                            tension: 0.4,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('id-ID')}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: GRID
                            },
                            ticks: {
                                color: TICK,
                                font: {
                                    size: 10
                                },
                                maxTicksLimit: 8,
                                maxRotation: 0
                            },
                            border: {
                                display: false
                            },
                        },
                        y: {
                            grid: {
                                color: GRID
                            },
                            ticks: {
                                color: TICK,
                                font: {
                                    size: 10
                                }
                            },
                            border: {
                                display: false
                            },
                            beginAtZero: true,
                        },
                    },
                },
            });
        }

        // ────────────────────────────────────────────────────────────────
        // CHART 2: Doughnut — Distribusi Tipe Mutasi
        // ────────────────────────────────────────────────────────────────
        const c2 = document.getElementById('chartTypes');
        if (c2) {
            if (!types.labels || types.labels.length === 0) {
                const el = document.getElementById('chartTypesEmpty');
                if (el) el.classList.remove('hidden');
                c2.style.display = 'none';
            } else {
                const palette = ['#818cf8', '#6ee7b7', '#fcd34d', '#f87171', '#c4b5fd', '#67e8f9', '#fb923c'];
                new Chart(c2, {
                    type: 'doughnut',
                    data: {
                        labels: types.labels,
                        datasets: [{
                            data: types.data,
                            backgroundColor: palette.slice(0, types.labels.length),
                            borderColor: 'rgba(12,18,38,0.9)',
                            borderWidth: 2,
                            hoverOffset: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: TICK,
                                    font: {
                                        size: 10
                                    },
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    padding: 8,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${ctx.parsed} transaksi`,
                                },
                            },
                        },
                    },
                });
            }
        }

        // ────────────────────────────────────────────────────────────────
        // CHART 3: Horizontal Bar — Top 10 Item Stok Tertinggi
        // ────────────────────────────────────────────────────────────────
        const c3 = document.getElementById('chartTopStock');
        if (c3) {
            const labels = topStock.labels ?? [];
            const units = topStock.units ?? [];
            const colors = labels.map((_, i) => `hsla(${160 + (i * 22) % 180}, 68%, 62%, 0.82)`);

            new Chart(c3, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Total Stok',
                        data: topStock.data ?? [],
                        backgroundColor: colors,
                        borderRadius: 5,
                        borderSkipped: false,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.parsed.x.toLocaleString('id-ID')} ${units[ctx.dataIndex] ?? ''}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: GRID
                            },
                            ticks: {
                                color: TICK,
                                font: {
                                    size: 10
                                }
                            },
                            border: {
                                display: false
                            },
                            beginAtZero: true,
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: TICK,
                                font: {
                                    size: 10
                                },
                                callback(val, idx) {
                                    const lbl = labels[idx] ?? '';
                                    return lbl.length > 22 ? lbl.slice(0, 20) + '…' : lbl;
                                },
                            },
                        },
                    },
                },
            });
        }

        // ────────────────────────────────────────────────────────────────
        // CHART 4: Grouped Bar — Status PO & Work Order
        // ────────────────────────────────────────────────────────────────
        const c4 = document.getElementById('chartOrders');
        if (c4) {
            // Data labels sudah tersedia dalam $chartOrders dari controller
            const poLabels = orders.labels ?? [];
            const woLabels = orders.woLabels ?? [];
            const poData = orders.poData ?? [];
            const woData = orders.woData ?? [];

            // Buat union semua status (PO + WO)
            const allStatuses = [...new Set([...poLabels, ...woLabels])];

            const poMap = Object.fromEntries(poLabels.map((l, i) => [l, poData[i] ?? 0]));
            const woMap = Object.fromEntries(woLabels.map((l, i) => [l, woData[i] ?? 0]));

            new Chart(c4, {
                type: 'bar',
                data: {
                    labels: allStatuses,
                    datasets: [{
                            label: 'Purchase Order',
                            data: allStatuses.map(s => poMap[s] ?? 0),
                            backgroundColor: 'rgba(129,140,248,0.78)',
                            borderColor: '#818cf8',
                            borderWidth: 1,
                            borderRadius: 5,
                            borderSkipped: false,
                        },
                        {
                            label: 'Work Order',
                            data: allStatuses.map(s => woMap[s] ?? 0),
                            backgroundColor: 'rgba(52,211,153,0.75)',
                            borderColor: '#34d399',
                            borderWidth: 1,
                            borderRadius: 5,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: TICK,
                                font: {
                                    size: 10
                                }
                            },
                        },
                        y: {
                            grid: {
                                color: GRID
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: TICK,
                                font: {
                                    size: 10
                                },
                                stepSize: 1
                            },
                            beginAtZero: true,
                        },
                    },
                },
            });
        }
    })();
</script>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
    }
</style>

@endsection