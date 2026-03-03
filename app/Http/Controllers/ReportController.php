<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
  public function stockSummary(Request $request)
  {
    $warehouseId = $request->warehouse_id;

    $stocks = Stock::with('item.category', 'item.unit', 'warehouse', 'location')
      ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
      ->where('quantity', '>', 0)
      ->orderBy('warehouse_id')
      ->paginate(50);

    $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

    return view('reports.stock-summary', compact('stocks', 'warehouses'));
  }

  public function lowStock(Request $request)
  {
    $warehouseId = $request->warehouse_id;
    $warehouses  = Warehouse::where('is_active', true)->orderBy('name')->get();

    $items = Item::with(['stocks', 'unit', 'category'])
      ->where('is_active', true)
      ->get()
      ->filter(function ($item) use ($warehouseId) {
        // Use pre-loaded stocks collection to avoid N+1 queries
        $totalQty = $warehouseId
          ? (float) $item->stocks->where('warehouse_id', $warehouseId)->sum('quantity')
          : (float) $item->stocks->sum('quantity');

        return $totalQty < (float) $item->min_stock;
      })
      ->sortBy('name')
      ->values();

    return view('reports.low-stock', compact('items', 'warehouses'));
  }

  public function movementHistory(Request $request)
  {
    $query = StockMovement::with('item.unit', 'user', 'fromWarehouse', 'toWarehouse')
      ->when($request->type, fn($q) => $q->where('type', $request->type))
      ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
      ->when($request->warehouse_id, fn($q) => $q->where(function ($sub) use ($request) {
        $sub->where('from_warehouse_id', $request->warehouse_id)
          ->orWhere('to_warehouse_id', $request->warehouse_id);
      }))
      ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
      ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

    $movements  = $query->latest()->paginate(50)->withQueryString();
    $items      = Item::where('is_active', true)->orderBy('name')->get();
    $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
    $types      = StockMovement::TYPES;

    return view('reports.movement-history', compact('movements', 'items', 'warehouses', 'types'));
  }

  // ─── CSV EXPORTS ────────────────────────────────────────────────

  public function stockSummaryCsv(Request $request)
  {
    $warehouseId = $request->warehouse_id;
    $stocks = Stock::with('item.category', 'item.unit', 'warehouse')
      ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
      ->where('quantity', '>', 0)
      ->orderBy('warehouse_id')
      ->get();

    $warehouseName = $warehouseId
      ? Warehouse::find($warehouseId)?->name ?? 'Semua Gudang'
      : 'Semua Gudang';

    $filename = 'ringkasan-stok-' . now()->format('Ymd_His') . '.xls';
    $headers  = [
      'Content-Type'        => 'application/vnd.ms-excel',
      'Content-Disposition' => "attachment; filename=\"$filename\"",
      'Pragma'              => 'no-cache',
      'Expires'             => '0',
    ];

    $html  = $this->excelWrap(function () use ($stocks, $warehouseName) {
      $title    = 'Laporan Ringkasan Stok — ' . $warehouseName;
      $genAt    = now()->format('d/m/Y H:i');
      $cols     = ['Kode', 'Nama Item', 'Kategori', 'Gudang', 'Stok', 'Stok Min', 'Satuan', 'Kondisi'];
      $rows = [];
      foreach ($stocks as $s) {
        $low = $s->quantity <= $s->item->min_stock;
        $rows[] = [
          htmlspecialchars($s->item->code ?? '-'),
          htmlspecialchars($s->item->name),
          htmlspecialchars($s->item->category->name ?? '-'),
          htmlspecialchars($s->warehouse->name),
          ['v' => number_format($s->quantity, 2, '.', ''), 'align' => 'right', 'color' => $low ? '#dc2626' : ''],
          ['v' => number_format($s->item->min_stock, 2, '.', ''), 'align' => 'right'],
          htmlspecialchars($s->item->unit->abbreviation ?? '-'),
          ['v' => $low ? 'Rendah' : 'Normal', 'bold' => true, 'color' => $low ? '#dc2626' : '#059669'],
        ];
      }
      return $this->buildExcelTable($title, $genAt, $cols, $rows, '#1e3a5f');
    });

    return response($html, 200, $headers);
  }

  public function lowStockCsv(Request $request)
  {
    $warehouseId = $request->warehouse_id;
    $items = Item::with(['stocks', 'unit', 'category'])
      ->where('is_active', true)->get()
      ->filter(fn($item) => ($warehouseId
        ? (float) $item->stocks->where('warehouse_id', $warehouseId)->sum('quantity')
        : (float) $item->stocks->sum('quantity')) < (float) $item->min_stock)
      ->sortBy('name')->values();

    $warehouseName = $warehouseId
      ? Warehouse::find($warehouseId)?->name ?? 'Semua Gudang'
      : 'Semua Gudang';

    $filename = 'stok-rendah-' . now()->format('Ymd_His') . '.xls';
    $headers  = [
      'Content-Type'        => 'application/vnd.ms-excel',
      'Content-Disposition' => "attachment; filename=\"$filename\"",
      'Pragma'              => 'no-cache',
      'Expires'             => '0',
    ];

    $html = $this->excelWrap(function () use ($items, $warehouseId, $warehouseName) {
      $title = 'Laporan Item Stok Rendah — ' . $warehouseName;
      $genAt = now()->format('d/m/Y H:i');
      $cols  = ['Kode', 'Nama Item', 'Kategori', 'Total Stok', 'Stok Min', 'Kekurangan', 'Satuan'];
      $rows  = [];
      foreach ($items as $item) {
        $qty     = $warehouseId
          ? (float) $item->stocks->where('warehouse_id', $warehouseId)->sum('quantity')
          : (float) $item->stocks->sum('quantity');
        $deficit = $item->min_stock - $qty;
        $rows[] = [
          htmlspecialchars($item->code ?? '-'),
          htmlspecialchars($item->name),
          htmlspecialchars($item->category->name ?? '-'),
          ['v' => number_format($qty, 2, '.', ''), 'align' => 'right', 'color' => '#dc2626', 'bold' => true],
          ['v' => number_format($item->min_stock, 2, '.', ''), 'align' => 'right'],
          ['v' => number_format($deficit, 2, '.', ''), 'align' => 'right', 'color' => '#d97706', 'bold' => true],
          htmlspecialchars($item->unit->abbreviation ?? '-'),
        ];
      }
      return $this->buildExcelTable($title, $genAt, $cols, $rows, '#92400e');
    });

    return response($html, 200, $headers);
  }

  public function movementHistoryCsv(Request $request)
  {
    $movements = StockMovement::with('item.unit', 'user', 'fromWarehouse', 'toWarehouse')
      ->when($request->type, fn($q) => $q->where('type', $request->type))
      ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
      ->when($request->warehouse_id, fn($q) => $q->where(function ($sub) use ($request) {
        $sub->where('from_warehouse_id', $request->warehouse_id)
          ->orWhere('to_warehouse_id', $request->warehouse_id);
      }))
      ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
      ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
      ->latest()->get();

    $filename = 'mutasi-stok-' . now()->format('Ymd_His') . '.xls';
    $headers  = [
      'Content-Type'        => 'application/vnd.ms-excel',
      'Content-Disposition' => "attachment; filename=\"$filename\"",
      'Pragma'              => 'no-cache',
      'Expires'             => '0',
    ];

    $html = $this->excelWrap(function () use ($movements) {
      $title = 'Riwayat Mutasi Stok';
      $genAt = now()->format('d/m/Y H:i');
      $cols  = ['No. Referensi', 'Tanggal', 'Tipe', 'Item', 'Qty', 'Dari Gudang', 'Ke Gudang', 'Oleh', 'Dok. Referensi', 'Catatan'];
      $rows  = [];
      foreach ($movements as $m) {
        $qty = $m->quantity;
        $rows[] = [
          ['v' => htmlspecialchars($m->reference_number ?? '-'), 'mono' => true],
          htmlspecialchars($m->created_at->format('d/m/Y H:i')),
          ['v' => htmlspecialchars($m->type_label), 'bold' => true],
          htmlspecialchars($m->item->name ?? '-'),
          ['v' => ($qty > 0 ? '+' : '') . number_format($qty, 2, '.', ''), 'align' => 'right', 'color' => $qty > 0 ? '#059669' : '#dc2626', 'bold' => true],
          htmlspecialchars($m->fromWarehouse->name ?? '-'),
          htmlspecialchars($m->toWarehouse->name ?? '-'),
          htmlspecialchars($m->user->name ?? '-'),
          htmlspecialchars($m->reference_document ?? '-'),
          htmlspecialchars($m->notes ?? '-'),
        ];
      }
      return $this->buildExcelTable($title, $genAt, $cols, $rows, '#1e3a5f');
    });

    return response($html, 200, $headers);
  }

  // ─── PDF EXPORTS ────────────────────────────────────────────────

  public function stockSummaryPdf(Request $request)
  {
    $warehouseId = $request->warehouse_id;
    $stocks = Stock::with('item.category', 'item.unit', 'warehouse')
      ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
      ->where('quantity', '>', 0)
      ->orderBy('warehouse_id')
      ->get();
    $warehouseName = $warehouseId
      ? Warehouse::find($warehouseId)?->name ?? 'Semua Gudang'
      : 'Semua Gudang';
    $generatedAt = now()->format('d/m/Y H:i');

    return view('reports.pdf.stock-summary', compact('stocks', 'warehouseName', 'generatedAt'));
  }

  public function lowStockPdf(Request $request)
  {
    $warehouseId = $request->warehouse_id;
    $items = Item::with(['stocks', 'unit', 'category'])
      ->where('is_active', true)->get()
      ->filter(fn($item) => (
        $warehouseId
        ? (float) $item->stocks->where('warehouse_id', $warehouseId)->sum('quantity')
        : (float) $item->stocks->sum('quantity')
      ) < (float) $item->min_stock)
      ->sortBy('name')->values();
    $warehouseName = $warehouseId
      ? Warehouse::find($warehouseId)?->name ?? 'Semua Gudang'
      : 'Semua Gudang';
    $generatedAt = now()->format('d/m/Y H:i');

    return view('reports.pdf.low-stock', compact('items', 'warehouseName', 'generatedAt', 'warehouseId'));
  }

  public function movementHistoryPdf(Request $request)
  {
    $movements = StockMovement::with('item.unit', 'user', 'fromWarehouse', 'toWarehouse')
      ->when($request->type, fn($q) => $q->where('type', $request->type))
      ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
      ->when($request->warehouse_id, fn($q) => $q->where(function ($sub) use ($request) {
        $sub->where('from_warehouse_id', $request->warehouse_id)
          ->orWhere('to_warehouse_id', $request->warehouse_id);
      }))
      ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
      ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
      ->latest()->get();
    $generatedAt = now()->format('d/m/Y H:i');
    $filterInfo  = collect([
      $request->type        ? 'Tipe: ' . ($request->type) : null,
      $request->date_from   ? 'Dari: ' . $request->date_from : null,
      $request->date_to     ? 'Sampai: ' . $request->date_to : null,
    ])->filter()->implode(' | ') ?: 'Semua data';

    return view('reports.pdf.movement-history', compact('movements', 'generatedAt', 'filterInfo'));
  }

  // ─── EXCEL HELPERS ───────────────────────────────────────────────

  private function excelWrap(callable $body): string
  {
    return '<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40"><head>
      <meta charset="UTF-8">
      <!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>
      <x:ExcelWorksheet><x:Name>Laporan</x:Name>
      <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
      </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
      </head><body>' . $body() . '</body></html>';
  }

  private function buildExcelTable(string $title, string $genAt, array $cols, array $rows, string $headerBg = '#1e3a5f'): string
  {
    $baseStyle = 'font-family:Arial,sans-serif;font-size:11pt;';
    $html  = '<table border="0" cellpadding="0" cellspacing="0" style="' . $baseStyle . '">';
    // Title row
    $html .= '<tr><td colspan="' . count($cols) . '" style="font-size:14pt;font-weight:bold;color:#1e3a5f;padding:6px 4px 2px;">' . htmlspecialchars($title) . '</td></tr>';
    $html .= '<tr><td colspan="' . count($cols) . '" style="font-size:9pt;color:#6b7280;padding:0 4px 8px;">Digenerate: ' . $genAt . ' &nbsp;|&nbsp; Total: ' . count($rows) . ' baris</td></tr>';
    // Header row
    $html .= '<tr>';
    foreach ($cols as $col) {
      $html .= '<td style="background:' . $headerBg . ';color:#ffffff;font-weight:bold;font-size:10pt;padding:6px 10px;border:1px solid ' . $headerBg . ';white-space:nowrap;">' . htmlspecialchars($col) . '</td>';
    }
    $html .= '</tr>';
    // Data rows
    foreach ($rows as $i => $row) {
      $bg = $i % 2 === 0 ? '#ffffff' : '#f5f7fb';
      $html .= '<tr>';
      foreach ($row as $cell) {
        if (is_array($cell)) {
          $v     = $cell['v'] ?? '';
          $align = $cell['align'] ?? 'left';
          $color = isset($cell['color']) && $cell['color'] ? 'color:' . $cell['color'] . ';' : '';
          $bold  = ($cell['bold'] ?? false) ? 'font-weight:bold;' : '';
          $mono  = ($cell['mono'] ?? false) ? 'font-family:Courier New,monospace;font-size:10pt;' : '';
          $html .= '<td style="background:' . $bg . ';padding:5px 10px;border:1px solid #e5e7eb;text-align:' . $align . ';' . $color . $bold . $mono . '">' . $v . '</td>';
        } else {
          $html .= '<td style="background:' . $bg . ';padding:5px 10px;border:1px solid #e5e7eb;">' . $cell . '</td>';
        }
      }
      $html .= '</tr>';
    }
    if (empty($rows)) {
      $html .= '<tr><td colspan="' . count($cols) . '" style="text-align:center;padding:20px;color:#9ca3af;">Tidak ada data.</td></tr>';
    }
    $html .= '</table>';
    return $html;
  }
}
