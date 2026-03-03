<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // ════════════════════════════════════════════════════════════
        //  USERS
        // ════════════════════════════════════════════════════════════
        $admin  = User::create(['name' => 'Ahmad Fauzi',         'email' => 'admin@skbu.com',     'password' => Hash::make('password'), 'role' => 'admin',               'is_active' => true]);
        $inv    = User::create(['name' => 'Dewi Rahayu',         'email' => 'inventory@skbu.com', 'password' => Hash::make('password'), 'role' => 'inventory_controller', 'is_active' => true]);
        $whop   = User::create(['name' => 'Rudi Hartono',        'email' => 'gudang@skbu.com',    'password' => Hash::make('password'), 'role' => 'warehouse_operator',   'is_active' => true]);
        $spv    = User::create(['name' => 'Budi Santoso',        'email' => 'supervisor@skbu.com', 'password' => Hash::make('password'), 'role' => 'supervisor',           'is_active' => true]);
        $prod   = User::create(['name' => 'Slamet Widodo',       'email' => 'produksi@skbu.com',  'password' => Hash::make('password'), 'role' => 'production_staff',     'is_active' => true]);
        $viewer = User::create(['name' => 'Tamu / Auditor',      'email' => 'viewer@skbu.com',    'password' => Hash::make('password'), 'role' => 'viewer',               'is_active' => true]);

        // ════════════════════════════════════════════════════════════
        //  MASTER DATA
        // ════════════════════════════════════════════════════════════

        // Categories
        $catBB   = Category::create(['name' => 'Bahan Baku',          'description' => 'Bahan baku produksi']);
        $catWIP  = Category::create(['name' => 'Barang Setengah Jadi', 'description' => 'Work-in-process / barang setengah jadi']);
        $catFG   = Category::create(['name' => 'Barang Jadi',         'description' => 'Produk selesai siap jual']);
        $catSP   = Category::create(['name' => 'Suku Cadang',         'description' => 'Spare part & consumables mesin']);
        $catPack = Category::create(['name' => 'Kemasan',             'description' => 'Material kemasan & packaging']);

        // Units
        $kg   = Unit::create(['name' => 'Kilogram',  'abbreviation' => 'kg']);
        $pcs  = Unit::create(['name' => 'Pieces',    'abbreviation' => 'pcs']);
        $ltr  = Unit::create(['name' => 'Liter',     'abbreviation' => 'ltr']);
        $mtr  = Unit::create(['name' => 'Meter',     'abbreviation' => 'm']);
        $roll = Unit::create(['name' => 'Roll',      'abbreviation' => 'roll']);
        $set  = Unit::create(['name' => 'Set',       'abbreviation' => 'set']);
        $box  = Unit::create(['name' => 'Box',       'abbreviation' => 'box']);

        // Suppliers
        $sup1 = Supplier::create(['code' => 'SUP-001', 'name' => 'PT Indo Baja Steel',       'email' => 'sales@indobaja.co.id',       'phone' => '021-5554411', 'contact_person' => 'Hendra Wijaya',  'address' => 'Jl. Raya Bekasi KM 25, Bekasi Barat',       'is_active' => true]);
        $sup2 = Supplier::create(['code' => 'SUP-002', 'name' => 'CV Maju Cat Indonesia',    'email' => 'order@majucat.co.id',        'phone' => '024-7761200', 'contact_person' => 'Rina Kusuma',    'address' => 'Jl. Siliwangi No. 88, Semarang',            'is_active' => true]);
        $sup3 = Supplier::create(['code' => 'SUP-003', 'name' => 'UD Teknik Fastener',       'email' => 'pembelian@teknikfast.id',    'phone' => '031-8882200', 'contact_person' => 'Agus Purnomo',   'address' => 'Rungkut Industri Raya No. 12, Surabaya',    'is_active' => true]);
        $sup4 = Supplier::create(['code' => 'SUP-004', 'name' => 'PT Kemasan Prima Utama',   'email' => 'cs@kemasanprima.co.id',      'phone' => '021-5557788', 'contact_person' => 'Yanti Susanti',  'address' => 'Kawasan Industri Pulogadung Blok C No. 5',  'is_active' => true]);
        $sup5 = Supplier::create(['code' => 'SUP-005', 'name' => 'CV Abadi Logam Jaya',      'email' => 'info@abadilogam.id',         'phone' => '022-4201133', 'contact_person' => 'Dedi Kurniawan', 'address' => 'Jl. Industri No. 45, Bandung',              'is_active' => true]);

        // Warehouses & Locations
        $whBB = Warehouse::create(['code' => 'WH-BB',   'name' => 'Gudang Bahan Baku',   'address' => 'Area A, Plant 1 — PT Maju Jaya Metalindo', 'is_active' => true]);
        Location::create(['warehouse_id' => $whBB->id, 'code' => 'A-01', 'name' => 'Rak Besi & Profil']);
        Location::create(['warehouse_id' => $whBB->id, 'code' => 'A-02', 'name' => 'Rak Cat & Kimia']);
        Location::create(['warehouse_id' => $whBB->id, 'code' => 'A-03', 'name' => 'Rak Fastener & Suku Cadang']);

        $whProd = Warehouse::create(['code' => 'WH-PROD', 'name' => 'Gudang Produksi',    'address' => 'Area B, Plant 1 — PT Maju Jaya Metalindo', 'is_active' => true]);
        Location::create(['warehouse_id' => $whProd->id, 'code' => 'P-01', 'name' => 'Area Fabrikasi']);
        Location::create(['warehouse_id' => $whProd->id, 'code' => 'P-02', 'name' => 'Area Pengecatan']);

        $whBJ = Warehouse::create(['code' => 'WH-BJ',   'name' => 'Gudang Barang Jadi',  'address' => 'Area C, Plant 1 — PT Maju Jaya Metalindo', 'is_active' => true]);
        Location::create(['warehouse_id' => $whBJ->id, 'code' => 'C-01', 'name' => 'Area Penyimpanan Rak']);
        Location::create(['warehouse_id' => $whBJ->id, 'code' => 'C-02', 'name' => 'Area Staging Pengiriman']);

        // ════════════════════════════════════════════════════════════
        //  ITEMS
        // ════════════════════════════════════════════════════════════
        // Bahan Baku
        $i1  = Item::create(['code' => 'BB-001', 'name' => 'Besi Hollow 40x40x2mm',   'category_id' => $catBB->id,   'unit_id' => $mtr->id,  'description' => 'Besi hollow persegi 40x40mm tebal 2mm, panjang 6m/batang', 'min_stock' => 100, 'max_stock' => 1000, 'is_active' => true]);
        $i2  = Item::create(['code' => 'BB-002', 'name' => 'Besi Hollow 20x40x1.5mm', 'category_id' => $catBB->id,   'unit_id' => $mtr->id,  'description' => 'Besi hollow persegi panjang 20x40mm tebal 1.5mm',          'min_stock' => 80,  'max_stock' => 800,  'is_active' => true]);
        $i3  = Item::create(['code' => 'BB-003', 'name' => 'Pelat Besi 2mm',          'category_id' => $catBB->id,   'unit_id' => $kg->id,   'description' => 'Pelat besi tebal 2mm lebar 1.2m, estimasi 15.7 kg/m²',     'min_stock' => 200, 'max_stock' => 2000, 'is_active' => true]);
        $i4  = Item::create(['code' => 'BB-004', 'name' => 'Elektroda Las E6013 3.2', 'category_id' => $catBB->id,   'unit_id' => $kg->id,   'description' => 'Elektroda las rutile E6013 diameter 3.2mm',                'min_stock' => 20,  'max_stock' => 200,  'is_active' => true]);
        $i5  = Item::create(['code' => 'BB-005', 'name' => 'Cat Primer Abu',          'category_id' => $catBB->id,   'unit_id' => $ltr->id,  'description' => 'Cat primer anti-karat warna abu-abu',                      'min_stock' => 30,  'max_stock' => 300,  'is_active' => true]);
        $i6  = Item::create(['code' => 'BB-006', 'name' => 'Cat Finish Biru Tua',     'category_id' => $catBB->id,   'unit_id' => $ltr->id,  'description' => 'Cat powder coating finish warna biru tua RAL 5010',         'min_stock' => 20,  'max_stock' => 200,  'is_active' => true]);
        $i7  = Item::create(['code' => 'BB-007', 'name' => 'Thinner Industri',        'category_id' => $catBB->id,   'unit_id' => $ltr->id,  'description' => 'Thinner industri untuk pengenceran cat primer',             'min_stock' => 20,  'max_stock' => 150,  'is_active' => true]);
        // Suku Cadang
        $i8  = Item::create(['code' => 'SP-001', 'name' => 'Baut Hex M8x30',          'category_id' => $catSP->id,   'unit_id' => $pcs->id,  'description' => 'Baut hex galvanis M8 panjang 30mm + mur',                  'min_stock' => 500, 'max_stock' => 5000, 'is_active' => true]);
        $i9  = Item::create(['code' => 'SP-002', 'name' => 'Baut Hex M10x40',         'category_id' => $catSP->id,   'unit_id' => $pcs->id,  'description' => 'Baut hex galvanis M10 panjang 40mm + mur',                 'min_stock' => 300, 'max_stock' => 3000, 'is_active' => true]);
        $i10 = Item::create(['code' => 'SP-003', 'name' => 'Mata Gerinda Potong 14"', 'category_id' => $catSP->id,   'unit_id' => $pcs->id,  'description' => 'Mata gerinda potong besi 14 inch merk Krisbow',            'min_stock' => 20,  'max_stock' => 200,  'is_active' => true]);
        // Kemasan
        $i11 = Item::create(['code' => 'PK-001', 'name' => 'Bubble Wrap Roll 1m',     'category_id' => $catPack->id, 'unit_id' => $roll->id, 'description' => 'Bubble wrap packing lebar 1m per roll (±100m)',            'min_stock' => 5,   'max_stock' => 50,   'is_active' => true]);
        $i12 = Item::create(['code' => 'PK-002', 'name' => 'Strapping Band 15mm',     'category_id' => $catPack->id, 'unit_id' => $roll->id, 'description' => 'Strapping band plastik PP lebar 15mm per roll 2.5kg',      'min_stock' => 5,   'max_stock' => 50,   'is_active' => true]);
        // Barang Jadi
        $i13 = Item::create(['code' => 'BJ-001', 'name' => 'Rak Besi 5 Tingkat HD',   'category_id' => $catFG->id,   'unit_id' => $pcs->id,  'description' => 'Rak besi heavy duty 5 tingkat 200x60x180cm kapasitas 500kg/shelf', 'min_stock' => 5, 'max_stock' => 100, 'is_active' => true]);
        $i14 = Item::create(['code' => 'BJ-002', 'name' => 'Rak Besi 4 Tingkat STD',  'category_id' => $catFG->id,   'unit_id' => $pcs->id,  'description' => 'Rak besi standard 4 tingkat 150x50x160cm kapasitas 250kg/shelf',  'min_stock' => 5, 'max_stock' => 100, 'is_active' => true]);
        $i15 = Item::create(['code' => 'BJ-003', 'name' => 'Kabinet Loker 2 Pintu',   'category_id' => $catFG->id,   'unit_id' => $pcs->id,  'description' => 'Kabinet loker besi 2 pintu ukuran 90x45x180cm dengan kunci',      'min_stock' => 3, 'max_stock' => 50,  'is_active' => true]);
        $i16 = Item::create(['code' => 'BJ-004', 'name' => 'Meja Kerja Besi 1.2m',   'category_id' => $catFG->id,   'unit_id' => $pcs->id,  'description' => 'Meja kerja workshop besi 120x60x80cm dengan laci',               'min_stock' => 3, 'max_stock' => 30,  'is_active' => true]);

        // ════════════════════════════════════════════════════════════
        //  STOCK (current quantities — after all history below)
        // ════════════════════════════════════════════════════════════
        Stock::create(['item_id' => $i1->id,  'warehouse_id' => $whBB->id,   'quantity' => 382]);
        Stock::create(['item_id' => $i2->id,  'warehouse_id' => $whBB->id,   'quantity' => 245]);
        Stock::create(['item_id' => $i3->id,  'warehouse_id' => $whBB->id,   'quantity' => 186]);
        Stock::create(['item_id' => $i4->id,  'warehouse_id' => $whBB->id,   'quantity' => 34]);
        Stock::create(['item_id' => $i5->id,  'warehouse_id' => $whBB->id,   'quantity' => 58]);
        Stock::create(['item_id' => $i6->id,  'warehouse_id' => $whBB->id,   'quantity' => 41]);
        Stock::create(['item_id' => $i7->id,  'warehouse_id' => $whBB->id,   'quantity' => 26]);
        Stock::create(['item_id' => $i8->id,  'warehouse_id' => $whBB->id,   'quantity' => 1840]);
        Stock::create(['item_id' => $i9->id,  'warehouse_id' => $whBB->id,   'quantity' => 1220]);
        Stock::create(['item_id' => $i10->id, 'warehouse_id' => $whBB->id,   'quantity' => 14]);  // near min
        Stock::create(['item_id' => $i11->id, 'warehouse_id' => $whBJ->id,   'quantity' => 9]);
        Stock::create(['item_id' => $i12->id, 'warehouse_id' => $whBJ->id,   'quantity' => 7]);
        Stock::create(['item_id' => $i13->id, 'warehouse_id' => $whBJ->id,   'quantity' => 18]);
        Stock::create(['item_id' => $i14->id, 'warehouse_id' => $whBJ->id,   'quantity' => 13]);
        Stock::create(['item_id' => $i15->id, 'warehouse_id' => $whBJ->id,   'quantity' => 4]);   // BELOW MIN
        Stock::create(['item_id' => $i16->id, 'warehouse_id' => $whBJ->id,   'quantity' => 2]);   // BELOW MIN
        // Transfer ke gudang produksi (bahan baku duplikat untuk WO aktif)
        Stock::create(['item_id' => $i1->id,  'warehouse_id' => $whProd->id, 'quantity' => 18]);
        Stock::create(['item_id' => $i2->id,  'warehouse_id' => $whProd->id, 'quantity' => 12]);

        // ════════════════════════════════════════════════════════════
        //  PURCHASE ORDERS
        // ════════════════════════════════════════════════════════════
        //  PO-1: Received (2 months ago) — besi hollow dari SUP-001
        $po1 = PurchaseOrder::create([
            'po_number'    => 'PO-202512-0001',
            'supplier_id'  => $sup1->id,
            'warehouse_id' => $whBB->id,
            'status'       => 'received',
            'order_date'   => Carbon::now()->subMonths(2)->startOfMonth()->toDateString(),
            'expected_date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(14)->toDateString(),
            'notes'        => 'Pembelian rutin besi hollow Q4',
            'approved_by'  => $spv->id,
            'approved_at'  => Carbon::now()->subMonths(2)->startOfMonth()->addDay(),
            'user_id'      => $inv->id,
        ]);
        PurchaseOrderItem::create(['purchase_order_id' => $po1->id, 'item_id' => $i1->id, 'quantity_ordered' => 300, 'quantity_received' => 300, 'unit_price' => 45000]);
        PurchaseOrderItem::create(['purchase_order_id' => $po1->id, 'item_id' => $i2->id, 'quantity_ordered' => 200, 'quantity_received' => 200, 'unit_price' => 38000]);
        PurchaseOrderItem::create(['purchase_order_id' => $po1->id, 'item_id' => $i3->id, 'quantity_ordered' => 250, 'quantity_received' => 250, 'unit_price' => 18500]);

        //  PO-2: Received (6 weeks ago) — cat & thinner dari SUP-002
        $po2 = PurchaseOrder::create([
            'po_number'    => 'PO-202512-0002',
            'supplier_id'  => $sup2->id,
            'warehouse_id' => $whBB->id,
            'status'       => 'received',
            'order_date'   => Carbon::now()->subWeeks(7)->toDateString(),
            'expected_date' => Carbon::now()->subWeeks(6)->toDateString(),
            'notes'        => 'Restok cat primer dan thinner',
            'approved_by'  => $spv->id,
            'approved_at'  => Carbon::now()->subWeeks(7)->addDay(),
            'user_id'      => $inv->id,
        ]);
        PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'item_id' => $i5->id, 'quantity_ordered' => 100, 'quantity_received' => 100, 'unit_price' => 52000]);
        PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'item_id' => $i6->id, 'quantity_ordered' => 80,  'quantity_received' => 80,  'unit_price' => 65000]);
        PurchaseOrderItem::create(['purchase_order_id' => $po2->id, 'item_id' => $i7->id, 'quantity_ordered' => 60,  'quantity_received' => 60,  'unit_price' => 28000]);

        //  PO-3: Received (5 weeks ago) — fastener & consumables dari SUP-003
        $po3 = PurchaseOrder::create([
            'po_number'    => 'PO-202601-0001',
            'supplier_id'  => $sup3->id,
            'warehouse_id' => $whBB->id,
            'status'       => 'received',
            'order_date'   => Carbon::now()->subWeeks(6)->toDateString(),
            'expected_date' => Carbon::now()->subWeeks(5)->toDateString(),
            'notes'        => 'Baut, mur, dan mata gerinda',
            'approved_by'  => $spv->id,
            'approved_at'  => Carbon::now()->subWeeks(6)->addDay(),
            'user_id'      => $inv->id,
        ]);
        PurchaseOrderItem::create(['purchase_order_id' => $po3->id, 'item_id' => $i8->id,  'quantity_ordered' => 2000, 'quantity_received' => 2000, 'unit_price' => 850]);
        PurchaseOrderItem::create(['purchase_order_id' => $po3->id, 'item_id' => $i9->id,  'quantity_ordered' => 1500, 'quantity_received' => 1500, 'unit_price' => 1200]);
        PurchaseOrderItem::create(['purchase_order_id' => $po3->id, 'item_id' => $i10->id, 'quantity_ordered' => 50,   'quantity_received' => 50,   'unit_price' => 35000]);

        //  PO-4: Partially Received (2 weeks ago) — elektroda las dari SUP-005
        $po4 = PurchaseOrder::create([
            'po_number'    => 'PO-202601-0002',
            'supplier_id'  => $sup5->id,
            'warehouse_id' => $whBB->id,
            'status'       => 'partially_received',
            'order_date'   => Carbon::now()->subWeeks(3)->toDateString(),
            'expected_date' => Carbon::now()->subWeeks(1)->toDateString(),
            'notes'        => 'Elektroda las & pelat besi — pengiriman 2 tahap',
            'approved_by'  => $spv->id,
            'approved_at'  => Carbon::now()->subWeeks(3)->addDay(),
            'user_id'      => $inv->id,
        ]);
        PurchaseOrderItem::create(['purchase_order_id' => $po4->id, 'item_id' => $i4->id, 'quantity_ordered' => 100, 'quantity_received' => 60,  'unit_price' => 32000]);
        PurchaseOrderItem::create(['purchase_order_id' => $po4->id, 'item_id' => $i3->id, 'quantity_ordered' => 300, 'quantity_received' => 150, 'unit_price' => 18500]);

        //  PO-5: Approved — menunggu realisasi pengiriman (kemasan dari SUP-004)
        $po5 = PurchaseOrder::create([
            'po_number'    => 'PO-202602-0001',
            'supplier_id'  => $sup4->id,
            'warehouse_id' => $whBJ->id,
            'status'       => 'approved',
            'order_date'   => Carbon::now()->subDays(5)->toDateString(),
            'expected_date' => Carbon::now()->addDays(7)->toDateString(),
            'notes'        => 'Restok bubble wrap dan strapping band',
            'approved_by'  => $spv->id,
            'approved_at'  => Carbon::now()->subDays(4),
            'user_id'      => $inv->id,
        ]);
        PurchaseOrderItem::create(['purchase_order_id' => $po5->id, 'item_id' => $i11->id, 'quantity_ordered' => 20, 'quantity_received' => 0, 'unit_price' => 75000]);
        PurchaseOrderItem::create(['purchase_order_id' => $po5->id, 'item_id' => $i12->id, 'quantity_ordered' => 15, 'quantity_received' => 0, 'unit_price' => 55000]);

        //  PO-6: Draft — diajukan hari ini (besi hollow restock dari SUP-001)
        $po6 = PurchaseOrder::create([
            'po_number'    => 'PO-202602-0002',
            'supplier_id'  => $sup1->id,
            'warehouse_id' => $whBB->id,
            'status'       => 'draft',
            'order_date'   => Carbon::now()->toDateString(),
            'expected_date' => Carbon::now()->addDays(14)->toDateString(),
            'notes'        => 'Pengadaan besi hollow untuk Q1 - perlu approval supervisor',
            'user_id'      => $inv->id,
        ]);
        PurchaseOrderItem::create(['purchase_order_id' => $po6->id, 'item_id' => $i1->id, 'quantity_ordered' => 400, 'quantity_received' => 0, 'unit_price' => 44500]);
        PurchaseOrderItem::create(['purchase_order_id' => $po6->id, 'item_id' => $i2->id, 'quantity_ordered' => 250, 'quantity_received' => 0, 'unit_price' => 37500]);

        // ════════════════════════════════════════════════════════════
        //  PRODUCTION ORDERS
        // ════════════════════════════════════════════════════════════
        //  WO-1: Completed (6 weeks ago) — produksi rak 5 tingkat HD
        $wo1 = ProductionOrder::create([
            'wo_number'    => 'WO-202512-0001',
            'title'        => 'Produksi Rak Besi 5T HD — Batch 1 Q1',
            'description'  => 'Order batch 15 unit rak heavy duty untuk PT Sukses Abadi',
            'warehouse_id' => $whProd->id,
            'status'       => 'completed',
            'planned_start' => Carbon::now()->subWeeks(7)->toDateString(),
            'planned_end'  => Carbon::now()->subWeeks(6)->toDateString(),
            'actual_start' => Carbon::now()->subWeeks(7)->toDateString(),
            'actual_end'   => Carbon::now()->subWeeks(6)->addDay()->toDateString(),
            'user_id'      => $prod->id,
        ]);
        ProductionOrderItem::create(['production_order_id' => $wo1->id, 'item_id' => $i1->id, 'type' => 'input',  'quantity' => 90]);
        ProductionOrderItem::create(['production_order_id' => $wo1->id, 'item_id' => $i2->id, 'type' => 'input',  'quantity' => 45]);
        ProductionOrderItem::create(['production_order_id' => $wo1->id, 'item_id' => $i5->id, 'type' => 'input',  'quantity' => 30]);
        ProductionOrderItem::create(['production_order_id' => $wo1->id, 'item_id' => $i6->id, 'type' => 'input',  'quantity' => 20]);
        ProductionOrderItem::create(['production_order_id' => $wo1->id, 'item_id' => $i8->id, 'type' => 'input',  'quantity' => 120]);
        ProductionOrderItem::create(['production_order_id' => $wo1->id, 'item_id' => $i13->id, 'type' => 'output', 'quantity' => 15]);

        //  WO-2: Completed (3 weeks ago) — produksi rak 4 tingkat STD
        $wo2 = ProductionOrder::create([
            'wo_number'    => 'WO-202601-0001',
            'title'        => 'Produksi Rak Besi 4T STD — Batch 1 Februari',
            'description'  => 'Stock reguler rak standar untuk stok gudang',
            'warehouse_id' => $whProd->id,
            'status'       => 'completed',
            'planned_start' => Carbon::now()->subWeeks(4)->toDateString(),
            'planned_end'  => Carbon::now()->subWeeks(3)->toDateString(),
            'actual_start' => Carbon::now()->subWeeks(4)->toDateString(),
            'actual_end'   => Carbon::now()->subWeeks(3)->toDateString(),
            'user_id'      => $prod->id,
        ]);
        ProductionOrderItem::create(['production_order_id' => $wo2->id, 'item_id' => $i1->id, 'type' => 'input',  'quantity' => 60]);
        ProductionOrderItem::create(['production_order_id' => $wo2->id, 'item_id' => $i2->id, 'type' => 'input',  'quantity' => 40]);
        ProductionOrderItem::create(['production_order_id' => $wo2->id, 'item_id' => $i5->id, 'type' => 'input',  'quantity' => 20]);
        ProductionOrderItem::create(['production_order_id' => $wo2->id, 'item_id' => $i6->id, 'type' => 'input',  'quantity' => 15]);
        ProductionOrderItem::create(['production_order_id' => $wo2->id, 'item_id' => $i8->id, 'type' => 'input',  'quantity' => 80]);
        ProductionOrderItem::create(['production_order_id' => $wo2->id, 'item_id' => $i14->id, 'type' => 'output', 'quantity' => 20]);

        //  WO-3: In Progress (1 week ago) — produksi kabinet loker
        $wo3 = ProductionOrder::create([
            'wo_number'    => 'WO-202602-0001',
            'title'        => 'Produksi Kabinet Loker 2 Pintu — Batch Februari',
            'description'  => 'Order 10 unit kabinet loker untuk proyek pabrik di Karawang',
            'warehouse_id' => $whProd->id,
            'status'       => 'in_progress',
            'planned_start' => Carbon::now()->subWeeks(1)->toDateString(),
            'planned_end'  => Carbon::now()->addWeeks(1)->toDateString(),
            'actual_start' => Carbon::now()->subWeeks(1)->toDateString(),
            'actual_end'   => null,
            'user_id'      => $prod->id,
        ]);
        ProductionOrderItem::create(['production_order_id' => $wo3->id, 'item_id' => $i1->id,  'type' => 'input',  'quantity' => 50]);
        ProductionOrderItem::create(['production_order_id' => $wo3->id, 'item_id' => $i3->id,  'type' => 'input',  'quantity' => 80]);
        ProductionOrderItem::create(['production_order_id' => $wo3->id, 'item_id' => $i5->id,  'type' => 'input',  'quantity' => 15]);
        ProductionOrderItem::create(['production_order_id' => $wo3->id, 'item_id' => $i9->id,  'type' => 'input',  'quantity' => 100]);
        ProductionOrderItem::create(['production_order_id' => $wo3->id, 'item_id' => $i15->id, 'type' => 'output', 'quantity' => 10]);

        //  WO-4: Draft — direncanakan minggu depan (meja kerja)
        $wo4 = ProductionOrder::create([
            'wo_number'    => 'WO-202602-0002',
            'title'        => 'Produksi Meja Kerja Besi 1.2m — Pilot Run',
            'description'  => 'Pilot run produksi meja kerja model baru, target 5 unit untuk evaluasi',
            'warehouse_id' => $whProd->id,
            'status'       => 'draft',
            'planned_start' => Carbon::now()->addDays(3)->toDateString(),
            'planned_end'  => Carbon::now()->addDays(10)->toDateString(),
            'actual_start' => null,
            'actual_end'   => null,
            'user_id'      => $prod->id,
        ]);
        ProductionOrderItem::create(['production_order_id' => $wo4->id, 'item_id' => $i1->id,  'type' => 'input',  'quantity' => 25]);
        ProductionOrderItem::create(['production_order_id' => $wo4->id, 'item_id' => $i2->id,  'type' => 'input',  'quantity' => 15]);
        ProductionOrderItem::create(['production_order_id' => $wo4->id, 'item_id' => $i3->id,  'type' => 'input',  'quantity' => 40]);
        ProductionOrderItem::create(['production_order_id' => $wo4->id, 'item_id' => $i8->id,  'type' => 'input',  'quantity' => 40]);
        ProductionOrderItem::create(['production_order_id' => $wo4->id, 'item_id' => $i16->id, 'type' => 'output', 'quantity' => 5]);

        // ════════════════════════════════════════════════════════════
        //  STOCK MOVEMENTS (history — realistic sequence)
        // ════════════════════════════════════════════════════════════
        $smSeq = 1;
        $smRef = function () use (&$smSeq) {
            return 'SM-' . Carbon::now()->format('Ym') . '-' . str_pad($smSeq++, 4, '0', STR_PAD_LEFT);
        };

        // --- Goods receipts from PO-1 (besi hollow) ---
        $d = Carbon::now()->subMonths(2)->startOfMonth()->addDays(15);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i1->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 300, 'quantity_before' => 82,  'quantity_after' => 382, 'reference_document' => 'PO-202512-0001', 'notes' => 'Penerimaan besi hollow 40x40 dari PT Indo Baja Steel', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i2->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 200, 'quantity_before' => 45,  'quantity_after' => 245, 'reference_document' => 'PO-202512-0001', 'notes' => 'Penerimaan besi hollow 20x40', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i3->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 250, 'quantity_before' => 86,  'quantity_after' => 336, 'reference_document' => 'PO-202512-0001', 'notes' => 'Penerimaan pelat besi 2mm', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Goods receipts from PO-2 (cat & thinner) ---
        $d = Carbon::now()->subWeeks(6);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i5->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 100, 'quantity_before' => 8,   'quantity_after' => 108, 'reference_document' => 'PO-202512-0002', 'notes' => 'Penerimaan cat primer abu-abu', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i6->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 80,  'quantity_before' => 11,  'quantity_after' => 91,  'reference_document' => 'PO-202512-0002', 'notes' => 'Penerimaan cat finish biru tua', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i7->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 60,  'quantity_before' => 6,   'quantity_after' => 66,  'reference_document' => 'PO-202512-0002', 'notes' => 'Penerimaan thinner industri', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Goods receipts from PO-3 (fastener) ---
        $d = Carbon::now()->subWeeks(5);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i8->id,  'to_warehouse_id' => $whBB->id, 'quantity' => 2000, 'quantity_before' => 40,  'quantity_after' => 2040, 'reference_document' => 'PO-202601-0001', 'notes' => 'Penerimaan baut M8x30', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i9->id,  'to_warehouse_id' => $whBB->id, 'quantity' => 1500, 'quantity_before' => 20,  'quantity_after' => 1520, 'reference_document' => 'PO-202601-0001', 'notes' => 'Penerimaan baut M10x40', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i10->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 50,   'quantity_before' => 0,   'quantity_after' => 50,   'reference_document' => 'PO-202601-0001', 'notes' => 'Penerimaan mata gerinda potong', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Material issues for WO-1 (rak 5 tingkat HD) ---
        $d = Carbon::now()->subWeeks(7);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i1->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 90,  'quantity_before' => 382, 'quantity_after' => 292, 'reference_document' => 'WO-202512-0001', 'notes' => 'Pengeluaran besi hollow 40x40 - WO rak HD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i2->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 45,  'quantity_before' => 245, 'quantity_after' => 200, 'reference_document' => 'WO-202512-0001', 'notes' => 'Pengeluaran besi hollow 20x40 - WO rak HD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i5->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 30,  'quantity_before' => 108, 'quantity_after' => 78,  'reference_document' => 'WO-202512-0001', 'notes' => 'Pengeluaran cat primer - WO rak HD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i6->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 20,  'quantity_before' => 91,  'quantity_after' => 71,  'reference_document' => 'WO-202512-0001', 'notes' => 'Pengeluaran cat finish - WO rak HD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i8->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 120, 'quantity_before' => 2040, 'quantity_after' => 1920, 'reference_document' => 'WO-202512-0001', 'notes' => 'Pengeluaran baut M8 - WO rak HD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Production output WO-1 ---
        $d = Carbon::now()->subWeeks(6)->addDay();
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'production_output', 'item_id' => $i13->id, 'to_warehouse_id' => $whBJ->id, 'quantity' => 15, 'quantity_before' => 6, 'quantity_after' => 21, 'reference_document' => 'WO-202512-0001', 'notes' => 'Output 15 unit rak 5 tingkat HD selesai', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Material issues for WO-2 (rak 4 tingkat STD) ---
        $d = Carbon::now()->subWeeks(4);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i1->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 60,  'quantity_before' => 292, 'quantity_after' => 232, 'reference_document' => 'WO-202601-0001', 'notes' => 'Pengeluaran besi hollow 40x40 - rak STD batch Feb', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i2->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 40,  'quantity_before' => 200, 'quantity_after' => 160, 'reference_document' => 'WO-202601-0001', 'notes' => 'Pengeluaran besi hollow 20x40 - rak STD batch Feb', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i5->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 20,  'quantity_before' => 78,  'quantity_after' => 58,  'reference_document' => 'WO-202601-0001', 'notes' => 'Cat primer - rak STD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i8->id, 'from_warehouse_id' => $whBB->id, 'quantity' => 80,  'quantity_before' => 1920, 'quantity_after' => 1840, 'reference_document' => 'WO-202601-0001', 'notes' => 'Baut M8 - rak STD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Production output WO-2 ---
        $d = Carbon::now()->subWeeks(3);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'production_output', 'item_id' => $i14->id, 'to_warehouse_id' => $whBJ->id, 'quantity' => 20, 'quantity_before' => 3, 'quantity_after' => 23, 'reference_document' => 'WO-202601-0001', 'notes' => 'Output 20 unit rak 4 tingkat STD', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Goods receipt partial from PO-4 ---
        $d = Carbon::now()->subWeeks(2);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i4->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 60,  'quantity_before' => 4,   'quantity_after' => 64,  'reference_document' => 'PO-202601-0002', 'notes' => 'Penerimaan tahap 1 — elektroda las (sisa 40kg menyusul)', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'goods_receipt', 'item_id' => $i3->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 150, 'quantity_before' => 336, 'quantity_after' => 486, 'reference_document' => 'PO-202601-0002', 'notes' => 'Penerimaan tahap 1 — pelat besi (sisa 150kg menyusul)', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Material issues for WO-3 (kabinet loker, in progress) ---
        $d = Carbon::now()->subWeeks(1);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i1->id,  'from_warehouse_id' => $whBB->id, 'quantity' => 50,  'quantity_before' => 232, 'quantity_after' => 182, 'reference_document' => 'WO-202602-0001', 'notes' => 'Pengeluaran besi hollow - kabinet loker', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i3->id,  'from_warehouse_id' => $whBB->id, 'quantity' => 80,  'quantity_before' => 486, 'quantity_after' => 406, 'reference_document' => 'WO-202602-0001', 'notes' => 'Pengeluaran pelat besi 2mm - kabinet loker', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i5->id,  'from_warehouse_id' => $whBB->id, 'quantity' => 15,  'quantity_before' => 58,  'quantity_after' => 43,  'reference_document' => 'WO-202602-0001', 'notes' => 'Cat primer - kabinet loker', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'material_issue', 'item_id' => $i9->id,  'from_warehouse_id' => $whBB->id, 'quantity' => 100, 'quantity_before' => 1520, 'quantity_after' => 1420, 'reference_document' => 'WO-202602-0001', 'notes' => 'Baut M10 - kabinet loker', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Sales dispatches (outbound) ---
        $d = Carbon::now()->subWeeks(5)->addDays(2);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'sales_dispatch', 'item_id' => $i13->id, 'from_warehouse_id' => $whBJ->id, 'quantity' => 5, 'quantity_before' => 21, 'quantity_after' => 16, 'reference_document' => 'SO-2025120045', 'notes' => 'Pengiriman 5 unit rak HD ke PT Maju Prakarsa', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        $d = Carbon::now()->subWeeks(3)->addDays(3);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'sales_dispatch', 'item_id' => $i14->id, 'from_warehouse_id' => $whBJ->id, 'quantity' => 10, 'quantity_before' => 23, 'quantity_after' => 13, 'reference_document' => 'SO-2026010012', 'notes' => 'Pengiriman 10 unit rak STD ke CV Kencana Jaya', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        $d = Carbon::now()->subDays(10);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'sales_dispatch', 'item_id' => $i13->id, 'from_warehouse_id' => $whBJ->id, 'quantity' => 3, 'quantity_before' => 16, 'quantity_after' => 13, 'reference_document' => 'SO-2026020003', 'notes' => 'Pengiriman 3 unit rak HD ke Toko Alat Industri Mandiri', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        $d = Carbon::now()->subDays(8);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'sales_dispatch', 'item_id' => $i14->id, 'from_warehouse_id' => $whBJ->id, 'quantity' => 4, 'quantity_before' => 13, 'quantity_after' => 9,  'reference_document' => 'SO-2026020008', 'notes' => 'Pengiriman rak STD ke CV Bangun Sentosa', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);
        $d = Carbon::now()->subDays(4);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'sales_dispatch', 'item_id' => $i13->id, 'from_warehouse_id' => $whBJ->id, 'quantity' => 5, 'quantity_before' => 13, 'quantity_after' => 8,  'reference_document' => 'SO-2026020019', 'notes' => 'Pengiriman rak HD ke proyek gudang PT Surya Logistik', 'user_id' => $whop->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Stock transfer WH-BB → WH-PROD (staging WO-3) ---
        $d = Carbon::now()->subWeeks(1)->addHours(2);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'stock_transfer', 'item_id' => $i1->id, 'from_warehouse_id' => $whBB->id, 'to_warehouse_id' => $whProd->id, 'quantity' => 18, 'quantity_before' => 200, 'quantity_after' => 182, 'reference_document' => 'WO-202602-0001', 'notes' => 'Transfer stok ke area produksi untuk WO-3', 'user_id' => $prod->id, 'created_at' => $d, 'updated_at' => $d]);

        // --- Stock adjustments ---
        $d = Carbon::now()->subDays(6);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'stock_adjustment', 'item_id' => $i10->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 36, 'quantity_before' => 50, 'quantity_after' => 14, 'notes' => 'Koreksi stok mata gerinda — 36 pcs terpakai operasional mesin tidak tercatat', 'user_id' => $inv->id, 'created_at' => $d, 'updated_at' => $d]);
        $d = Carbon::now()->subDays(3);
        StockMovement::create(['reference_number' => $smRef(), 'type' => 'stock_adjustment', 'item_id' => $i7->id, 'to_warehouse_id' => $whBB->id, 'quantity' => 40, 'quantity_before' => 66, 'quantity_after' => 26, 'notes' => 'Koreksi stok thinner — penyusutan & terpakai tanpa pencatatan', 'user_id' => $inv->id, 'created_at' => $d, 'updated_at' => $d]);

        // ════════════════════════════════════════════════════════════
        //  STOCK OPNAMES
        // ════════════════════════════════════════════════════════════
        //  Opname-1: Completed (end of January) — WH-BB
        $op1 = StockOpname::create([
            'reference_number' => 'SO-202501-0001',
            'warehouse_id'     => $whBB->id,
            'status'           => 'completed',
            'counted_at'       => Carbon::now()->subMonth()->endOfMonth()->toDateString(),
            'notes'            => 'Opname rutin akhir bulan Januari — gudang bahan baku',
            'user_id'          => $inv->id,
        ]);
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i1->id, 'system_quantity' => 292, 'physical_quantity' => 292, 'discrepancy' => 0]);
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i2->id, 'system_quantity' => 200, 'physical_quantity' => 198, 'discrepancy' => -2,  'notes' => '2m besi hollow patah, dibuang']); // slight diff
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i3->id, 'system_quantity' => 336, 'physical_quantity' => 340, 'discrepancy' => 4,   'notes' => '4kg lebih dari sistem — kemungkinan timbangan awal tidak dikalibrasi']);
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i5->id, 'system_quantity' => 78,  'physical_quantity' => 78,  'discrepancy' => 0]);
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i6->id, 'system_quantity' => 71,  'physical_quantity' => 70,  'discrepancy' => -1,  'notes' => '1 liter cat tumpah saat proses transfer']);
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i8->id, 'system_quantity' => 1920, 'physical_quantity' => 1920, 'discrepancy' => 0]);
        StockOpnameItem::create(['stock_opname_id' => $op1->id, 'item_id' => $i9->id, 'system_quantity' => 1520, 'physical_quantity' => 1520, 'discrepancy' => 0]);

        //  Opname-2: In Progress (this week) — WH-BJ
        $op2 = StockOpname::create([
            'reference_number' => 'SO-202602-0001',
            'warehouse_id'     => $whBJ->id,
            'status'           => 'in_progress',
            'counted_at'       => Carbon::now()->toDateString(),
            'notes'            => 'Opname barang jadi — pengecekan sebelum akhir bulan Februari',
            'user_id'          => $inv->id,
        ]);
        StockOpnameItem::create(['stock_opname_id' => $op2->id, 'item_id' => $i13->id, 'system_quantity' => 8,  'physical_quantity' => 8,  'discrepancy' => 0]);
        StockOpnameItem::create(['stock_opname_id' => $op2->id, 'item_id' => $i14->id, 'system_quantity' => 9,  'physical_quantity' => 9,  'discrepancy' => 0]);
        StockOpnameItem::create(['stock_opname_id' => $op2->id, 'item_id' => $i15->id, 'system_quantity' => 4,  'physical_quantity' => 4,  'discrepancy' => 0,  'notes' => 'Hitungan fisik belum diverifikasi']);
        StockOpnameItem::create(['stock_opname_id' => $op2->id, 'item_id' => $i16->id, 'system_quantity' => 2,  'physical_quantity' => 2,  'discrepancy' => 0,  'notes' => 'Hitungan fisik belum diverifikasi']);
        StockOpnameItem::create(['stock_opname_id' => $op2->id, 'item_id' => $i11->id, 'system_quantity' => 9,  'physical_quantity' => 9,  'discrepancy' => 0]);
        StockOpnameItem::create(['stock_opname_id' => $op2->id, 'item_id' => $i12->id, 'system_quantity' => 7,  'physical_quantity' => 7,  'discrepancy' => 0]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
