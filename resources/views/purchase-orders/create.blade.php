@extends('layouts.app')
@section('title', 'Buat Purchase Order')
@section('content')

<x-modal id="po-create" title="Buat Purchase Order Baru" size="xl" back-url="{{ route('purchase-orders.index') }}">
  <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-5">
    @csrf
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Supplier <span class="modal-req">*</span></label>
        <select name="supplier_id" class="modal-select" required>
          <option value="">— Pilih Supplier —</option>
          @foreach($suppliers as $sup)
          <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="modal-field">
        <label class="modal-label">Gudang Tujuan <span class="modal-req">*</span></label>
        <select name="warehouse_id" class="modal-select" required>
          <option value="">— Pilih Gudang —</option>
          @foreach($warehouses as $wh)
          <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="modal-field">
        <label class="modal-label">Tanggal Order <span class="modal-req">*</span></label>
        <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" class="modal-input" required>
      </div>
      <div class="modal-field">
        <label class="modal-label">Expected Delivery</label>
        <input type="date" name="expected_date" value="{{ old('expected_date') }}" class="modal-input">
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Catatan</label>
      <textarea name="notes" rows="2" class="modal-textarea" placeholder="Catatan opsional...">{{ old('notes') }}</textarea>
    </div>

    {{-- Items Table --}}
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        <label class="modal-label" style="margin:0;">Item PO</label>
        <button type="button" onclick="addRow()" style="background:rgba(16,185,129,0.15);color:#6ee7b7;border:1px solid rgba(16,185,129,0.25);padding:4px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='rgba(16,185,129,0.25)'" onmouseout="this.style.background='rgba(16,185,129,0.15)'">+ Tambah Baris</button>
      </div>
      <div style="overflow-x:auto;border:1px solid rgba(255,255,255,0.07);border-radius:12px;">
        <table class="table-inline" id="items-table">
          <thead>
            <tr>
              <th class="text-left" style="width:40%;">Item</th>
              <th class="text-right" style="width:15%;">Qty</th>
              <th class="text-right" style="width:22%;">Harga Satuan</th>
              <th class="text-right" style="width:18%;">Subtotal</th>
              <th style="width:5%;"></th>
            </tr>
          </thead>
          <tbody id="items-body">
            <tr class="item-row">
              <td style="padding:6px 8px;">
                <select name="items[0][item_id]" class="modal-select" style="font-size:0.8rem;" required>
                  <option value="">— Pilih Item —</option>
                  @foreach($items as $item)
                  <option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>
                  @endforeach
                </select>
              </td>
              <td style="padding:6px 8px;"><input type="number" step="0.01" name="items[0][quantity]" value="1" min="0.01" oninput="calcRow(this)" class="modal-input" style="font-size:0.8rem;text-align:right;" required></td>
              <td style="padding:6px 8px;"><input type="number" step="0.01" name="items[0][unit_price]" value="0" min="0" oninput="calcRow(this)" class="modal-input" style="font-size:0.8rem;text-align:right;"></td>
              <td class="subtotal text-right" style="padding:6px 8px;font-size:0.8rem;color:rgba(226,232,240,0.8);">Rp 0</td>
              <td style="padding:6px 8px;text-align:center;"><button type="button" onclick="removeRow(this)" style="color:rgba(252,165,165,0.6);background:none;border:none;cursor:pointer;font-size:18px;line-height:1;" onmouseover="this.style.color='#fca5a5'" onmouseout="this.style.color='rgba(252,165,165,0.6)'">&times;</button></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3" style="padding:10px;text-align:right;font-size:0.8rem;font-weight:600;color:rgba(148,163,184,0.7);border-top:1px solid rgba(255,255,255,0.07);">Total</td>
              <td id="grand-total" style="padding:10px;text-align:right;font-size:0.875rem;font-weight:700;color:#e2e8f0;border-top:1px solid rgba(255,255,255,0.07);">Rp 0</td>
              <td style="border-top:1px solid rgba(255,255,255,0.07);"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Draft PO</button>
      <a href="{{ route('purchase-orders.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

<script>
  let rowIndex = 1;
  const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>@endforeach`;

  function addRow() {
    const tbody = document.getElementById('items-body');
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
    <td style="padding:6px 8px;"><select name="items[${rowIndex}][item_id]" class="modal-select" style="font-size:0.8rem;" required><option value="">— Pilih Item —</option>${itemOptions}</select></td>
    <td style="padding:6px 8px;"><input type="number" step="0.01" name="items[${rowIndex}][quantity]" value="1" min="0.01" oninput="calcRow(this)" class="modal-input" style="font-size:0.8rem;text-align:right;" required></td>
    <td style="padding:6px 8px;"><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" value="0" min="0" oninput="calcRow(this)" class="modal-input" style="font-size:0.8rem;text-align:right;"></td>
    <td class="subtotal text-right" style="padding:6px 8px;font-size:0.8rem;color:rgba(226,232,240,0.8);">Rp 0</td>
    <td style="padding:6px 8px;text-align:center;"><button type="button" onclick="removeRow(this)" style="color:rgba(252,165,165,0.6);background:none;border:none;cursor:pointer;font-size:18px;line-height:1;">&times;</button></td>`;
    tbody.appendChild(tr);
    rowIndex++;
  }

  function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
      btn.closest('tr').remove();
      updateTotal();
    }
  }

  function calcRow(el) {
    const row = el.closest('tr');
    const qty = parseFloat(row.querySelector('[name$="[quantity]"]').value) || 0;
    const price = parseFloat(row.querySelector('[name$="[unit_price]"]').value) || 0;
    row.querySelector('.subtotal').textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
    updateTotal();
  }

  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.item-row').forEach(row => {
      const qty = parseFloat(row.querySelector('[name$="[quantity]"]').value) || 0;
      const price = parseFloat(row.querySelector('[name$="[unit_price]"]').value) || 0;
      total += qty * price;
    });
    document.getElementById('grand-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
  }
</script>

@endsection