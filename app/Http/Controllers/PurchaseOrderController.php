<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
  public function __construct(protected PurchaseOrderService $service) {}

  public function index(Request $request)
  {
    $query = PurchaseOrder::with('supplier', 'warehouse', 'user')
      ->when($request->status, fn($q) => $q->where('status', $request->status))
      ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id));

    $orders    = $query->latest()->paginate(15)->withQueryString();
    $statuses  = PurchaseOrder::STATUSES;
    $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
    return view('purchase-orders.index', compact('orders', 'statuses', 'suppliers'));
  }

  public function create()
  {
    $suppliers  = Supplier::where('is_active', true)->orderBy('name')->get();
    $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
    $items      = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
    return view('purchase-orders.create', compact('suppliers', 'warehouses', 'items'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'supplier_id'   => 'required|exists:suppliers,id',
      'warehouse_id'  => 'required|exists:warehouses,id',
      'order_date'    => 'required|date',
      'expected_date' => 'nullable|date|after_or_equal:order_date',
      'notes'         => 'nullable|string|max:1000',
      'items'         => 'required|array|min:1',
      'items.*.item_id'       => 'required|exists:items,id',
      'items.*.quantity'      => 'required|numeric|min:0.0001',
      'items.*.unit_price'    => 'nullable|numeric|min:0',
    ]);

    $po = PurchaseOrder::create([
      'po_number'    => $this->service->generatePoNumber(),
      'supplier_id'  => $data['supplier_id'],
      'warehouse_id' => $data['warehouse_id'],
      'order_date'   => $data['order_date'],
      'expected_date' => $data['expected_date'] ?? null,
      'notes'        => $data['notes'] ?? null,
      'status'       => 'draft',
      'user_id'      => auth()->id(),
    ]);

    foreach ($data['items'] as $line) {
      $po->items()->create([
        'item_id'          => $line['item_id'],
        'quantity_ordered' => $line['quantity'],
        'unit_price'       => $line['unit_price'] ?? 0,
      ]);
    }

    return redirect()->route('purchase-orders.show', $po)->with('success', 'PO created: ' . $po->po_number);
  }

  public function show(PurchaseOrder $purchaseOrder)
  {
    $purchaseOrder->load('supplier', 'warehouse', 'user', 'approver', 'items.item.unit');
    return view('purchase-orders.show', ['po' => $purchaseOrder]);
  }

  public function approve(PurchaseOrder $purchaseOrder)
  {
    $this->authorize('approve', $purchaseOrder);
    try {
      $this->service->approve($purchaseOrder, auth()->id());
      return back()->with('success', 'Purchase order approved.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function receiveForm(PurchaseOrder $purchaseOrder)
  {
    $purchaseOrder->load('items.item.unit', 'warehouse');
    return view('purchase-orders.receive', ['po' => $purchaseOrder]);
  }

  public function receive(Request $request, PurchaseOrder $purchaseOrder)
  {
    $this->authorize('receive', $purchaseOrder);
    $request->validate([
      'items'        => 'required|array',
      'items.*.quantity' => 'nullable|numeric|min:0',
    ]);

    try {
      $this->service->receive($purchaseOrder, $request->items, auth()->id());
      return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Items received and stock updated.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  public function cancel(PurchaseOrder $purchaseOrder)
  {
    $this->authorize('cancel', $purchaseOrder);
    try {
      $this->service->cancel($purchaseOrder);
      return back()->with('success', 'Purchase order cancelled.');
    } catch (\RuntimeException $e) {
      return back()->with('error', $e->getMessage());
    }
  }
}
