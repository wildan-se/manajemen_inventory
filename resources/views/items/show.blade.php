@extends('layouts.app')
@section('title', 'Detail Item')
@section('content')
<div class="flex justify-between items-center mb-4">
  <a href="{{ route('items.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
  <a href="{{ route('items.edit', $item) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Edit</a>
</div>
<div class="grid grid-cols-3 gap-4">
  <div class="col-span-1 space-y-4">
    <div class="bg-white rounded-xl shadow-sm p-5 space-y-3">
      <h3 class="font-semibold text-gray-700 border-b pb-2">Info Item</h3>
      <div class="text-sm"><span class="text-gray-500 block">Kode</span><span class="font-mono font-medium">{{ $item->code }}</span></div>
      <div class="text-sm"><span class="text-gray-500 block">Nama</span><span class="font-medium">{{ $item->name }}</span></div>
      <div class="text-sm"><span class="text-gray-500 block">Kategori</span><span>{{ $item->category->name ?? '-' }}</span></div>
      <div class="text-sm"><span class="text-gray-500 block">Satuan</span><span>{{ $item->unit->name ?? '-' }} ({{ $item->unit->abbreviation ?? '' }})</span></div>
      <div class="text-sm"><span class="text-gray-500 block">Stok Min</span><span>{{ number_format($item->min_stock, 2) }}</span></div>
      <div class="text-sm"><span class="text-gray-500 block">Stok Max</span><span>{{ number_format($item->max_stock, 2) }}</span></div>
      <div class="text-sm"><span class="text-gray-500 block">Total Stok</span>
        <span class="font-bold text-lg {{ $item->isBelowMinStock() ? 'text-red-600' : 'text-green-600' }}">
          {{ number_format($item->totalStock(), 2) }}
        </span>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
      <h4 class="font-semibold text-gray-700 border-b pb-2 mb-3 text-sm">Stok per Gudang</h4>
      @forelse($item->stocks as $stock)
      <div class="flex justify-between text-sm py-1">
        <span class="text-gray-600">{{ $stock->warehouse->name }}</span>
        <span class="font-medium">{{ number_format($stock->quantity, 2) }}</span>
      </div>
      @empty
      <p class="text-xs text-gray-400">Belum ada stok.</p>
      @endforelse
    </div>
  </div>
  <div class="col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b font-semibold text-gray-700 text-sm">Riwayat Mutasi Terbaru</div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-4 py-3 text-left">Tanggal</th>
          <th class="px-4 py-3 text-left">Tipe</th>
          <th class="px-4 py-3 text-left">Gudang</th>
          <th class="px-4 py-3 text-right">Qty</th>
          <th class="px-4 py-3 text-left">Oleh</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($recentMovements as $mov)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-gray-500">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
          <td class="px-4 py-3">
            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $mov->type_label }}</span>
          </td>
          <td class="px-4 py-3 text-gray-600">{{ $mov->toWarehouse->name ?? $mov->fromWarehouse->name ?? '-' }}</td>
          <td class="px-4 py-3 text-right font-medium {{ $mov->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $mov->quantity > 0 ? '+' : '' }}{{ number_format($mov->quantity, 2) }}
          </td>
          <td class="px-4 py-3 text-gray-500 text-xs">{{ $mov->user->name ?? '-' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada mutasi.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection