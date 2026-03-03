<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Ringkasan Stok</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 14mm 12mm;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 11px;
      color: #1a1a1a;
      margin: 0;
      background: #f3f4f6;
    }

    .page {
      background: #fff;
      max-width: 1050px;
      margin: 20px auto;
      padding: 22px 26px;
      border-radius: 8px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, .1);
    }

    .toolbar {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      margin-bottom: 14px;
    }

    .btn-print {
      background: #dc2626;
      color: #fff;
      border: none;
      padding: 7px 16px;
      font-size: 12px;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-back {
      background: #6b7280;
      color: #fff;
      border: none;
      padding: 7px 16px;
      font-size: 12px;
      border-radius: 6px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .btn-print:hover {
      background: #b91c1c;
    }

    .btn-back:hover {
      background: #4b5563;
    }

    @media print {
      .toolbar {
        display: none !important;
      }

      body {
        background: none;
      }

      .page {
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: none;
        border-radius: 0;
      }
    }

    .header {
      text-align: center;
      border-bottom: 2px solid #1e3a5f;
      padding-bottom: 8px;
      margin-bottom: 14px;
    }

    .header h1 {
      font-size: 16px;
      font-weight: bold;
      color: #1e3a5f;
      margin: 0 0 2px;
    }

    .header p {
      font-size: 10px;
      color: #555;
      margin: 0;
    }

    .meta {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-size: 10px;
      color: #555;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead th {
      background: #1e3a5f;
      color: #fff;
      padding: 6px 8px;
      text-align: left;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    thead th.r {
      text-align: right;
    }

    tbody tr:nth-child(even) {
      background: #f5f7fb;
    }

    tbody td {
      padding: 5px 8px;
      border-bottom: 1px solid #e5e7eb;
      vertical-align: middle;
    }

    tbody td.r {
      text-align: right;
    }

    .badge {
      display: inline-block;
      padding: 1px 6px;
      border-radius: 8px;
      font-size: 9px;
      font-weight: bold;
    }

    .badge-ok {
      background: #d1fae5;
      color: #065f46;
    }

    .badge-low {
      background: #fee2e2;
      color: #991b1b;
    }

    .red {
      color: #dc2626;
      font-weight: bold;
    }

    .footer {
      margin-top: 16px;
      text-align: right;
      font-size: 9px;
      color: #9ca3af;
    }
  </style>
</head>

<body>
  <div class="page">
    <div class="toolbar">
      <a href="javascript:history.back()" class="btn-back">&#8592; Kembali</a>
      <button onclick="window.print()" class="btn-print">&#128438; Print / Save as PDF</button>
    </div>
    <div class="header">
      <h1>PT Sumber Karya Baja Utama</h1>
      <p>Laporan Ringkasan Stok &mdash; {{ $warehouseName }}</p>
    </div>
    <div class="meta">
      <span>Total Baris: <strong>{{ $stocks->count() }}</strong></span>
      <span>Digenerate: {{ $generatedAt }}</span>
    </div>
    <table>
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama Item</th>
          <th>Kategori</th>
          <th>Gudang</th>
          <th class="r">Stok</th>
          <th class="r">Min</th>
          <th>Satuan</th>
          <th>Kondisi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($stocks as $s)
        <tr>
          <td style="font-family:monospace;font-size:10px;color:#6b7280">{{ $s->item->code }}</td>
          <td>{{ $s->item->name }}</td>
          <td style="color:#6b7280">{{ $s->item->category->name ?? '-' }}</td>
          <td style="color:#6b7280">{{ $s->warehouse->name }}</td>
          <td class="r {{ $s->quantity <= $s->item->min_stock ? 'red' : '' }}">{{ number_format($s->quantity, 2) }}</td>
          <td class="r" style="color:#9ca3af">{{ number_format($s->item->min_stock, 2) }}</td>
          <td style="color:#6b7280">{{ $s->item->unit->abbreviation ?? '-' }}</td>
          <td><span class="badge {{ $s->quantity <= $s->item->min_stock ? 'badge-low' : 'badge-ok' }}">{{ $s->quantity <= $s->item->min_stock ? 'Rendah' : 'Normal' }}</span></td>
        </tr>
        @empty
        <tr>
          <td colspan="8" style="text-align:center;padding:20px;color:#9ca3af">Tidak ada data stok.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    <div class="footer">Sistem Manajemen Inventori &mdash; PT Sumber Karya Baja Utama</div>
  </div>
</body>

</html>