@extends('layouts.app')
@section('title', 'Buat Mutasi Stok')
@section('content')

<x-modal id="stock-create" title="Buat Mutasi Stok" size="md" back-url="{{ route('stock-movements.index') }}">
  <form method="POST" action="{{ route('stock-movements.store') }}" class="space-y-4">
    @csrf
    <div class="modal-field">
      <label class="modal-label">Tipe Mutasi <span class="modal-req">*</span></label>
      <select name="type" id="type" onchange="toggleFields()" class="modal-select" required>
        <option value="">— Pilih Tipe —</option>
        @foreach($types as $val => $label)
        <option value="{{ $val }}" {{ old('type') == $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="modal-field">
      <label class="modal-label">Item <span class="modal-req">*</span></label>
      <select name="item_id" class="modal-select" required>
        <option value="">— Pilih Item —</option>
        @foreach($items as $item)
        <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
          {{ $item->code }} — {{ $item->name }}
        </option>
        @endforeach
      </select>
    </div>
    <div class="modal-grid-2">
      <div id="from-warehouse-wrap" class="modal-field" style="display:none;">
        <label class="modal-label">Gudang Asal <span class="modal-req">*</span></label>
        <select name="from_warehouse_id" class="modal-select">
          <option value="">— Pilih Gudang —</option>
          @foreach($warehouses as $wh)
          <option value="{{ $wh->id }}" {{ old('from_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
          @endforeach
        </select>
      </div>
      <div id="to-warehouse-wrap" class="modal-field" style="display:none;">
        <label class="modal-label">Gudang Tujuan <span class="modal-req">*</span></label>
        <select name="to_warehouse_id" class="modal-select">
          <option value="">— Pilih Gudang —</option>
          @foreach($warehouses as $wh)
          <option value="{{ $wh->id }}" {{ old('to_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="modal-grid-2">
      <div id="quantity-wrap" class="modal-field">
        <label class="modal-label">Jumlah <span class="modal-req">*</span></label>
        <input type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" class="modal-input">
      </div>
      <div id="new-quantity-wrap" class="modal-field" style="display:none;">
        <label class="modal-label">Stok Baru (Target) <span class="modal-req">*</span></label>
        <input type="number" step="0.01" min="0" name="new_quantity" value="{{ old('new_quantity') }}" class="modal-input">
        <span class="modal-hint">Stok aktual yang seharusnya ada.</span>
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Catatan</label>
      <textarea name="notes" rows="2" class="modal-textarea" placeholder="Catatan opsional...">{{ old('notes') }}</textarea>
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Mutasi</button>
      <a href="{{ route('stock-movements.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

<script>
  function toggleFields() {
    const type = document.getElementById('type').value;
    const from = document.getElementById('from-warehouse-wrap');
    const to = document.getElementById('to-warehouse-wrap');
    const qtyWrap = document.getElementById('quantity-wrap');
    const newQtyWrap = document.getElementById('new-quantity-wrap');
    from.style.display = 'none';
    to.style.display = 'none';
    qtyWrap.style.display = 'flex';
    newQtyWrap.style.display = 'none';
    if (type === 'goods_receipt' || type === 'production_output') {
      to.style.display = 'flex';
    } else if (type === 'material_issue' || type === 'sales_dispatch') {
      from.style.display = 'flex';
    } else if (type === 'stock_transfer') {
      from.style.display = 'flex';
      to.style.display = 'flex';
    } else if (type === 'stock_adjustment') {
      to.style.display = 'flex';
      qtyWrap.style.display = 'none';
      newQtyWrap.style.display = 'flex';
    }
  }
  document.addEventListener('DOMContentLoaded', toggleFields);
</script>

@endsection