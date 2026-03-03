# TestSprite AI Testing Report (MCP) — Backend API

---

## 1️⃣ Document Metadata

| Field            | Value                                           |
| ---------------- | ----------------------------------------------- |
| **Project Name** | manajemen-inventori                             |
| **Test Type**    | Backend REST API                                |
| **Date**         | 2026-02-27                                      |
| **Prepared by**  | TestSprite AI Team (Manual pytest suite)        |
| **Server**       | http://127.0.0.1:8000                           |
| **Auth Method**  | Bearer Token (Laravel Sanctum via `/api/login`) |
| **Test Admin**   | admin@mjmetal.co.id                             |
| **Test Files**   | testsprite_tests/manual_tests/                  |

---

## 2️⃣ Executive Summary

| Metric      | Value        |
| ----------- | ------------ |
| Total Tests | **68**       |
| ✅ Passed   | **68**       |
| ❌ Failed   | **0**        |
| Pass Rate   | **100%**     |
| Duration    | ~54 seconds  |
| Runner      | pytest 9.0.2 |

---

## 3️⃣ Bugs Found & Fixed During Test Development

The following backend bugs were discovered and fixed while writing/running the test suite:

| #   | Bug                                                                                          | Fix Applied                                                                        |
| --- | -------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| 1   | `StockService::recordMovement()` missing — route called non-existent method                  | Added `recordMovement(array $data)` dispatch method to `StockService`              |
| 2   | `PurchaseOrderService::create()` missing — PO create route returned 500                      | Added `create(array $data, $user)` method to `PurchaseOrderService`                |
| 3   | `ProductionOrderService::create()` missing — WO create route returned 500                    | Added `create(array $data, $user)` method to `ProductionOrderService`              |
| 4   | `StockOpnameService` missing `create()`, `loadStock()`, `saveCount()`, `cancel()`            | Added all four missing methods; added `loadStock` and `saveCount` as clean aliases |
| 5   | `StockOpnameService::savePhysicalCount()` keyed by opname_item.id, not item_id               | Changed lookup to `where('item_id', $itemId)`                                      |
| 6   | Route `GET /suppliers/{supplier}` used `fn(Supplier $s)` — name mismatch broke model binding | Fixed to `fn(Supplier $supplier)`                                                  |
| 7   | Route `GET /warehouses/{warehouse}` used `fn(Warehouse $w)` — same name mismatch             | Fixed to `fn(Warehouse $warehouse)`                                                |
| 8   | PO/WO/Opname routes passed `$request->user()` (User object) as `int $userId`                 | Fixed all to `$request->user()->id`                                                |
| 9   | PO/WO/Opname `approve/start/complete/cancel` routes assigned void return to `$po/$wo/$op`    | Fixed routes to call service void methods then `fresh()` reload the model          |
| 10  | PO receive route sent raw `$request->items` in wrong format to service                       | Fixed to `keyBy('purchase_order_item_id')->map(fn→received_quantity)`              |
| 11  | Stock Opname create route used `unique:stock_opnames,opname_number` (column doesn't exist)   | Fixed to map `opname_number` → `reference_number` and check uniqueness manually    |
| 12  | GET detail routes loaded `'creator'` relationship not defined on models (500 error)          | Changed to `'user'` relationship (the actual relationship name on all models)      |
| 13  | Test requests missing `Accept: application/json` header — 422s redirected to 200             | Added `Accept: application/json` to all test headers in conftest.py                |

---

## 4️⃣ Test Coverage by Module

### Auth (TC001–TC004)

| ID    | Test                                        | Status |
| ----- | ------------------------------------------- | ------ |
| TC001 | POST /login with valid credentials          | ✅     |
| TC002 | POST /login with wrong password → 422       | ✅     |
| TC003 | GET /me with valid Bearer token → user data | ✅     |
| TC004 | GET /categories without token → 401         | ✅     |

### Categories (TC005–TC010)

| ID    | Test                                       | Status |
| ----- | ------------------------------------------ | ------ |
| TC005 | GET /categories → list                     | ✅     |
| TC006 | POST /categories → 201 + created object    | ✅     |
| TC007 | PUT /categories/{id} → updated             | ✅     |
| TC008 | DELETE /categories/{id} → 200              | ✅     |
| TC009 | POST duplicate name → 422 validation error | ✅     |
| TC010 | POST missing name → 422 validation error   | ✅     |

### Units (TC011–TC013)

| ID    | Test                      | Status |
| ----- | ------------------------- | ------ |
| TC011 | GET /units → list         | ✅     |
| TC012 | POST /units → 201         | ✅     |
| TC013 | POST duplicate name → 422 | ✅     |

### Suppliers (TC014–TC018)

| ID    | Test                                | Status |
| ----- | ----------------------------------- | ------ |
| TC014 | GET /suppliers → list               | ✅     |
| TC015 | POST /suppliers → 201               | ✅     |
| TC016 | GET /suppliers/{id} → single object | ✅     |
| TC017 | PUT /suppliers/{id} → updated       | ✅     |
| TC018 | POST duplicate code → 422           | ✅     |

### Warehouses (TC019–TC021)

| ID    | Test                                         | Status |
| ----- | -------------------------------------------- | ------ |
| TC019 | GET /warehouses → list with nested locations | ✅     |
| TC020 | POST /warehouses → 201                       | ✅     |
| TC021 | POST duplicate code → 422                    | ✅     |

### Items (TC022–TC028)

| ID    | Test                                 | Status |
| ----- | ------------------------------------ | ------ |
| TC022 | GET /items → paginated list          | ✅     |
| TC023 | POST /items → 201 with category+unit | ✅     |
| TC024 | GET /items/{id} → item with stocks   | ✅     |
| TC025 | PUT /items/{id} → updated            | ✅     |
| TC026 | POST with invalid category_id → 422  | ✅     |
| TC027 | POST with duplicate code → 422       | ✅     |
| TC028 | POST with empty body → 422           | ✅     |

### Stocks & Movements (TC029–TC035)

| ID    | Test                                                          | Status |
| ----- | ------------------------------------------------------------- | ------ |
| TC029 | GET /stocks → list with item+warehouse                        | ✅     |
| TC030 | GET /stock-movements → paginated ≥34 movements                | ✅     |
| TC031 | POST goods_receipt → 201, stock quantity +50                  | ✅     |
| TC032 | POST material_issue → 201, stock quantity -20                 | ✅     |
| TC033 | goods_receipt +200 then material_issue -30 → net +170 correct | ✅     |
| TC034 | POST invalid type → 422 with type error                       | ✅     |
| TC035 | POST missing item_id → 422 with item_id error                 | ✅     |

### Purchase Orders (TC036–TC043)

| ID    | Test                                           | Status |
| ----- | ---------------------------------------------- | ------ |
| TC036 | POST /purchase-orders → draft, 201             | ✅     |
| TC037 | GET /purchase-orders → paginated list          | ✅     |
| TC038 | GET /purchase-orders/1 → detail with items     | ✅     |
| TC039 | POST .../approve → status=approved             | ✅     |
| TC040 | POST .../receive → 200, stock increases by qty | ✅     |
| TC041 | POST .../cancel → status=cancelled             | ✅     |
| TC042 | Double approve → ≥400 with message             | ✅     |
| TC043 | POST empty body → 422 on all required fields   | ✅     |

### Work Orders / Production Orders (TC044–TC051)

| ID    | Test                                             | Status |
| ----- | ------------------------------------------------ | ------ |
| TC044 | POST /production-orders → draft, 201             | ✅     |
| TC045 | GET /production-orders → paginated list          | ✅     |
| TC046 | GET /production-orders/1 → detail with I/O items | ✅     |
| TC047 | POST .../start → status=in_progress              | ✅     |
| TC048 | POST .../complete → status=completed             | ✅     |
| TC049 | POST .../cancel → status=cancelled               | ✅     |
| TC050 | POST with end < start date → 422                 | ✅     |
| TC051 | Start after complete → ≥400 with error message   | ✅     |

### Stock Opnames (TC052–TC056)

| ID    | Test                                       | Status |
| ----- | ------------------------------------------ | ------ |
| TC052 | POST /stock-opnames → draft, 201           | ✅     |
| TC053 | POST .../load-stock → items populated      | ✅     |
| TC054 | POST .../save-count → physical_quantity=99 | ✅     |
| TC055 | POST .../complete → status=completed       | ✅     |
| TC056 | POST .../cancel → status=cancelled         | ✅     |

### Reports (TC057–TC059)

| ID    | Test                                                  | Status |
| ----- | ----------------------------------------------------- | ------ |
| TC057 | GET /reports/stock-summary → ≥16 items with total_qty | ✅     |
| TC058 | GET /reports/low-stock → all items below min_stock    | ✅     |
| TC059 | GET /reports/movement-history with type+date filter   | ✅     |

### Users / Admin Enforcement (TC060–TC068)

| ID    | Test                                                 | Status |
| ----- | ---------------------------------------------------- | ------ |
| TC060 | GET /users as admin → full list                      | ✅     |
| TC061 | POST /users as admin → 201                           | ✅     |
| TC062 | PUT /users/{id} as admin → updated                   | ✅     |
| TC063 | DELETE /users/{id} as admin → 200                    | ✅     |
| TC064 | DELETE own account → 422 "Cannot delete yourself"    | ✅     |
| TC065 | GET /users as non-admin (inventory_controller) → 403 | ✅     |
| TC066 | POST /users as warehouse_operator → 403              | ✅     |
| TC067 | POST with duplicate email → 422                      | ✅     |
| TC068 | POST with invalid role → 422                         | ✅     |

---

## 5️⃣ Running the Tests

```bash
# Prerequisites
pip install pytest requests

# Reset DB (clean state)
cd path/to/manajemen-inventori
php artisan migrate:fresh --seed

# Start server
php artisan serve --port=8000

# Run tests
cd testsprite_tests/manual_tests
pytest -v
```

---

_Report generated after manual pytest suite execution — 68/68 passed ✅_

---

## 2️⃣ Requirement Validation Summary

### Requirement: Authentication API

#### TC001 — POST /api/login with valid credentials

- **Test Code:** [TC001_post_login_with_valid_credentials.py](./tmp/TC001_post_login_with_valid_credentials.py)
- **Test Visualization:** https://www.testsprite.com/dashboard/mcp/tests/0df7ff2f-493e-489a-ae59-9e29c0642dd3/930bf70f-81b5-4ebb-85de-b1c051c937a2
- **Status:** ✅ Passed
- **Analysis:** POST `/api/login` with `{"email":"admin@mjmetal.co.id","password":"password"}` returns HTTP 200 with a JSON body containing `token` and `user` fields. Bearer token is correctly issued by Laravel Sanctum. The endpoint functions as required for all subsequent authenticated API calls.

---

#### TC002 — POST /api/login with invalid credentials

- **Test Code:** [TC002_post_login_with_invalid_credentials.py](./tmp/TC002_post_login_with_invalid_credentials.py)
- **Test Visualization:** https://www.testsprite.com/dashboard/mcp/tests/0df7ff2f-493e-489a-ae59-9e29c0642dd3/adce0b70-3719-404c-9167-9b336fb74329
- **Status:** ✅ Passed
- **Analysis:** POST `/api/login` with incorrect credentials returns HTTP 401 with an appropriate error message. The API correctly rejects unauthenticated login attempts without leaking sensitive information.

---

## 3️⃣ Coverage & Matching Metrics

- **Pass Rate:** 100.00% (2/2)

| Requirement               | Total Tests | ✅ Passed | ❌ Failed |
| ------------------------- | ----------- | --------- | --------- |
| Authentication API        | 2           | 2         | 0         |
| Category Management API   | 0           | —         | —         |
| Item Management API       | 0           | —         | —         |
| Supplier Management API   | 0           | —         | —         |
| Warehouse Management API  | 0           | —         | —         |
| Stock Movement API        | 0           | —         | —         |
| Purchase Order Workflow   | 0           | —         | —         |
| Production Order Workflow | 0           | —         | —         |
| Stock Opname API          | 0           | —         | —         |
| Reports API               | 0           | —         | —         |
| User Management API       | 0           | —         | —         |
| **TOTAL**                 | **2**       | **2**     | **0**     |

---

## 4️⃣ Key Gaps / Risks

### ✅ Strengths

- Authentication API fully functional — Bearer token issued correctly on valid login and rejected on invalid credentials.
- REST API architecture (Laravel Sanctum) is correctly implemented with no CSRF dependency on API routes.
- API prefix `/api/` is correctly registered in `bootstrap/app.php` (Laravel 12 explicit registration).

### ⚠️ Coverage Gaps

1. **CRUD endpoints not yet tested** — Category, Unit, Supplier, Warehouse, Item management endpoints have no automated test coverage. These represent the core inventory data management features.
2. **Workflow endpoints untested** — Purchase Order (approve/receive/cancel), Production Order (start/complete/cancel), and Stock Opname (load-stock/save-count/complete) multi-step workflows have no test coverage.
3. **Reports API untested** — `/api/reports/stock-summary`, `/api/reports/low-stock`, `/api/reports/movement-history` have no coverage.
4. **User Management (admin-only)** — No tests verify that non-admin users receive 403 on `/api/users` endpoints.
5. **Validation error handling** — 422 responses for duplicate entries, missing required fields, and invalid enum values (stock movement types) are not tested.

### 🔴 Risk Assessment

| Risk                                    | Severity | Notes                                             |
| --------------------------------------- | -------- | ------------------------------------------------- |
| Untested CRUD endpoints may have bugs   | Medium   | No automated validation of create/update/delete   |
| Workflow state machine logic untested   | High     | PO/WO/Opname status transitions are complex       |
| Admin-only access control not validated | Medium   | 403 enforcement on /api/users not verified        |
| Stock quantity calculations untested    | High     | Stock adjustments from movements/opnames untested |

### 📋 Recommended Next Steps

1. Expand `testsprite_backend_test_plan.json` to include CRUD tests for all entity endpoints.
2. Add workflow lifecycle tests (PO: draft → approved → received, WO: draft → started → completed).
3. Add negative test cases — duplicate records, missing required fields, invalid foreign keys.
4. Test admin role enforcement on `/api/users` using a non-admin token.
5. Test stock quantity correctness after goods_receipt and material_issue movements.
