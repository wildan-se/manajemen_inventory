@extends('layouts.app')
@section('title', 'Detail Work Order')
@section('content')

<x-modal id="wo-detail" title="Detail Work Order — {{ $wo->wo_number }}" size="xl" back-url="{{ route('production-orders.index') }}">

  {{-- Header info + actions --}}
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
      <div class="font-mono text-sm" style="color:#a5b4fc;">{{ $wo->wo_number }}</div>
      <div style="font-size:0.875rem;font-weight:500;color:#e2e8f0;margin-top:2px;">{{ $wo->title }}</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      @php
      $woColor = match($wo->status) { 'draft'=>'bg-slate-100','in_progress'=>'bg-indigo-50','completed'=>'bg-emerald-50','cancelled'=>'bg-red-50',default=>'bg-slate-100' };
      @endphp
      <span class="status-badge {{ $woColor }}">{{ ucwords(str_replace('_', ' ', $wo->status)) }}</span>
      @if($wo->status === 'draft')
      @if(auth()->user()->hasRole(['admin','production_staff','supervisor']))
      <form method="POST" action="{{ route('production-orders.start', $wo) }}" onsubmit="return confirm('Mulai produksi? Material akan dikeluarkan dari stok.')" style="display:inline;">
        @csrf <button class="modal-btn-submit" style="padding:5px 14px;font-size:0.78rem;">Mulai Produksi</button>
      </form>
      @endif
      <form method="POST" action="{{ route('production-orders.cancel', $wo) }}" onsubmit="return confirm('Batalkan WO ini?')" style="display:inline;">
        @csrf <button class="modal-btn-cancel" style="padding:5px 14px;font-size:0.78rem;background:rgba(239,68,68,0.12);color:#fca5a5;border-color:rgba(239,68,68,0.2);">Batalkan</button>
      </form>
      @elseif($wo->status === 'in_progress')
      <form method="POST" action="{{ route('production-orders.complete', $wo) }}" onsubmit="return confirm('Selesaikan produksi?')" style="display:inline;">
        @csrf <button class="modal-btn-submit" style="padding:5px 14px;font-size:0.78rem;background:linear-gradient(135deg,#059669,#10b981);">Selesaikan</button>
      </form>
      <form method="POST" action="{{ route('production-orders.cancel', $wo) }}" onsubmit="return confirm('Batalkan WO ini?')" style="display:inline;">
        @csrf <button class="modal-btn-cancel" style="padding:5px 14px;font-size:0.78rem;background:rgba(239,68,68,0.12);color:#fca5a5;border-color:rgba(239,68,68,0.2);">Batalkan</button>
      </form>
      @endif
    </div>
  </div>

  {{-- Info grid --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px;">
      <div style="font-size:0.68rem;color:rgba(148,163,184,0.5);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Gudang</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $wo->warehouse->name ?? '-' }}</div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px;">
      <div style="font-size:0.68rem;color:rgba(148,163,184,0.5);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Tanggal Mulai</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $wo->planned_start?->format('d/m/Y') ?? '-' }}</div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px;">
      <div style="font-size:0.68rem;color:rgba(148,163,184,0.5);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Target Selesai</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $wo->planned_end?->format('d/m/Y') ?? '-' }}</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    {{-- Input Materials --}}
    <div style="border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;">
      <div style="padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.06);font-size:0.78rem;font-weight:600;color:#fcd34d;display:flex;align-items:center;gap:6px;">
        <span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;display:inline-block;"></span>
        Material Input (Bahan Baku)
      </div>
      <table class="table-inline">
        <thead>
          <tr>
            <th class="text-left">Item</th>
            <th class="text-right">Qty</th>
            <th class="text-center">Satuan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($wo->inputs as $inp)
          <tr>
            <td style="padding:8px 10px;">
              <div style="font-size:0.82rem;color:#e2e8f0;">{{ $inp->item->name }}</div>
              <div style="font-size:0.68rem;color:rgba(165,180,252,0.7);">{{ $inp->item->code }}</div>
            </td>
            <td style="padding:8px 10px;text-align:right;color:rgba(226,232,240,0.8);">{{ number_format($inp->quantity, 2) }}</td>
            <td style="padding:8px 10px;text-align:center;font-size:0.78rem;color:rgba(148,163,184,0.6);">{{ $inp->item->unit->abbreviation ?? '' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="3" style="padding:12px;text-align:center;color:rgba(148,163,184,0.5);font-size:0.8rem;">Tidak ada data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Output Products --}}
    <div style="border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;">
      <div style="padding:10px 14px;border-bottom:1px solid rgba(255,255,255,0.06);font-size:0.78rem;font-weight:600;color:#6ee7b7;display:flex;align-items:center;gap:6px;">
        <span style="width:6px;height:6px;border-radius:50%;background:#10b981;display:inline-block;"></span>
        Produk Output (Hasil Produksi)
      </div>
      <table class="table-inline">
        <thead>
          <tr>
            <th class="text-left">Item</th>
            <th class="text-right">Qty</th>
            <th class="text-center">Satuan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($wo->outputs as $out)
          <tr>
            <td style="padding:8px 10px;">
              <div style="font-size:0.82rem;color:#e2e8f0;">{{ $out->item->name }}</div>
              <div style="font-size:0.68rem;color:rgba(165,180,252,0.7);">{{ $out->item->code }}</div>
            </td>
            <td style="padding:8px 10px;text-align:right;color:rgba(226,232,240,0.8);">{{ number_format($out->quantity, 2) }}</td>
            <td style="padding:8px 10px;text-align:center;font-size:0.78rem;color:rgba(148,163,184,0.6);">{{ $out->item->unit->abbreviation ?? '' }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="3" style="padding:12px;text-align:center;color:rgba(148,163,184,0.5);font-size:0.8rem;">Tidak ada data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal-footer" style="justify-content:flex-end;">
    <a href="{{ route('production-orders.index') }}" class="modal-btn-cancel">Tutup</a>
  </div>
</x-modal>

@endsection