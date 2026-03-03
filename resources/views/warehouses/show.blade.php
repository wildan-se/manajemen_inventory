@extends('layouts.app')
@section('title', 'Detail Gudang')
@section('content')
<div class="flex justify-between items-center mb-4">
  <a href="{{ route('warehouses.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
  <a href="{{ route('warehouses.edit', $warehouse) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Edit</a>
</div>
<div class="grid grid-cols-3 gap-4">
  <div class="col-span-1 bg-white rounded-xl shadow-sm p-5 space-y-3">
    <h3 class="font-semibold text-gray-700 border-b pb-2">Info Gudang</h3>
    <div class="text-sm"><span class="text-gray-500 block">Kode</span><span class="font-mono font-medium">{{ $warehouse->code }}</span></div>
    <div class="text-sm"><span class="text-gray-500 block">Nama</span><span class="font-medium">{{ $warehouse->name }}</span></div>
    <div class="text-sm"><span class="text-gray-500 block">Alamat</span><span>{{ $warehouse->address ?? '-' }}</span></div>
    <div class="text-sm"><span class="text-gray-500 block">Status</span>
      @if($warehouse->is_active)
      <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Aktif</span>
      @else
      <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full">Nonaktif</span>
      @endif
    </div>
    <h4 class="font-semibold text-gray-600 border-b pb-1 pt-2 text-sm">Lokasi</h4>
    @forelse($warehouse->locations as $loc)
    <div class="text-xs text-gray-600 bg-gray-50 rounded px-2 py-1">{{ $loc->name }} <span class="text-gray-400">({{ $loc->code }})</span></div>
    @empty
    <p class="text-xs text-gray-400">Belum ada lokasi.</p>
    @endforelse
  </div>
  <div class="col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b font-semibold text-gray-700 text-sm">Stok di Gudang Ini</div>
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
        <tr>
          <th class="px-4 py-3 text-left">Item</th>
          <th class="px-4 py-3 text-left">Lokasi</th>
          <th class="px-4 py-3 text-right">Qty</th>
          <th class="px-4 py-3 text-center">Satuan</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($stocks as $stock)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3">
            <div class="font-medium text-gray-800">{{ $stock->item->name }}</div>
            <div class="text-xs text-gray-400">{{ $stock->item->code }}</div>
          </td>
          <td class="px-4 py-3 text-gray-500 text-xs">{{ $stock->location->name ?? 'Default' }}</td>
          <td class="px-4 py-3 text-right font-semibold {{ $stock->quantity <= $stock->item->min_stock ? 'text-red-600' : 'text-gray-800' }}">
            {{ number_format($stock->quantity, 2) }}
          </td>
          <td class="px-4 py-3 text-center text-gray-500">{{ $stock->item->unit->abbreviation ?? '' }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada stok.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="px-4 py-3 border-t">{{ $stocks->links() }}</div>
  </div>
</div>
@endsection