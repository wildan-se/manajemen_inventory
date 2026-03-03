@extends('layouts.app')
@section('title', 'Detail Supplier')
@section('content')
<div class="flex justify-between items-center mb-4">
  <a href="{{ route('suppliers.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
  <a href="{{ route('suppliers.edit', $supplier) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Edit</a>
</div>
<div class="grid grid-cols-3 gap-4">
  <div class="col-span-1 bg-white rounded-xl shadow-sm p-6 space-y-3">
    <h3 class="font-semibold text-gray-700 border-b pb-2">Informasi Supplier</h3>
    <div class="flex justify-between text-sm"><span class="text-gray-500">Kode</span><span class="font-mono font-medium">{{ $supplier->code }}</span></div>
    <div class="flex justify-between text-sm"><span class="text-gray-500">Nama</span><span class="font-medium">{{ $supplier->name }}</span></div>
    <div class="flex justify-between text-sm"><span class="text-gray-500">Email</span><span>{{ $supplier->email ?? '-' }}</span></div>
    <div class="flex justify-between text-sm"><span class="text-gray-500">Telepon</span><span>{{ $supplier->phone ?? '-' }}</span></div>
    <div class="flex justify-between text-sm"><span class="text-gray-500">Contact Person</span><span>{{ $supplier->contact_person ?? '-' }}</span></div>
    <div class="flex justify-between text-sm"><span class="text-gray-500">Status</span>
      @if($supplier->is_active)
      <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Aktif</span>
      @else
      <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Nonaktif</span>
      @endif
    </div>
    @if($supplier->address)
    <div class="text-sm"><span class="text-gray-500 block">Alamat</span><span>{{ $supplier->address }}</span></div>
    @endif
  </div>
  <div class="col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b font-semibold text-gray-700 text-sm">Purchase Order Terbaru</div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-4 py-3 text-left">No. PO</th>
          <th class="px-4 py-3 text-left">Tanggal</th>
          <th class="px-4 py-3 text-right">Total</th>
          <th class="px-4 py-3 text-center">Status</th>
          <th class="px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($recentPOs as $po)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-mono text-xs">{{ $po->po_number }}</td>
          <td class="px-4 py-3 text-gray-500">{{ $po->order_date->format('d/m/Y') }}</td>
          <td class="px-4 py-3 text-right">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
          <td class="px-4 py-3 text-center">
            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $po->statusColor }}">{{ ucfirst($po->status) }}</span>
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('purchase-orders.show', $po) }}" class="text-blue-600 hover:underline text-xs">Detail</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada PO.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection