<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockOpname;
use App\Models\Warehouse;
use App\Services\StockOpnameService;
use Illuminate\Http\Request;

class StockOpnameController extends Controller
{
  public function __construct(protected StockOpnameService $service) {}

  public function index(Request $request)
  {
    $opnames = StockOpname::with('warehouse', 'user')
      ->when($request->status, fn($q) => $q->where('status', $request->status))
      ->latest()->paginate(15)->withQueryString();

    $statuses = StockOpname::STATUSES;
    return view('stock-opnames.index', compact('opnames', 'statuses'));
  }

  public function create()
  {
    $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
    return view('stock-opnames.create', compact('warehouses'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'warehouse_id' => 'required|exists:warehouses,id',
      'counted_at'   => 'required|date',
      'notes'        => 'nullable|string|max:500',
    ]);

    $opname = StockOpname::create([
      'reference_number' => $this->service->generateReference(),
      'warehouse_id'     => $data['warehouse_id'],
      'counted_at'       => $data['counted_at'],
      'notes'            => $data['notes'] ?? null,
      'status'           => 'draft',
      'user_id'          => auth()->id(),
    ]);

    return redirect()->route('stock-opnames.show', $opname)->with('success', 'Stock opname created: ' . $opname->reference_number);
  }

  public function show(StockOpname $stockOpname)
  {
    $stockOpname->load('warehouse', 'user', 'items.item.unit', 'items.location');
    $opname = $stockOpname;
    return view('stock-opnames.show', compact('opname'));
  }

  public function loadStock(StockOpname $stockOpname)
  {
    try {
      $this->service->loadSystemQuantities($stockOpname);
      return back()->with('success', 'System stock quantities loaded. You can now enter physical counts.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function saveCount(Request $request, StockOpname $stockOpname)
  {
    $request->validate([
      'counts'   => 'required|array',
      'counts.*' => 'nullable|numeric|min:0',
    ]);

    try {
      $this->service->savePhysicalCount($stockOpname, $request->counts);
      return back()->with('success', 'Physical counts saved.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function complete(StockOpname $stockOpname)
  {
    try {
      $this->service->complete($stockOpname, auth()->id());
      return back()->with('success', 'Stock opname completed. Stock adjustments applied.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function cancel(StockOpname $stockOpname)
  {
    if (!in_array($stockOpname->status, ['draft', 'in_progress'])) {
      return back()->with('error', 'Cannot cancel this opname.');
    }
    $stockOpname->update(['status' => 'cancelled']);
    return back()->with('success', 'Stock opname cancelled.');
  }
}
