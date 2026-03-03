<?php

use App\Models\Category;
use App\Models\Item;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderInputItem;
use App\Models\ProductionOrderOutputItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ProductionOrderService;
use App\Services\PurchaseOrderService;
use App\Services\StockOpnameService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

// ─── AUTH ─────────────────────────────────────────────────────────────────────

Route::post('/login', function (Request $request) {
    $request->validate(['email' => 'required|email', 'password' => 'required']);
    $user = User::where('email', $request->email)->first();

    if ($user && !$user->is_active) {
        throw ValidationException::withMessages(['email' => ['Akses Ditolak. Akun Anda telah dinonaktifkan.']]);
    }

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
    }
    $token = $user->createToken('api-token')->plainTextToken;
    return response()->json(['token' => $token, 'user' => $user]);
})->middleware('throttle:5,1');

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');

// ─── PROTECTED ROUTES ─────────────────────────────────────────────────────────

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', fn(Request $r) => response()->json($r->user()));

    // ── Categories ──────────────────────────────────────────────────────────
    Route::get('/categories', fn() => response()->json(Category::orderBy('name')->get()));
    Route::post('/categories', function (Request $request) {
        $data = $request->validate(['name' => 'required|string|max:100|unique:categories,name', 'description' => 'nullable|string']);
        return response()->json(Category::create($data), 201);
    });
    Route::put('/categories/{category}', function (Request $request, Category $category) {
        $data = $request->validate(['name' => 'required|string|max:100|unique:categories,name,' . $category->id, 'description' => 'nullable|string']);
        $category->update($data);
        return response()->json($category);
    });
    Route::delete('/categories/{category}', function (Category $category) {
        $category->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // ── Units ────────────────────────────────────────────────────────────────
    Route::get('/units', fn() => response()->json(Unit::orderBy('name')->get()));
    Route::post('/units', function (Request $request) {
        $data = $request->validate(['name' => 'required|string|max:50|unique:units,name', 'abbreviation' => 'required|string|max:20']);
        return response()->json(Unit::create($data), 201);
    });
    Route::put('/units/{unit}', function (Request $request, Unit $unit) {
        $data = $request->validate(['name' => 'required|string|max:50|unique:units,name,' . $unit->id, 'abbreviation' => 'required|string|max:20']);
        $unit->update($data);
        return response()->json($unit);
    });
    Route::delete('/units/{unit}', function (Unit $unit) {
        $unit->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // ── Suppliers ────────────────────────────────────────────────────────────
    Route::get('/suppliers', fn() => response()->json(Supplier::orderBy('name')->get()));
    Route::post('/suppliers', function (Request $request) {
        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:suppliers,code',
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'is_active'      => 'boolean',
        ]);
        return response()->json(Supplier::create($data), 201);
    });
    Route::get('/suppliers/{supplier}', fn(Supplier $supplier) => response()->json($supplier));
    Route::put('/suppliers/{supplier}', function (Request $request, Supplier $supplier) {
        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'is_active'      => 'boolean',
        ]);
        $supplier->update($data);
        return response()->json($supplier);
    });
    Route::delete('/suppliers/{supplier}', function (Supplier $supplier) {
        $supplier->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // ── Warehouses ───────────────────────────────────────────────────────────
    Route::get('/warehouses', fn() => response()->json(Warehouse::with('locations')->orderBy('name')->get()));
    Route::post('/warehouses', function (Request $request) {
        $data = $request->validate(['code' => 'required|string|max:50|unique:warehouses,code', 'name' => 'required|string|max:255', 'address' => 'nullable|string', 'is_active' => 'boolean']);
        return response()->json(Warehouse::create($data), 201);
    });
    Route::get('/warehouses/{warehouse}', fn(Warehouse $warehouse) => response()->json($warehouse->load('locations')));
    Route::put('/warehouses/{warehouse}', function (Request $request, Warehouse $warehouse) {
        $data = $request->validate(['code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id, 'name' => 'required|string|max:255', 'address' => 'nullable|string', 'is_active' => 'boolean']);
        $warehouse->update($data);
        return response()->json($warehouse);
    });
    Route::delete('/warehouses/{warehouse}', function (Warehouse $warehouse) {
        $warehouse->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // ── Items ────────────────────────────────────────────────────────────────
    Route::get('/items', function (Request $request) {
        $q = Item::with('category', 'unit');
        if ($request->search) $q->where(fn($x) => $x->where('code', 'like', "%{$request->search}%")->orWhere('name', 'like', "%{$request->search}%"));
        if ($request->category_id) $q->where('category_id', $request->category_id);
        if ($request->filled('is_active')) $q->where('is_active', $request->is_active);
        return response()->json($q->orderBy('name')->paginate(50));
    });
    Route::post('/items', function (Request $request) {
        $data = $request->validate([
            'code'        => 'required|string|max:50|unique:items,code',
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'description' => 'nullable|string',
            'min_stock'   => 'nullable|numeric|min:0',
            'max_stock'   => 'nullable|numeric|min:0',
            'is_active'   => 'boolean',
        ]);
        return response()->json(Item::create($data)->load('category', 'unit'), 201);
    });
    Route::get('/items/{item}', fn(Item $item) => response()->json($item->load('category', 'unit', 'stocks.warehouse')));
    Route::put('/items/{item}', function (Request $request, Item $item) {
        $data = $request->validate([
            'code'        => 'required|string|max:50|unique:items,code,' . $item->id,
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'description' => 'nullable|string',
            'min_stock'   => 'nullable|numeric|min:0',
            'max_stock'   => 'nullable|numeric|min:0',
            'is_active'   => 'boolean',
        ]);
        $item->update($data);
        return response()->json($item->load('category', 'unit'));
    });
    Route::delete('/items/{item}', function (Item $item) {
        $item->delete();
        return response()->json(['message' => 'Deleted']);
    });

    // ── Stock ────────────────────────────────────────────────────────────────
    Route::get('/stocks', function (Request $request) {
        $q = Stock::with('item', 'warehouse', 'location');
        if ($request->warehouse_id) $q->where('warehouse_id', $request->warehouse_id);
        if ($request->item_id) $q->where('item_id', $request->item_id);
        return response()->json($q->get());
    });

    // ── Stock Movements ──────────────────────────────────────────────────────
    Route::get('/stock-movements', function (Request $request) {
        $q = StockMovement::with('item', 'user', 'toWarehouse', 'fromWarehouse');
        if ($request->type) $q->where('type', $request->type);
        if ($request->item_id) $q->where('item_id', $request->item_id);
        return response()->json($q->latest()->paginate(50));
    });
    Route::post('/stock-movements', function (Request $request) {
        $data = $request->validate([
            'type'              => 'required|in:goods_receipt,material_issue,stock_transfer,stock_adjustment,sales_dispatch,production_output',
            'item_id'           => 'required|exists:items,id',
            'to_warehouse_id'   => 'nullable|exists:warehouses,id',
            'from_warehouse_id' => 'nullable|exists:warehouses,id',
            'quantity'          => 'required|numeric|min:0.0001',
            'reference_number'  => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);
        $data['user_id'] = $request->user()->id;
        $svc = app(StockService::class);
        $movement = $svc->recordMovement($data);
        return response()->json($movement->load('item', 'toWarehouse', 'fromWarehouse', 'user'), 201);
    });
    Route::get('/stock-movements/{stockMovement}', fn(StockMovement $stockMovement) => response()->json($stockMovement->load('item', 'user', 'toWarehouse', 'fromWarehouse')));

    // ── Purchase Orders ──────────────────────────────────────────────────────
    Route::get('/purchase-orders', fn() => response()->json(PurchaseOrder::with('supplier')->latest()->paginate(50)));
    Route::post('/purchase-orders', function (Request $request) {
        $data = $request->validate([
            'po_number'     => 'required|string|max:100|unique:purchase_orders,po_number',
            'supplier_id'   => 'required|exists:suppliers,id',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'expected_date' => 'required|date',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.item_id'    => 'required|exists:items,id',
            'items.*.quantity'   => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
        $svc = app(PurchaseOrderService::class);
        $po = $svc->create($data, $request->user());
        return response()->json($po->load('supplier', 'items.item'), 201);
    });
    Route::get('/purchase-orders/{purchaseOrder}', fn(PurchaseOrder $purchaseOrder) => response()->json($purchaseOrder->load('supplier', 'warehouse', 'items.item', 'user')));
    Route::post('/purchase-orders/{purchaseOrder}/approve', function (Request $request, PurchaseOrder $purchaseOrder) {
        $svc = app(PurchaseOrderService::class);
        $svc->approve($purchaseOrder, $request->user()->id);
        return response()->json($purchaseOrder->fresh()->load('supplier', 'items.item'));
    });
    Route::post('/purchase-orders/{purchaseOrder}/receive', function (Request $request, PurchaseOrder $purchaseOrder) {
        $request->validate([
            'items' => 'required|array',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity'       => 'required|numeric|min:0',
        ]);
        $receivedItems = collect($request->input('items'))
            ->keyBy('purchase_order_item_id')
            ->map(fn($i) => ['quantity' => $i['received_quantity']])
            ->toArray();
        $svc = app(PurchaseOrderService::class);
        $svc->receive($purchaseOrder, $receivedItems, $request->user()->id);
        return response()->json($purchaseOrder->fresh()->load('supplier', 'items.item'));
    });
    Route::post('/purchase-orders/{purchaseOrder}/cancel', function (PurchaseOrder $purchaseOrder) {
        $svc = app(PurchaseOrderService::class);
        $svc->cancel($purchaseOrder);
        return response()->json($purchaseOrder->fresh());
    });

    // ── Production Orders ────────────────────────────────────────────────────
    Route::get('/production-orders', fn() => response()->json(ProductionOrder::latest()->paginate(50)));
    Route::post('/production-orders', function (Request $request) {
        $data = $request->validate([
            'wo_number'     => 'required|string|max:100|unique:production_orders,wo_number',
            'title'         => 'required|string|max:255',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'planned_start' => 'required|date',
            'planned_end'   => 'required|date|after_or_equal:planned_start',
            'description'   => 'nullable|string',
            'inputs'        => 'nullable|array',
            'inputs.*.item_id'  => 'required|exists:items,id',
            'inputs.*.quantity' => 'required|numeric|min:0.0001',
            'outputs'       => 'nullable|array',
            'outputs.*.item_id'  => 'required|exists:items,id',
            'outputs.*.quantity' => 'required|numeric|min:0.0001',
        ]);
        $svc = app(ProductionOrderService::class);
        $wo = $svc->create($data, $request->user());
        return response()->json($wo->load('inputs.item', 'outputs.item'), 201);
    });
    Route::get('/production-orders/{productionOrder}', fn(ProductionOrder $productionOrder) => response()->json($productionOrder->load('warehouse', 'inputs.item', 'outputs.item', 'user')));
    Route::post('/production-orders/{productionOrder}/start', function (Request $request, ProductionOrder $productionOrder) {
        $svc = app(ProductionOrderService::class);
        $svc->start($productionOrder, $request->user()->id);
        return response()->json($productionOrder->fresh()->load('inputs.item', 'outputs.item'));
    });
    Route::post('/production-orders/{productionOrder}/complete', function (Request $request, ProductionOrder $productionOrder) {
        $svc = app(ProductionOrderService::class);
        $svc->complete($productionOrder, $request->user()->id);
        return response()->json($productionOrder->fresh()->load('inputs.item', 'outputs.item'));
    });
    Route::post('/production-orders/{productionOrder}/cancel', function (ProductionOrder $productionOrder) {
        $svc = app(ProductionOrderService::class);
        $svc->cancel($productionOrder);
        return response()->json($productionOrder->fresh());
    });

    // ── Stock Opnames ────────────────────────────────────────────────────────
    Route::get('/stock-opnames', fn() => response()->json(StockOpname::with('warehouse')->latest()->paginate(50)));
    Route::post('/stock-opnames', function (Request $request) {
        $data = $request->validate([
            'opname_number' => 'required|string|max:50',
            'warehouse_id'  => 'required|exists:warehouses,id',
            'counted_at'    => 'required|date',
            'notes'         => 'nullable|string',
        ]);
        $data['reference_number'] = $data['opname_number'];
        unset($data['opname_number']);
        // Enforce uniqueness manually after rename
        if (\App\Models\StockOpname::where('reference_number', $data['reference_number'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['opname_number' => ['The opname number has already been taken.']]);
        }
        $svc = app(StockOpnameService::class);
        $op = $svc->create($data, $request->user());
        return response()->json($op, 201);
    });
    Route::get('/stock-opnames/{stockOpname}', fn(StockOpname $stockOpname) => response()->json($stockOpname->load('warehouse', 'items.item', 'user')));
    Route::post('/stock-opnames/{stockOpname}/load-stock', function (StockOpname $stockOpname) {
        $svc = app(StockOpnameService::class);
        $op = $svc->loadStock($stockOpname);
        return response()->json($op->load('items.item'));
    });
    Route::post('/stock-opnames/{stockOpname}/save-count', function (Request $request, StockOpname $stockOpname) {
        $request->validate(['counts' => 'required|array']);
        $svc = app(StockOpnameService::class);
        $op = $svc->saveCount($stockOpname, $request->counts);
        return response()->json($op->load('items.item'));
    });
    Route::post('/stock-opnames/{stockOpname}/complete', function (Request $request, StockOpname $stockOpname) {
        $svc = app(StockOpnameService::class);
        $svc->complete($stockOpname, $request->user()->id);
        return response()->json($stockOpname->fresh()->load('warehouse', 'items.item'));
    });
    Route::post('/stock-opnames/{stockOpname}/cancel', function (StockOpname $stockOpname) {
        $svc = app(StockOpnameService::class);
        $op = $svc->cancel($stockOpname);
        return response()->json($op);
    });

    // ── Reports ──────────────────────────────────────────────────────────────
    Route::get('/reports/stock-summary', function () {
        $items = Item::with('category', 'unit')
            ->withSum('stocks as total_qty', 'quantity')
            ->orderBy('name')
            ->get();
        return response()->json($items);
    });
    Route::get('/reports/low-stock', function () {
        $items = Item::with('category', 'unit')
            ->withSum('stocks as total_qty', 'quantity')
            ->whereNotNull('min_stock')
            ->havingRaw('COALESCE(total_qty, 0) < min_stock')
            ->get();
        return response()->json($items);
    });
    Route::get('/reports/movement-history', function (Request $request) {
        $q = StockMovement::with('item', 'user', 'toWarehouse', 'fromWarehouse');
        if ($request->type) $q->where('type', $request->type);
        if ($request->item_id) $q->where('item_id', $request->item_id);
        if ($request->date_from) $q->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to) $q->whereDate('created_at', '<=', $request->date_to);
        return response()->json($q->latest()->paginate(100));
    });

    // ── Users (admin only) ───────────────────────────────────────────────────
    Route::get('/users', function (Request $request) {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Forbidden'], 403);
        return response()->json(User::orderBy('name')->get());
    });
    Route::post('/users', function (Request $request) {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Forbidden'], 403);
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'role'      => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'is_active' => 'boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        return response()->json(User::create($data), 201);
    });
    Route::get('/users/{user}', function (Request $request, User $user) {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Forbidden'], 403);
        return response()->json($user);
    });
    Route::put('/users/{user}', function (Request $request, User $user) {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Forbidden'], 403);
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:8',
            'role'      => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'is_active' => 'boolean',
        ]);
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);

        if ($user->id === $request->user()->id && !($data['is_active'] ?? true)) {
            return response()->json(['message' => 'Cannot deactivate your own account'], 422);
        }

        $user->update($data);
        return response()->json($user);
    });
    Route::delete('/users/{user}', function (Request $request, User $user) {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Forbidden'], 403);
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete yourself'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'Deleted']);
    });
});
