<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
  public function __construct(protected StockService $stockService) {}

  public function index(Request $request)
  {
    $query = StockMovement::with('item.unit', 'user', 'toWarehouse', 'fromWarehouse')
      ->when($request->type, fn($q) => $q->where('type', $request->type))
      ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
      ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
      ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

    $movements  = $query->latest()->paginate(20)->withQueryString();
    $items      = Item::where('is_active', true)->orderBy('name')->get();
    $types      = StockMovement::TYPES;

    return view('stock-movements.index', compact('movements', 'items', 'types'));
  }

  public function create()
  {
    $items      = Item::where('is_active', true)->with('unit')->orderBy('name')->get();
    $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
    $types      = [
      'goods_receipt'    => 'Goods Receipt (Inbound)',
      'material_issue'   => 'Material Issue to Production',
      'stock_transfer'   => 'Stock Transfer',
      'production_output' => 'Production Output',
      'sales_dispatch'   => 'Sales Dispatch (Outbound)',
      'stock_adjustment' => 'Stock Adjustment',
    ];
    return view('stock-movements.create', compact('items', 'warehouses', 'types'));
  }

  public function store(Request $request)
  {
    $type         = $request->input('type');
    $requiresTo   = ['goods_receipt', 'production_output', 'stock_transfer', 'stock_adjustment'];
    $requiresFrom = ['material_issue', 'sales_dispatch', 'stock_transfer'];

    $data = $request->validate([
      'type'               => 'required|in:goods_receipt,material_issue,stock_transfer,production_output,sales_dispatch,stock_adjustment',
      'item_id'            => ['required', Rule::exists('items', 'id')->where('is_active', true)],
      'quantity'           => [Rule::requiredIf($type !== 'stock_adjustment'), 'nullable', 'numeric', 'min:0.0001'],
      'to_warehouse_id'    => [Rule::requiredIf(in_array($type, $requiresTo)), 'nullable', Rule::exists('warehouses', 'id')->where('is_active', true)],
      'from_warehouse_id'  => [Rule::requiredIf(in_array($type, $requiresFrom)), 'nullable', Rule::exists('warehouses', 'id')->where('is_active', true)],
      'reference_document' => 'nullable|string|max:100',
      'notes'              => 'nullable|string|max:500',
      'new_quantity'       => [Rule::requiredIf($type === 'stock_adjustment'), 'nullable', 'numeric', 'min:0'],
    ]);

    // Batasi stock_adjustment hanya untuk role tinggi
    if ($type === 'stock_adjustment') {
      if (!auth()->user()->hasRole(['admin', 'inventory_controller', 'supervisor'])) {
        abort(403, 'Stock adjustment memerlukan hak akses lebih tinggi.');
      }
    }

    try {
      $item = Item::findOrFail($data['item_id']);
      $userId = auth()->id();

      match ($data['type']) {
        'goods_receipt', 'production_output' => $this->stockService->goodsReceipt(
          $item,
          Warehouse::findOrFail($data['to_warehouse_id']),
          $data['quantity'],
          $userId,
          $data['reference_document'] ?? '',
          $data['notes'] ?? ''
        ),
        'material_issue', 'sales_dispatch' => $this->stockService->materialIssue(
          $item,
          Warehouse::findOrFail($data['from_warehouse_id']),
          $data['quantity'],
          $userId,
          $data['reference_document'] ?? '',
          $data['notes'] ?? ''
        ),
        'stock_transfer' => $this->stockService->stockTransfer(
          $item,
          Warehouse::findOrFail($data['from_warehouse_id']),
          Warehouse::findOrFail($data['to_warehouse_id']),
          $data['quantity'],
          $userId,
          $data['notes'] ?? ''
        ),
        'stock_adjustment' => $this->stockService->stockAdjustment(
          $item,
          Warehouse::findOrFail($data['to_warehouse_id']),
          (float) ($data['new_quantity'] ?? 0),
          $userId,
          $data['notes'] ?? ''
        ),
        default => throw new \RuntimeException('Unknown movement type.')
      };

      return redirect()->route('stock-movements.index')->with('success', 'Stock movement recorded successfully.');
    } catch (\RuntimeException $e) {
      return back()->withInput()->with('error', $e->getMessage());
    }
  }

  public function show(StockMovement $stockMovement)
  {
    $stockMovement->load('item.unit', 'user', 'toWarehouse', 'fromWarehouse', 'toLocation', 'fromLocation');
    return view('stock-movements.show', compact('stockMovement'));
  }
}
