<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\ProductionOrder;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
  public function index()
  {
    // ── Stat Cards ────────────────────────────────────────────────
    $totalItems     = Item::where('is_active', true)->count();
    $lowStockItems  = Item::with('stocks', 'unit')
      ->where('is_active', true)
      ->get()
      ->filter(fn($item) => $item->isBelowMinStock())
      ->take(10);

    $pendingPOs     = PurchaseOrder::whereIn('status', ['draft', 'approved'])->count();
    $pendingWOs     = ProductionOrder::whereIn('status', ['draft', 'in_progress'])->count();
    $pendingOpnames = StockOpname::whereIn('status', ['draft', 'in_progress'])->count();

    $recentMovements = StockMovement::with('item', 'user')
      ->latest()
      ->take(10)
      ->get();

    $totalStockValue = Stock::count();

    // ── Chart 1: Tren Mutasi Stok 30 Hari Terakhir ───────────────
    $thirtyDaysAgo = now()->subDays(29)->startOfDay();
    $dailyMovements = StockMovement::selectRaw('DATE(created_at) as date, type, SUM(quantity) as total')
      ->where('created_at', '>=', $thirtyDaysAgo)
      ->groupBy('date', 'type')
      ->get();

    $dates = collect();
    for ($i = 29; $i >= 0; $i--) {
      $dates->push(now()->subDays($i)->format('Y-m-d'));
    }

    $inboundTypes  = ['goods_receipt', 'production_output'];
    $outboundTypes = ['material_issue', 'sales_dispatch'];

    $chartTrend = [
      'labels'   => $dates->map(fn($d) => \Carbon\Carbon::parse($d)->translatedFormat('d M'))->values()->toArray(),
      'inbound'  => $dates->map(fn($d) => round((float) $dailyMovements->where('date', $d)->whereIn('type', $inboundTypes)->sum('total'), 2))->values()->toArray(),
      'outbound' => $dates->map(fn($d) => round((float) $dailyMovements->where('date', $d)->whereIn('type', $outboundTypes)->sum('total'), 2))->values()->toArray(),
    ];

    // ── Chart 2: Distribusi Tipe Mutasi Bulan Ini ─────────────────
    $movementByType = StockMovement::selectRaw('type, COUNT(*) as count')
      ->whereMonth('created_at', now()->month)
      ->whereYear('created_at', now()->year)
      ->groupBy('type')
      ->get();

    $typeLabels = StockMovement::TYPES;
    $chartTypes = [
      'labels' => $movementByType->pluck('type')->map(fn($t) => $typeLabels[$t] ?? $t)->values()->toArray(),
      'data'   => $movementByType->pluck('count')->values()->toArray(),
    ];

    // ── Chart 3: Top 10 Item Stok Tertinggi ──────────────────────
    $topStocks = DB::table('stocks')
      ->join('items', 'stocks.item_id', '=', 'items.id')
      ->join('units', 'items.unit_id', '=', 'units.id')
      ->selectRaw('items.name, units.abbreviation, SUM(stocks.quantity) as total_qty')
      ->groupBy('stocks.item_id', 'items.name', 'units.abbreviation')
      ->orderByDesc('total_qty')
      ->limit(10)
      ->get();

    $chartTopStock = [
      'labels' => $topStocks->pluck('name')->toArray(),
      'data'   => $topStocks->map(fn($s) => round((float) $s->total_qty, 2))->toArray(),
      'units'  => $topStocks->pluck('abbreviation')->toArray(),
    ];

    // ── Chart 4: Status PO & Work Order ──────────────────────────
    $poStatuses = PurchaseOrder::STATUSES;
    $woStatuses = ProductionOrder::STATUSES;

    $poByStatus = PurchaseOrder::selectRaw('status, COUNT(*) as count')
      ->groupBy('status')->get()->pluck('count', 'status');

    $woByStatus = ProductionOrder::selectRaw('status, COUNT(*) as count')
      ->groupBy('status')->get()->pluck('count', 'status');

    $chartOrders = [
      'labels'    => array_values($poStatuses),
      'poKeys'    => array_keys($poStatuses),
      'poData'    => collect(array_keys($poStatuses))->map(fn($s) => (int) ($poByStatus[$s] ?? 0))->toArray(),
      'woLabels'  => array_values($woStatuses),
      'woData'    => collect(array_keys($woStatuses))->map(fn($s) => (int) ($woByStatus[$s] ?? 0))->toArray(),
    ];

    return view('dashboard', compact(
      'totalItems',
      'lowStockItems',
      'pendingPOs',
      'pendingWOs',
      'pendingOpnames',
      'recentMovements',
      'totalStockValue',
      'chartTrend',
      'chartTypes',
      'chartTopStock',
      'chartOrders'
    ));
  }
}
