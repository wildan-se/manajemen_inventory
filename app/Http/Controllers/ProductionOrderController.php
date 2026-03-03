<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ProductionOrder;
use App\Models\Warehouse;
use App\Services\ProductionOrderService;
use Illuminate\Http\Request;

class ProductionOrderController extends Controller
{
  public function __construct(protected ProductionOrderService $service) {}

  public function index(Request $request)
  {
    $query = ProductionOrder::with('warehouse', 'user')
      ->when($request->status, fn($q) => $q->where('status', $request->status));

    $orders   = $query->latest()->paginate(15)->withQueryString();
    $statuses = ProductionOrder::STATUSES;
    return view('production-orders.index', compact('orders', 'statuses'));
  }

  public function create()
  {
    $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
    $items      = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
    return view('production-orders.create', compact('warehouses', 'items'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'title'         => 'required|string|max:255',
      'description'   => 'nullable|string|max:1000',
      'warehouse_id'  => 'required|exists:warehouses,id',
      'planned_start' => 'nullable|date',
      'planned_end'   => 'nullable|date|after_or_equal:planned_start',
      'inputs'              => 'required|array|min:1',
      'inputs.*.item_id'   => 'required|exists:items,id',
      'inputs.*.quantity'  => 'required|numeric|min:0.0001',
      'outputs'             => 'required|array|min:1',
      'outputs.*.item_id'  => 'required|exists:items,id',
      'outputs.*.quantity' => 'required|numeric|min:0.0001',
    ]);

    $wo = ProductionOrder::create([
      'wo_number'    => $this->service->generateWoNumber(),
      'title'        => $data['title'],
      'description'  => $data['description'] ?? null,
      'warehouse_id' => $data['warehouse_id'],
      'planned_start' => $data['planned_start'] ?? null,
      'planned_end'  => $data['planned_end'] ?? null,
      'status'       => 'draft',
      'user_id'      => auth()->id(),
    ]);

    foreach ($data['inputs'] as $line) {
      $wo->items()->create([
        'item_id'  => $line['item_id'],
        'quantity' => $line['quantity'],
        'type'     => 'input',
      ]);
    }
    foreach ($data['outputs'] as $line) {
      $wo->items()->create([
        'item_id'  => $line['item_id'],
        'quantity' => $line['quantity'],
        'type'     => 'output',
      ]);
    }

    return redirect()->route('production-orders.show', $wo)->with('success', 'Work order created: ' . $wo->wo_number);
  }

  public function show(ProductionOrder $productionOrder)
  {
    $productionOrder->load('warehouse', 'user', 'inputs.item.unit', 'outputs.item.unit');
    return view('production-orders.show', ['wo' => $productionOrder]);
  }

  public function start(ProductionOrder $productionOrder)
  {
    $this->authorize('start', $productionOrder);
    try {
      $this->service->start($productionOrder, auth()->id());
      return back()->with('success', 'Work order started. Input materials have been issued.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function complete(ProductionOrder $productionOrder)
  {
    $this->authorize('complete', $productionOrder);
    try {
      $this->service->complete($productionOrder, auth()->id());
      return back()->with('success', 'Work order completed. Output items added to stock.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function cancel(ProductionOrder $productionOrder)
  {
    $this->authorize('cancel', $productionOrder);
    try {
      $this->service->cancel($productionOrder);
      return back()->with('success', 'Work order cancelled.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }
}
