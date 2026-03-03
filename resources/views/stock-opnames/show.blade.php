@extends('layouts.app')
@section('title', 'Detail Stock Opname')
@section('content')

<x-modal id="opname-detail" title="Detail Stock Opname — {{ $opname->reference_number }}" size="xl" back-url="{{ route('stock-opnames.index') }}">

  {{-- Header --}}
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
      <div class="font-mono text-sm" style="color:#a5b4fc;">{{ $opname->reference_number }}</div>
      <div style="font-size:0.78rem;color:rgba(148,163,184,0.6);margin-top:2px;">
        {{ $opname->warehouse->name }} &bull; {{ $opname->counted_at->format('d F Y') }}
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      @php $sc = match($opname->status) { 'draft'=>'bg-slate-100','in_progress'=>'bg-indigo-50','completed'=>'bg-emerald-50','cancelled'=>'bg-red-50',default=>'bg-slate-100' }; @endphp
      <span class="status-badge {{ $sc }}">{{ ucfirst($opname->status) }}</span>
      @if($opname->status === 'draft')
      <form method="POST" action="{{ route('stock-opnames.load-stock', $opname) }}" onsubmit="return confirm('Muat data stok sistem?')" style="display:inline;">
        @csrf
        <button class="modal-btn-submit" style="padding:5px 14px;font-size:0.78rem;background:linear-gradient(135deg,#6366f1,#8b5cf6);">Muat Stok &amp; Mulai</button>
      </form>
      @endif
      @if(in_array($opname->status, ['draft','in_progress']))
      <form method="POST" action="{{ route('stock-opnames.complete', $opname) }}" onsubmit="return confirm('Selesaikan opname? Penyesuaian stok akan diterapkan.')" style="display:inline;">
        @csrf
        <button class="modal-btn-submit" style="padding:5px 14px;font-size:0.78rem;background:linear-gradient(135deg,#059669,#10b981);">Selesaikan</button>
      </form>
      <form method="POST" action="{{ route('stock-opnames.cancel', $opname) }}" onsubmit="return confirm('Batalkan opname ini?')" style="display:inline;">
        @csrf
        <button class="modal-btn-cancel" style="padding:5px 14px;font-size:0.78rem;background:rgba(239,68,68,0.12);color:#fca5a5;border-color:rgba(239,68,68,0.2);">Batalkan</button>
      </form>
      @endif
    </div>
  </div>

  {{-- Opname item table --}}
  <form method="POST" action="{{ route('stock-opnames.save-count', $opname) }}">
    @csrf
    <div style="border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;max-height:55vh;overflow-y:auto;">
      <table class="table-inline">
        <thead style="position:sticky;top:0;z-index:2;background:#1a1535;">
          <tr>
            <th class="text-left">Item</th>
            <th class="text-right">Qty Sistem</th>
            <th class="text-right" style="width:130px;">Qty Fisik</th>
            <th class="text-right">Selisih</th>
          </tr>
        </thead>
        <tbody>
          @foreach($opname->items as $line)
          <tr>
            <td style="padding:8px 10px;">
              <div style="font-size:0.85rem;font-weight:500;color:#e2e8f0;">{{ $line->item->name }}</div>
              <div style="font-size:0.68rem;color:rgba(165,180,252,0.7);">{{ $line->item->code }}</div>
            </td>
            <td style="padding:8px 10px;text-align:right;color:rgba(226,232,240,0.7);">{{ number_format($line->system_quantity, 2) }}</td>
            <td style="padding:8px 10px;">
              @if(in_array($opname->status, ['completed','cancelled']))
              <span style="display:block;text-align:right;color:#e2e8f0;">{{ number_format($line->physical_quantity, 2) }}</span>
              @else
              <input type="number" step="0.01" min="0"
                name="counts[{{ $line->item_id }}]"
                value="{{ $line->physical_quantity ?? $line->system_quantity }}"
                class="modal-input" style="text-align:right;font-size:0.82rem;padding:5px 8px;">
              @endif
            </td>
            <td style="padding:8px 10px;text-align:right;font-weight:600;color:{{ $line->discrepancy > 0 ? '#6ee7b7' : ($line->discrepancy < 0 ? '#fca5a5' : 'rgba(148,163,184,0.5)') }};">
              {{ $line->discrepancy !== null ? ($line->discrepancy > 0 ? '+' : '') . number_format($line->discrepancy, 2) : '—' }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if(!in_array($opname->status, ['completed','cancelled']))
    <div class="modal-footer">
      <button type="submit" class="modal-btn-submit">Simpan Hitungan Fisik</button>
      <a href="{{ route('stock-opnames.index') }}" class="modal-btn-cancel">Tutup</a>
    </div>
    @else
    <div class="modal-footer" style="justify-content:flex-end;">
      <a href="{{ route('stock-opnames.index') }}" class="modal-btn-cancel">Tutup</a>
    </div>
    @endif
  </form>
</x-modal>

@endsection