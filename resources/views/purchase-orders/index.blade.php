@extends('layouts.app')
@section('title', 'Purchase Order')
@section('content')

{{-- Page Header --}}
<div class="flex justify-between items-start mb-5">
    <div>
        <h2 class="text-base font-semibold" style="color:#e2e8f0;">Purchase Order</h2>
        <p class="text-xs mt-0.5" style="color:rgba(148,163,184,0.6);">Kelola pengadaan barang dari supplier</p>
    </div>
    <a href="{{ route('purchase-orders.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Buat PO
    </a>
</div>

{{-- Filter --}}
<div class="filter-card">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Status</label>
            <select name="status" class="filter-input">
                <option value="">Semua Status</option>
                @foreach(['draft','approved','partially_received','received','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" style="color:rgba(203,213,225,0.7);">Supplier</label>
            <select name="supplier_id" class="filter-input">
                <option value="">Semua Supplier</option>
                @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="filter-btn">Filter</button>
        <a href="{{ route('purchase-orders.index') }}" class="filter-reset">Reset</a>
    </form>
</div>

{{-- Table --}}
<div class="glass-card overflow-hidden">
    <table class="w-full text-sm data-table">
        <thead>
            <tr>
                <th class="text-left">No. PO</th>
                <th class="text-left">Supplier</th>
                <th class="text-left">Tanggal</th>
                <th class="text-right">Total</th>
                <th class="text-center">Status</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $po)
            <tr>
                <td class="font-mono text-xs" style="color:rgba(165,180,252,0.8);">{{ $po->po_number }}</td>
                <td class="font-medium" style="color:#e2e8f0;">{{ $po->supplier->name ?? '-' }}</td>
                <td class="text-xs" style="color:rgba(148,163,184,0.7);">{{ $po->order_date->format('d/m/Y') }}</td>
                <td class="text-right font-medium" style="color:rgba(226,232,240,0.9);">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="status-badge {{ $po->statusColor }}">{{ $po->status_label }}</span>
                </td>
                <td class="text-center">
                    <a href="{{ route('purchase-orders.show', $po) }}" class="btn-action-detail">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center"
                            style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);">
                            <svg class="w-6 h-6" style="color:rgba(99,102,241,0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-sm" style="color:rgba(148,163,184,0.6);">Belum ada purchase order.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-3" style="border-top:1px solid rgba(255,255,255,0.06);">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</div>

@endsection