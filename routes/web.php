<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));


Route::middleware(['auth', 'role'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('warehouses', WarehouseController::class);
    Route::resource('items', ItemController::class);

    // Stock Movements
    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    Route::get('/stock-movements/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
    Route::post('/stock-movements', [StockMovementController::class, 'store'])->name('stock-movements.store');
    Route::get('/stock-movements/{stockMovement}', [StockMovementController::class, 'show'])->name('stock-movements.show');

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('purchase-orders.receive-form');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

    // Production Orders (Work Orders)
    Route::resource('production-orders', ProductionOrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/production-orders/{productionOrder}/start', [ProductionOrderController::class, 'start'])->name('production-orders.start');
    Route::post('/production-orders/{productionOrder}/complete', [ProductionOrderController::class, 'complete'])->name('production-orders.complete');
    Route::post('/production-orders/{productionOrder}/cancel', [ProductionOrderController::class, 'cancel'])->name('production-orders.cancel');

    // Stock Opname
    Route::resource('stock-opnames', StockOpnameController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/stock-opnames/{stockOpname}/load-stock', [StockOpnameController::class, 'loadStock'])->name('stock-opnames.load-stock');
    Route::post('/stock-opnames/{stockOpname}/save-count', [StockOpnameController::class, 'saveCount'])->name('stock-opnames.save-count');
    Route::post('/stock-opnames/{stockOpname}/complete', [StockOpnameController::class, 'complete'])->name('stock-opnames.complete');
    Route::post('/stock-opnames/{stockOpname}/cancel', [StockOpnameController::class, 'cancel'])->name('stock-opnames.cancel');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/stock-summary', [ReportController::class, 'stockSummary'])->name('stock-summary');
        Route::get('/low-stock', [ReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/movement-history', [ReportController::class, 'movementHistory'])->name('movement-history');
        // Export reports — C-02: rate limited 10 requests/menit untuk cegah scraping
        Route::middleware('throttle:10,1')->group(function () {
            Route::get('/stock-summary/export-csv', [ReportController::class, 'stockSummaryCsv'])->name('stock-summary.csv');
            Route::get('/low-stock/export-csv', [ReportController::class, 'lowStockCsv'])->name('low-stock.csv');
            Route::get('/movement-history/export-csv', [ReportController::class, 'movementHistoryCsv'])->name('movement-history.csv');
            Route::get('/stock-summary/export-pdf', [ReportController::class, 'stockSummaryPdf'])->name('stock-summary.pdf');
            Route::get('/low-stock/export-pdf', [ReportController::class, 'lowStockPdf'])->name('low-stock.pdf');
            Route::get('/movement-history/export-pdf', [ReportController::class, 'movementHistoryPdf'])->name('movement-history.pdf');
        });
    });

    // User Management (Admin only)
    Route::middleware('role:admin')->resource('users', UserController::class)->except('show');
});

require __DIR__ . '/auth.php';
