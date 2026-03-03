@extends('layouts.app')
@section('title', 'Detail Purchase Order')
@section('content')

<x-modal id="po-detail" title="Detail PO — {{ $po->po_number }}" size="xl" back-url="{{ route('purchase-orders.index') }}">

  {{-- Header status + actions --}}
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
      <div class="font-mono text-sm" style="color:#a5b4fc;">{{ $po->po_number }}</div>
      <div style="font-size:0.78rem;color:rgba(148,163,184,0.6);margin-top:2px;">
        {{ $po->order_date->format('d F Y') }}
        @if($po->expected_date) &bull; Est. {{ $po->expected_date->format('d F Y') }} @endif
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="status-badge {{ $po->statusColor }}">{{ $po->status_label }}</span>
      {{-- Actions --}}
      @if($po->status === 'draft')
      @if(auth()->user()->hasRole(['admin','inventory_controller','supervisor']))
      <form method="POST" action="{{ route('purchase-orders.approve', $po) }}" onsubmit="return confirm('Approve PO ini?')" style="display:inline;">
        @csrf
        <button class="modal-btn-submit" style="padding:5px 14px;font-size:0.78rem;">Approve</button>
      </form>
      @endif
      <form method="POST" action="{{ route('purchase-orders.cancel', $po) }}" onsubmit="return confirm('Batalkan PO ini?')" style="display:inline;">
        @csrf
        <button class="modal-btn-cancel" style="padding:5px 14px;font-size:0.78rem;background:rgba(239,68,68,0.12);color:#fca5a5;border-color:rgba(239,68,68,0.2);">Batalkan</button>
      </form>
      @elseif($po->status === 'approved')
      @if(auth()->user()->hasRole(['admin','inventory_controller','warehouse_operator']))
      <a href="{{ route('purchase-orders.receive-form', $po) }}" class="modal-btn-submit" style="padding:5px 14px;font-size:0.78rem;">Terima Barang</a>
      @endif
      <form method="POST" action="{{ route('purchase-orders.cancel', $po) }}" onsubmit="return confirm('Batalkan PO ini?')" style="display:inline;">
        @csrf
        <button class="modal-btn-cancel" style="padding:5px 14px;font-size:0.78rem;background:rgba(239,68,68,0.12);color:#fca5a5;border-color:rgba(239,68,68,0.2);">Batalkan</button>
      </form>
      @endif
    </div>
  </div>

  {{-- Info grid --}}
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px;">
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px;">
      <div style="font-size:0.68rem;color:rgba(148,163,184,0.5);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Supplier</div>
      <div style="font-size:0.875rem;font-weight:500;color:#e2e8f0;">{{ $po->supplier->name }}</div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px;">
      <div style="font-size:0.68rem;color:rgba(148,163,184,0.5);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Dibuat Oleh</div>
      <div style="font-size:0.875rem;color:#e2e8f0;">{{ $po->user->name ?? '-' }}</div>
    </div>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:12px;">
      <div style="font-size:0.68rem;color:rgba(148,163,184,0.5);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Total</div>
      <div style="font-size:1.1rem;font-weight:700;color:#e2e8f0;">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</div>
    </div>
  </div>
  @if($po->notes)
  <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:10px 12px;margin-bottom:20px;font-size:0.85rem;color:rgba(226,232,240,0.7);">
    📝 {{ $po->notes }}
  </div>
  @endif

  {{-- Item Table --}}
  <div style="border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;">
    <div style="padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.06);font-size:0.8rem;font-weight:600;color:rgba(165,180,252,0.8);">Item Purchase Order</div>
    <table class="table-inline">
      <thead>
        <tr>
          <th class="text-left">Item</th>
          <th class="text-right">Qty Order</th>
          <th class="text-right">Qty Diterima</th>
          <th class="text-right">Harga Satuan</th>
          <th class="text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($po->items as $line)
        <tr>
          <td style="padding:10px 10px;">
            <div style="font-weight:500;color:#e2e8f0;font-size:0.85rem;">{{ $line->item->name }}</div>
            <div style="font-size:0.7rem;color:rgba(165,180,252,0.7);">{{ $line->item->code }}</div>
          </td>
          <td style="padding:10px;text-align:right;color:rgba(226,232,240,0.8);">{{ number_format($line->quantity_ordered, 2) }}</td>
          <td style="padding:10px;text-align:right;font-weight:600;color:{{ $line->quantity_received < $line->quantity_ordered ? '#fcd34d' : '#6ee7b7' }};">{{ number_format($line->quantity_received, 2) }}</td>
          <td style="padding:10px;text-align:right;color:rgba(226,232,240,0.7);">Rp {{ number_format($line->unit_price, 0, ',', '.') }}</td>
          <td style="padding:10px;text-align:right;font-weight:500;color:#e2e8f0;">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="padding:12px 10px;text-align:right;font-weight:600;color:rgba(148,163,184,0.7);border-top:1px solid rgba(255,255,255,0.07);">Total</td>
          <td style="padding:12px 10px;text-align:right;font-weight:700;color:#e2e8f0;font-size:0.95rem;border-top:1px solid rgba(255,255,255,0.07);">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="modal-footer" style="justify-content:flex-end;">
    <a href="{{ route('purchase-orders.index') }}" class="modal-btn-cancel">Tutup</a>
  </div>
</x-modal>

@endsection