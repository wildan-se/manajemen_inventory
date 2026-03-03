@extends('layouts.app')
@section('title', 'Buat Work Order')
@section('content')

<x-modal id="wo-create" title="Buat Work Order Baru" size="xl" back-url="{{ route('production-orders.index') }}">
  <form method="POST" action="{{ route('production-orders.store') }}" class="space-y-5">
    @csrf
    <div class="modal-field">
      <label class="modal-label">Judul / Nama WO <span class="modal-req">*</span></label>
      <input type="text" name="title" value="{{ old('title') }}" placeholder="Nama work order..." class="modal-input" required>
    </div>
    <div class="modal-grid-2">
      <div class="modal-field">
        <label class="modal-label">Tanggal Mulai</label>
        <input type="date" name="planned_start" value="{{ old('planned_start', date('Y-m-d')) }}" class="modal-input">
      </div>
      <div class="modal-field">
        <label class="modal-label">Target Selesai</label>
        <input type="date" name="planned_end" value="{{ old('planned_end') }}" class="modal-input">
      </div>
    </div>
    <div class="modal-field">
      <label class="modal-label">Gudang Produksi <span class="modal-req">*</span></label>
      <select name="warehouse_id" class="modal-select" required>
        <option value="">— Pilih Gudang —</option>
        @foreach($warehouses as $wh)
        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="modal-field">
      <label class="modal-label">Keterangan</label>
      <textarea name="description" rows="2" class="modal-textarea" placeholder="Deskripsi opsional...">{{ old('description') }}</textarea>
    </div>

    {{-- Material Input --}}
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        <label class="modal-label" style="margin:0;color:#fbbf24;">⬇ Material Input (Bahan Baku)</label>
        <button type="button" onclick="addInputRow()" style="background:rgba(245,158,11,0.12);color:#fcd34d;border:1px solid rgba(245,158,11,0.2);padding:4px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;cursor:pointer;">+ Tambah</button>
      </div>
      <div style="overflow-x:auto;border:1px solid rgba(255,255,255,0.07);border-radius:12px;">
        <table class="table-inline">
          <thead>
            <tr>
              <th class="text-left" style="width:75%;">Item</th>
              <th class="text-right" style="width:20%;">Qty</th>
              <th style="width:5%;"></th>
            </tr>
          </thead>
          <tbody id="input-body">
            <tr class="input-row">
              <td style="padding:6px 8px;"><select name="inputs[0][item_id]" class="modal-select" style="font-size:0.8rem;" required>
                  <option value="">— Pilih Item —</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>@endforeach
                </select></td>
              <td style="padding:6px 8px;"><input type="number" step="0.01" name="inputs[0][quantity]" value="1" min="0.01" class="modal-input" style="text-align:right;font-size:0.8rem;" required></td>
              <td style="padding:6px 8px;text-align:center;"><button type="button" onclick="removeRow(this)" style="color:rgba(252,165,165,0.6);background:none;border:none;cursor:pointer;font-size:18px;">&times;</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    {{-- Output Products --}}
    <div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
        <label class="modal-label" style="margin:0;color:#6ee7b7;">⬆ Produk Output (Hasil Produksi)</label>
        <button type="button" onclick="addOutputRow()" style="background:rgba(16,185,129,0.12);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2);padding:4px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;cursor:pointer;">+ Tambah</button>
      </div>
      <div style="overflow-x:auto;border:1px solid rgba(255,255,255,0.07);border-radius:12px;">
        <table class="table-inline">
          <thead>
            <tr>
              <th class="text-left" style="width:75%;">Item</th>
              <th class="text-right" style="width:20%;">Qty</th>
              <th style="width:5%;"></th>
            </tr>
          </thead>
          <tbody id="output-body">
            <tr class="output-row">
              <td style="padding:6px 8px;"><select name="outputs[0][item_id]" class="modal-select" style="font-size:0.8rem;" required>
                  <option value="">— Pilih Item —</option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>@endforeach
                </select></td>
              <td style="padding:6px 8px;"><input type="number" step="0.01" name="outputs[0][quantity]" value="1" min="0.01" class="modal-input" style="text-align:right;font-size:0.8rem;" required></td>
              <td style="padding:6px 8px;text-align:center;"><button type="button" onclick="removeRow(this)" style="color:rgba(252,165,165,0.6);background:none;border:none;cursor:pointer;font-size:18px;">&times;</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Draft WO</button>
      <a href="{{ route('production-orders.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

<script>
  let inIdx = 1,
    outIdx = 1;
  const itemOpts = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>@endforeach`;

  function addInputRow() {
    const b = document.getElementById('input-body');
    b.insertAdjacentHTML('beforeend', `<tr class="input-row"><td style="padding:6px 8px;"><select name="inputs[${inIdx}][item_id]" class="modal-select" style="font-size:0.8rem;" required><option value="">— Pilih Item —</option>${itemOpts}</select></td><td style="padding:6px 8px;"><input type="number" step="0.01" name="inputs[${inIdx}][quantity]" value="1" min="0.01" class="modal-input" style="text-align:right;" required></td><td style="padding:6px 8px;text-align:center;"><button type="button" onclick="removeRow(this)" style="color:rgba(252,165,165,0.6);background:none;border:none;cursor:pointer;font-size:18px;">&times;</button></td></tr>`);
    inIdx++;
  }

  function addOutputRow() {
    const b = document.getElementById('output-body');
    b.insertAdjacentHTML('beforeend', `<tr class="output-row"><td style="padding:6px 8px;"><select name="outputs[${outIdx}][item_id]" class="modal-select" style="font-size:0.8rem;" required><option value="">— Pilih Item —</option>${itemOpts}</select></td><td style="padding:6px 8px;"><input type="number" step="0.01" name="outputs[${outIdx}][quantity]" value="1" min="0.01" class="modal-input" style="text-align:right;" required></td><td style="padding:6px 8px;text-align:center;"><button type="button" onclick="removeRow(this)" style="color:rgba(252,165,165,0.6);background:none;border:none;cursor:pointer;font-size:18px;">&times;</button></td></tr>`);
    outIdx++;
  }

  function removeRow(btn) {
    btn.closest('tr').remove();
  }
</script>

@endsection