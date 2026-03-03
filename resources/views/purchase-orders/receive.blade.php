@extends('layouts.app')
@section('title', 'Terima Barang PO')
@section('content')
<div class="max-w-3xl">
  <a href="{{ route('purchase-orders.show', $po) }}" class="text-sm text-blue-600 hover:underline mb-4 inline-block">&larr; Kembali ke {{ $po->po_number }}</a>
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-1">Penerimaan Barang</h2>
    <p class="text-xs text-gray-400 mb-5">PO: {{ $po->po_number }} &mdash; Supplier: {{ $po->supplier->name }} &mdash; Gudang: {{ $po->warehouse->name }}</p>
    <form method="POST" action="{{ route('purchase-orders.receive', $po) }}" class="space-y-4">
      @csrf
      <div class="overflow-x-auto border rounded-lg">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-2 text-left">Item</th>
              <th class="px-4 py-2 text-right">Qty Order</th>
              <th class="px-4 py-2 text-right">Sudah Terima</th>
              <th class="px-4 py-2 text-right w-32">Terima Sekarang</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            @foreach($po->items as $line)
            <tr>
              <td class="px-4 py-2">
                <div class="font-medium text-gray-800">{{ $line->item->name }}</div>
                <div class="text-xs text-gray-400">{{ $line->item->code }}</div>
              </td>
              <td class="px-4 py-2 text-right">{{ number_format($line->quantity_ordered, 2) }}</td>
              <td class="px-4 py-2 text-right text-amber-600">{{ number_format($line->quantity_received, 2) }}</td>
              <td class="px-4 py-2">
                <input type="number" step="0.01" min="0" max="{{ $line->quantity_ordered - $line->quantity_received }}"
                  name="items[{{ $line->id }}][quantity]"
                  value="{{ old("items.{$line->id}.quantity", $line->quantity_ordered - $line->quantity_received) }}"
                  class="w-full border border-gray-300 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-blue-700">Konfirmasi Penerimaan</button>
        <a href="{{ route('purchase-orders.show', $po) }}" class="bg-gray-100 text-gray-700 px-5 py-2 rounded-lg text-sm hover:bg-gray-200">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection