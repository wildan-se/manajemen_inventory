@extends('layouts.app')
@section('title', 'Buat Stock Opname')
@section('content')

<x-modal id="opname-create" title="Buat Stock Opname Baru" size="sm" back-url="{{ route('stock-opnames.index') }}">
  <form method="POST" action="{{ route('stock-opnames.store') }}" class="space-y-4">
    @csrf
    <div class="modal-field">
      <label class="modal-label">Gudang <span class="modal-req">*</span></label>
      <select name="warehouse_id" class="modal-select" required>
        <option value="">— Pilih Gudang —</option>
        @foreach($warehouses as $wh)
        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="modal-field">
      <label class="modal-label">Tanggal Opname <span class="modal-req">*</span></label>
      <input type="date" name="counted_at" value="{{ old('counted_at', date('Y-m-d')) }}" class="modal-input" required>
    </div>
    <div class="modal-field">
      <label class="modal-label">Catatan</label>
      <textarea name="notes" rows="2" class="modal-textarea" placeholder="Catatan opsional...">{{ old('notes') }}</textarea>
    </div>
    <div style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.15);border-radius:10px;padding:10px 12px;font-size:0.78rem;color:rgba(165,180,252,0.8);">
      Setelah disimpan, sistem akan memuat daftar stok dari gudang yang dipilih untuk diisi jumlah fisik.
    </div>
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Buat &amp; Muat Stok</button>
      <a href="{{ route('stock-opnames.index') }}" class="modal-btn-cancel">Batal</a>
    </div>
  </form>
</x-modal>

@endsection