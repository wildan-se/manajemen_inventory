@extends('layouts.app')
@section('title', 'Detail Mutasi Stok')
@section('content')

<x-modal id="mov-detail" title="Detail Mutasi Stok" size="md" back-url="{{ route('stock-movements.index') }}">
  {{-- Header Info --}}
  <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:20px;">
    <div>
      <div class="font-mono text-sm" style="color:#a5b4fc;margin-bottom:4px;">{{ $stockMovement->reference_number }}</div>
      <div style="font-size:0.78rem;color:rgba(148,163,184,0.6);">{{ $stockMovement->created_at->format('d F Y, H:i') }}</div>
    </div>
    <span style="background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.25);padding:4px 12px;border-radius:999px;font-size:0.72rem;font-weight:600;">
      {{ $stockMovement->type_label }}
    </span>
  </div>

  <hr class="modal-divider">

  {{-- Detail Grid --}}
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Item</div>
      <div style="font-size:0.9rem;font-weight:500;color:#e2e8f0;">{{ $stockMovement->item->name }}</div>
      <div style="font-size:0.72rem;color:rgba(165,180,252,0.7);">{{ $stockMovement->item->code }}</div>
    </div>
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Kuantitas</div>
      <div style="font-size:1.3rem;font-weight:700;color:{{ $stockMovement->quantity > 0 ? '#6ee7b7' : '#fca5a5' }};">
        {{ $stockMovement->quantity > 0 ? '+' : '' }}{{ number_format($stockMovement->quantity, 2) }}
        <span style="font-size:0.8rem;font-weight:500;color:rgba(148,163,184,0.6);">{{ $stockMovement->item->unit->abbreviation ?? '' }}</span>
      </div>
    </div>
    @if($stockMovement->fromWarehouse)
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Gudang Asal</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $stockMovement->fromWarehouse->name }}</div>
    </div>
    @endif
    @if($stockMovement->toWarehouse)
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Gudang Tujuan</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $stockMovement->toWarehouse->name }}</div>
    </div>
    @endif
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Stok Sebelum</div>
      <div style="font-size:0.875rem;color:rgba(226,232,240,0.8);">{{ number_format($stockMovement->quantity_before, 2) }}</div>
    </div>
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Stok Sesudah</div>
      <div style="font-size:0.875rem;font-weight:600;color:#e2e8f0;">{{ number_format($stockMovement->quantity_after, 2) }}</div>
    </div>
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Dibuat Oleh</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $stockMovement->user->name ?? '-' }}</div>
    </div>
    @if($stockMovement->reference_document)
    <div>
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Dok. Referensi</div>
      <div class="font-mono text-sm" style="color:rgba(165,180,252,0.8);">{{ $stockMovement->reference_document }}</div>
    </div>
    @endif
    @if($stockMovement->notes)
    <div style="grid-column:span 2;">
      <div style="font-size:0.72rem;color:rgba(148,163,184,0.55);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:6px;">Catatan</div>
      <div style="font-size:0.875rem;color:rgba(226,232,240,0.8);background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:10px 12px;">
        {{ $stockMovement->notes }}
      </div>
    </div>
    @endif
  </div>

  <div class="modal-footer" style="justify-content:flex-end;">
    <a href="{{ route('stock-movements.index') }}" class="modal-btn-cancel">Tutup</a>
  </div>
</x-modal>

@endsection