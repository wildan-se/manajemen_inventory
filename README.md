# 📦 SKBU — Sistem Manajemen Inventori

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-77C1D2?style=for-the-badge&logo=alpine.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)

**Aplikasi Web Manajemen Inventori komprehensif untuk pengelolaan rantai pasok perusahaan.**  
Dibalut desain *Premium Dark Glassmorphism* dengan navigasi SPA menggunakan Turbo Drive.

[Demo](#) · [Laporan Bug](#) · [Permintaan Fitur](#)

</div>

---

## 📑 Daftar Isi

1. [Gambaran Umum](#-gambaran-umum)
2. [Tech Stack](#-tech-stack)
3. [Arsitektur Sistem](#-arsitektur-sistem)
4. [Fitur Detail per Modul](#-fitur-detail-per-modul)
5. [Frontend — UI/UX](#-frontend--uiux)
6. [Backend — Server Side](#-backend--server-side)
7. [Database & Relasi](#-database--relasi)
8. [Keamanan (Security)](#-keamanan-security)
9. [Role & Hak Akses](#-role--hak-akses)
10. [Panduan Instalasi](#-panduan-instalasi)
11. [Struktur Direktori](#-struktur-direktori)
12. [Konfigurasi Environment](#-konfigurasi-environment)

---

## 🚀 Gambaran Umum

**SKBU Sistem Manajemen Inventori** adalah aplikasi web *full-stack* berbasis Laravel yang dirancang untuk mengelola seluruh siklus hidup barang di dalam perusahaan — mulai dari **penerimaan barang dari supplier**, **pergerakan antar gudang**, **pelacakan stok real-time**, **proses produksi internal**, hingga **pembuatan laporan eksekutif**.

Sistem ini dibangun di atas filosofi **Event-Sourcing ringan**: setiap perubahan stok dicatat sebagai transaksi `StockMovement` yang tidak dapat diubah, sehingga rekam jejak (audit trail) selalu terjaga.

### ⚡ Keunggulan Utama

| Aspek | Keterangan |
|-------|-----------|
| **Navigasi SPA** | Hotwire Turbo Drive — perpindahan halaman tanpa full-page reload |
| **Real-time UI** | Alpine.js untuk reaktivitas komponen tanpa framework berat |
| **Design System** | Dark Glassmorphism konsisten di seluruh halaman |
| **Security** | OWASP Top 10 compliant dengan middleware keamanan custom |
| **Multi-Warehouse** | Mendukung banyak gudang sekaligus dengan lokasi rak spesifik |
| **Export Data** | Export ke PDF & Excel (XLS) dengan rate limiting anti-scraping |

---

## 🛠️ Tech Stack

### Backend
| Layer | Teknologi | Versi |
|-------|-----------|-------|
| Framework | Laravel | 12.x |
| Language | PHP | 8.2+ |
| Auth | Laravel Breeze (Session-based) | — |
| API Token | Laravel Sanctum | — |
| ORM | Eloquent ORM | — |
| Service Layer | Custom Service Classes | — |
| Policy | Laravel Gates & Policies | — |

### Frontend
| Layer | Teknologi | Versi |
|-------|-----------|-------|
| Bundler | Vite | 7.x |
| CSS Framework | Tailwind CSS | 3.x |
| Interaktivitas | Alpine.js | 3.x |
| SPA Navigation | Hotwire Turbo Drive | 8.x |
| Charts | Chart.js | 4.4.x |
| Templating | Laravel Blade | — |

### Database & Infrastruktur
| Layer | Teknologi |
|-------|-----------|
| Database | MySQL 8.0+ / MariaDB 10.3+ |
| Session | Database Driver |
| Cache | File Driver (dev) / Redis (prod) |
| Queue | Sync (dev) / Database (prod) |

### Development & Testing
| Tool | Kegunaan |
|------|---------|
| Playwright | E2E Testing |
| PHPUnit | Unit & Feature Testing |
| Concurrently | Jalankan dev server paralel |

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────┐
│                         BROWSER                                  │
│  Turbo Drive SPA | Alpine.js Reactivity | Chart.js Visualization │
└────────────────────┬────────────────────────────────────────────┘
                     │ HTTP Request (Turbo / Form)
┌────────────────────▼────────────────────────────────────────────┐
│                   LARAVEL APPLICATION                            │
│                                                                  │
│  ┌─────────────┐   ┌─────────────┐   ┌──────────────────────┐  │
│  │  Middleware  │──▶│  Controller  │──▶│    Service Layer      │  │
│  │  - Auth      │   │  (Resource)  │   │  - StockService      │  │
│  │  - EnsureRole│   │              │   │  - PurchaseOrder Svc  │  │
│  │  - SecurityHd│   │              │   │  - ProductionOrder Svc│  │
│  └─────────────┘   └──────┬──────┘   │  - StockOpname Svc    │  │
│                            │          └──────────┬─────────────┘  │
│                    ┌───────▼──────┐              │                │
│                    │  Blade View   │    ┌─────────▼───────────┐   │
│                    │  (Template)   │    │   Eloquent Models   │   │
│                    └──────────────┘    │   & DB Transactions │   │
│                                        └─────────┬───────────┘   │
└──────────────────────────────────────────────────┼───────────────┘
                                                   │
┌──────────────────────────────────────────────────▼───────────────┐
│                         MySQL Database                            │
│  users | items | warehouses | stocks | stock_movements | ...      │
└──────────────────────────────────────────────────────────────────┘
```

### Pola Desain (Design Patterns) yang Digunakan

- **Service Layer Pattern** — Logika bisnis dipisahkan ke `app/Services/`, controller hanya sebagai orchestrator
- **Repository-like dengan Eloquent** — Model mengandung query scope dan relasi
- **Policy Pattern** — Otorisasi granular via `app/Policies/`
- **Event Sourcing (Lightweight)** — Setiap perubahan stok menghasilkan record `StockMovement` yang immutable
- **SPA Navigation** — Turbo Drive memberikan pengalaman SPA tanpa JavaScript framework berat

---

## 📋 Fitur Detail per Modul

### 1. 📊 Dashboard Interaktif

Dashboard utama menampilkan ringkasan operasional secara real-time dengan **4 chart Chart.js** dan **2 data panel**.

**Stat Cards (4 metrik utama):**
- Total Item Aktif di sistem
- Jumlah Item di Bawah Stok Minimum (peringatan)
- Purchase Order yang masih Pending (Draft/Approved)
- Work Order yang sedang Aktif (Draft/In Progress)

**Charts (Chart.js v4.4):**
| Chart | Tipe | Data |
|-------|------|------|
| Tren Mutasi Stok | Line + Area Fill | Inbound vs Outbound 30 hari terakhir |
| Distribusi Tipe Mutasi | Doughnut | Komposisi jenis transaksi bulan ini |
| Top 10 Stok Tertinggi | Horizontal Bar | Item dengan kuantitas stok terbesar |
| Status PO & Work Order | Grouped Bar | Distribusi status per jenis dokumen |

**Data Panels:**
- Daftar 6 item dengan stok paling kritis (di bawah minimum)
- 10 mutasi stok terbaru dengan timestamp relatif

---

### 2. 🗂️ Master Data

#### Gudang & Lokasi (`/warehouses`)
- CRUD lengkap multi-gudang
- Setiap gudang dapat memiliki banyak **lokasi/rak** (`locations`)
- Toggle aktif/nonaktif dengan toast notification
- Detail view menampilkan semua lokasi dan stok per gudang

#### Kategori (`/categories`) & Satuan (`/units`)
- Klasifikasi item (contoh: Bahan Baku, Produk Jadi, Spare Part)
- Unit pengukuran (Pcs, Kg, Liter, Box, dll.) dengan singkatan
- Validasi unik pada nama dan kode

#### Item / Material (`/items`)
- Data lengkap item: kode, nama, kategori, satuan, batas stok min/max
- Penempatan default (gudang & lokasi)
- Toggle aktif dengan validasi: item nonaktif tidak bisa digunakan dalam transaksi baru
- Tampilan stok total real-time per item dari semua gudang

#### Supplier (`/suppliers`)
- Data vendor: nama perusahaan, PIC, email, telepon, alamat
- Riwayat Purchase Order per supplier
- Toggle aktif/nonaktif

---

### 3. 📦 Mutasi Stok (`/stock-movements`)

**7 Tipe Transaksi yang Didukung:**
| Kode | Label | Deskripsi |
|------|-------|-----------|
| `goods_receipt` | Goods Receipt | Penerimaan barang dari Purchase Order |
| `material_issue` | Material Issue | Pengeluaran bahan baku ke produksi |
| `stock_transfer` | Stock Transfer | Pemindahan stok antar gudang |
| `production_output` | Production Output | Hasil produksi masuk ke stok |
| `sales_dispatch` | Sales Dispatch | Pengiriman barang ke pelanggan |
| `stock_adjustment` | Stock Adjustment | Koreksi stok (khusus role privileged) |
| `stock_opname` | Stock Opname | Penyesuaian hasil opname fisik |

**Fitur:**
- Setiap transaksi menghasilkan nomor referensi unik otomatis (format: `SM-YYYYMMDD-XXXX`)
- Validasi stok tersedia sebelum transaksi diproses
- `stock_adjustment` hanya bisa dilakukan oleh: Admin, Inventory Controller, Supervisor
- Item dan gudang tidak aktif tidak dapat dipilih
- Detail view per transaksi dengan riwayat lengkap

---

### 4. 📋 Stock Opname (`/stock-opnames`)

Proses count fisik stok dan rekonsiliasi dengan data sistem.

**Alur Proses:**
```
Draft → Load Stock (sistem auto-populate dari DB) → Input Count Fisik → Complete
                                                                     ↓
                                              Selisih di-post sebagai Stock Movement
```

**Fitur:**
- Load semua item stok dari gudang yang dipilih secara otomatis
- Input hitungan fisik per item
- Kalkulasi variance (selisih sistem vs fisik) otomatis
- Saat `complete`: selisih diproses sebagai `stock_opname` movement secara otomatis
- Status: `draft` → `in_progress` → `completed` / `cancelled`

---

### 5. 🛒 Purchase Order (`/purchase-orders`)

Modul pengadaan barang dari supplier eksternal.

**Alur Approval:**
```
Draft (Operator) → Approved (Supervisor/Admin) → Receive Goods → Received
                                               ↓                     ↓
                                          Cancelled          Partially Received
```

**Fitur:**
- Multi-item per PO (banyak produk dalam satu order)
- Nomor PO otomatis (format: `PO-YYYYMMDD-XXXX`)
- **Anti Race Condition**: `lockForUpdate()` dalam DB transaction saat approval
- **Anti Self-Approval**: User yang membuat PO tidak bisa meng-approve PO-nya sendiri
- Receive goods: menghasilkan `goods_receipt` movement otomatis ke stok
- Status tracking lengkap dengan timestamp approval dan penerima

---

### 6. ⚙️ Production Order / Work Order (`/production-orders`)

Surat Perintah Produksi untuk manufaktur internal.

**Alur Proses:**
```
Draft → Start (tarik bahan baku otomatis) → Complete (setor produk jadi) / Cancel
```

**Fitur:**
- Definisi **input materials** (bahan baku yang akan dikonsumsi)
- Definisi **output products** (produk jadi yang akan dihasilkan)
- Saat `start`: bahan baku otomatis keluar dari stok sebagai `material_issue`
- Saat `complete`: produk jadi otomatis masuk ke stok sebagai `production_output`
- Validasi ketersediaan stok bahan baku sebelum proses start
- Tanggal rencana & aktual start/end

---

### 7. 📊 Laporan (`/reports`)

Sistem pelaporan dengan filter dinamis dan opsi export.

#### Ringkasan Stok (`/reports/stock-summary`)
- Pivot stok terkini semua item per gudang
- Filter per gudang
- Indikator visual stok rendah (merah)
- Export: **PDF** (dibuka tab baru) & **Excel/XLS**

#### Stok Rendah (`/reports/low-stock`)
- Daftar semua item di bawah batas minimum
- Kalkulasi kekurangan (deficit = min_stock - current_stock)
- Filter per gudang
- Export: **PDF** & **Excel/XLS**

#### Riwayat Mutasi (`/reports/movement-history`)
- Log semua transaksi stok dengan filter lengkap
- Filter: tipe transaksi, item, gudang, rentang tanggal
- Paginasi (50 per halaman)
- Export: **PDF** & **Excel/XLS**

> **Rate Limiting Export:** Semua route export dilindungi `throttle:10,1` (max 10 request per menit per pengguna) untuk mencegah scraping data.

---

### 8. 👥 Manajemen Pengguna (`/users`) — Admin Only

- CRUD pengguna (khusus role `admin`)
- 5 pilihan role dengan hak akses berbeda
- Toggle aktif/nonaktif: user nonaktif tidak bisa login
- Jika user sedang login lalu dinonaktifkan → otomatis di-logout pada request berikutnya
- Validasi email unik
- Reset password dengan hash bcrypt

---

## 🎨 Frontend — UI/UX

### Design System: Dark Glassmorphism

Seluruh antarmuka menggunakan desain konsisten yang dibangun di atas:

```css
/* Core design tokens */
--bg-base: #0d1117        /* Background utama — deep navy */
--glass-bg: rgba(255,255,255,0.04)   /* Glassmorphism panel */
--glass-border: rgba(255,255,255,0.08) /* Border subtle */
--text-primary: #e2e8f0   /* Teks utama */
--text-muted: rgba(148,163,184,0.7)  /* Teks sekunder */
--accent-indigo: #6366f1  /* Warna aksen utama */
```

### Komponen Utama

**Sidebar Navigasi:**
- Collapsible sidebar dengan animasi smooth
- State tersimpan di `localStorage` (persistent antar sesi)
- Auto-sync active link via Turbo Drive events
- Keyboard shortcut `[` untuk toggle
- Indikator aktif per halaman

**Toast Notifications:**
- Posisi fixed kanan atas
- Auto-dismiss setelah 5 detik
- 4 varian: `success` (hijau), `error` (merah), `warning` (kuning), `info` (biru)
- Animasi slide-in dari kanan, slide-out ke kanan
- Stacking: beberapa toast dapat muncul bersamaan
- Tombol close manual

**Modal System:**
- Komponen `<x-modal>` reusable berbasis Alpine.js
- Backdrop blur dengan click-outside-to-close
- Digunakan untuk: Create item, Edit item, Detail view
- Animasi scale + fade in/out

**Form Controls:**
- Input dengan glassmorphism focus ring
- Custom toggle switch (hijau = aktif, abu = nonaktif) — menggantikan checkbox biasa
- Select dropdown dengan styling konsisten
- Validasi error inline dari Laravel

**Tabel Data:**
- Zebra striping subtle
- Hover row highlight
- Badge status berwarna per konteks
- Responsive dengan scroll horizontal di mobile

### SPA Navigation (Turbo Drive)

Aplikasi menggunakan **Hotwire Turbo Drive** untuk navigasi tanpa full-page reload:

- `data-turbo-permanent` pada sidebar — elemen tidak di-render ulang saat navigate
- `data-turbo="false"` pada link export PDF/CSV — mencegah Turbo menginterksepsi download
- `turbo:before-cache` event — mencegah halaman PDF di-cache oleh Turbo
- `turbo:load` event — sync state sidebar active link dan collapse state

### Chart Dashboard (Chart.js 4.4)

Semua chart dikonfigurasi dengan tema dark yang konsisten:
- Background transparan, grid tipis `rgba(255,255,255,0.05)`
- Tooltip dark dengan border subtle
- Font Inter/system-ui
- Animasi entrance smooth

---

## ⚙️ Backend — Server Side

### Struktur Aplikasi

```
app/
├── Http/
│   ├── Controllers/        # 13 Controller (Resource + Custom Actions)
│   ├── Middleware/
│   │   ├── EnsureRole.php      # RBAC middleware
│   │   └── SecurityHeaders.php # HTTP Security Headers
│   └── Requests/
│       └── Auth/               # Form Request validation
├── Models/                 # 15 Eloquent Model
├── Policies/
│   ├── PurchaseOrderPolicy.php
│   └── StockMovementPolicy.php
├── Services/               # Business Logic Layer
│   ├── StockService.php           # Core stock operations
│   ├── PurchaseOrderService.php   # PO lifecycle
│   ├── ProductionOrderService.php # WO lifecycle
│   └── StockOpnameService.php     # Opname reconciliation
└── View/
    └── Components/         # Blade Components
```

### Service Layer

Semua logika bisnis yang kompleks dienkapsulasi dalam Service classes, bukan di Controller:

#### `StockService`
- `generateReference()` — generate nomor referensi unik dengan loop uniqueness check
- `getOrCreateStock()` — firstOrCreate stock record per item/warehouse/location
- `goodsReceipt()` — tambah stok dengan DB transaction + lockForUpdate
- `materialIssue()` — kurangi stok dengan validasi ketersediaan
- `stockTransfer()` — pindah stok antar gudang (debit source, kredit destination)
- `productionOutput()` — tambah stok produk jadi
- `stockAdjustment()` — koreksi stok dengan delta calculation

#### `PurchaseOrderService`
- `create()` — buat PO dengan validasi supplier & items
- `approve()` — approve dengan DB transaction + lockForUpdate (anti race condition)
- `receive()` — terima barang, trigger `goodsReceipt` ke StockService
- `cancel()` — cancel PO dengan validasi status

#### `ProductionOrderService`
- `start()` — validasi ketersediaan semua bahan baku, trigger `materialIssue`
- `complete()` — trigger `productionOutput` untuk semua output items
- `cancel()` — rollback jika masih di status draft

#### `StockOpnameService`
- `loadStock()` — populate semua stok aktif dari gudang yang dipilih
- `saveCount()` — simpan hitungan fisik per item
- `complete()` — hitung selisih & posting koreksi sebagai `stock_opname` movement

### Middleware Stack

```
Request → TrustProxies → ValidatePathEncoding → HandleCors
        → ValidatePostSize → TrimStrings → ConvertEmptyStringsToNull
        → EncryptCookies → AddQueuedCookies → StartSession
        → ShareErrorsFromSession → VerifyCsrfToken
        → Authenticate (auth) → SecurityHeaders → EnsureRole → Controller
```

**`SecurityHeaders` Middleware** menambahkan:
- `X-Frame-Options: SAMEORIGIN` — anti clickjacking
- `X-Content-Type-Options: nosniff` — anti MIME sniffing
- `X-XSS-Protection: 1; mode=block` — legacy browser XSS protection
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: geolocation=(), microphone=(), camera=()`
- `Strict-Transport-Security` — hanya jika HTTPS
- `Content-Security-Policy` — hanya di environment production/staging

---

## 🗃️ Database & Relasi

### Daftar Tabel (14 Migrasi)

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Pengguna sistem dengan role dan status aktif |
| `categories` | Klasifikasi item |
| `units` | Satuan pengukuran (UoM) |
| `suppliers` | Data vendor/pemasok |
| `warehouses` | Data gudang |
| `locations` | Lokasi rak/area di dalam gudang |
| `items` | Master data barang/material |
| `stocks` | Saldo stok real-time per item/warehouse/location |
| `stock_movements` | Log semua pergerakan stok (immutable) |
| `purchase_orders` | Header Purchase Order |
| `purchase_order_items` | Detail item per Purchase Order |
| `production_orders` | Header Work Order / Production Order |
| `production_order_items` | Bahan baku (input) dan produk (output) per WO |
| `stock_opnames` | Header Stock Opname session |
| `stock_opname_items` | Detail count per item dalam opname |

### Entity Relationship Diagram (ERD)

```
users ──────────────────────── stock_movements
  │                              │         │
  ├── purchase_orders ──────────┤    items ─┤
  │     └── purchase_order_items│     │    │
  │                              │   stocks │
  ├── production_orders ─────────┤     │    │
  │     └── production_order_items     │    │
  │                              │     │   warehouses
  └── stock_opnames              │     │     └── locations
        └── stock_opname_items   │     │
                                 │     │
              categories ────────┘     │
              units ───────────────────┘
              suppliers ── purchase_orders
```

### Filosofi Data Integrity

1. **Stok Selalu Konsisten** — Tabel `stocks` adalah **derived state** yang diperbarui secara transaksional setiap ada pergerakan. Tidak ada angka stok yang dihitung manual.

2. **Immutable Audit Trail** — Tabel `stock_movements` adalah append-only. Tidak ada UPDATE atau DELETE pada records historis.

3. **Soft Delete filosofi** — Item/Supplier yang memiliki transaksi tidak dihapus secara fisik, melainkan di-toggle `is_active = false`.

4. **DB Transactions** — Semua operasi bisnis yang melibatkan lebih dari satu tabel dibungkus `DB::transaction()` dengan `lockForUpdate()` untuk mencegah race condition.

---

## 🔐 Keamanan (Security)

Implementasi berdasarkan **OWASP Top 10** dan best practices Laravel:

### Critical (C)
| ID | Kerentanan | Mitigasi |
|----|----------|---------|
| C-01 | Debug route terbuka (`/toast-test`) | Route dihapus dari production |
| C-02 | Tidak ada rate limiting pada export | `throttle:10,1` pada semua route export |

### High (H)
| ID | Kerentanan | Mitigasi |
|----|----------|---------|
| H-01 | Race condition pada PO approval | `DB::transaction()` + `lockForUpdate()` |
| H-02 | Self-approval Purchase Order | Validasi `approved_by !== created_by` di Policy |
| H-03 | Otorisasi `stock_adjustment` kurang ketat | Dibatasi hanya role privileged (admin/IC/supervisor) |
| H-04 | Tidak ada security headers | Middleware `SecurityHeaders` global |

### Medium (M)
| ID | Kerentanan | Mitigasi |
|----|----------|---------|
| M-01 | Error message bocorkan info stok spesifik | Generic error message + internal logging |
| M-04 | Item/gudang nonaktif bisa dipakai di transaksi | Validasi `Rule::exists()->where('is_active', true)` |

### Autentikasi

- Session-based auth via Laravel Breeze
- Rate limiting pada login (`throttle:5,1`)
- CSRF protection (built-in Laravel)
- Session invalidation saat user dinonaktifkan
- Password hashing dengan bcrypt

---

## 👤 Role & Hak Akses

| Role | Kode | Hak Utama |
|------|------|-----------|
| **Admin** | `admin` | Full access: semua fitur + manajemen user |
| **Inventory Controller** | `inventory_controller` | Semua transaksi inventori + stock adjustment |
| **Supervisor / Manager** | `supervisor` | Approve PO + semua laporan + stock adjustment |
| **Warehouse Operator** | `warehouse_operator` | Input mutasi stok, opname, create PO/WO |
| **Production Staff** | `production_staff` | Input material issue, create & manage WO |

### Matriks Akses (Ringkasan)

| Fitur | Admin | IC | Supervisor | WH Operator | Prod Staff |
|-------|:-----:|:--:|:----------:|:-----------:|:----------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |
| Master Data (CRUD) | ✅ | ✅ | 👁️ | 👁️ | 👁️ |
| Stock Movement | ✅ | ✅ | ✅ | ✅ | ✅ |
| Stock Adjustment | ✅ | ✅ | ✅ | ❌ | ❌ |
| Purchase Order | ✅ | ✅ | ✅ | ✅ | ❌ |
| Approve PO | ✅ | ❌ | ✅ | ❌ | ❌ |
| Work Order | ✅ | ✅ | ✅ | ✅ | ✅ |
| Stock Opname | ✅ | ✅ | ✅ | ✅ | ❌ |
| Reports & Export | ✅ | ✅ | ✅ | ✅ | ❌ |
| User Management | ✅ | ❌ | ❌ | ❌ | ❌ |

> 👁️ = Read Only · ✅ = Full Access · ❌ = No Access

---

## 🚀 Panduan Instalasi

### Prasyarat Sistem

- **PHP** >= 8.2 (dengan ekstensi: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML)
- **Composer** >= 2.x
- **Node.js** >= 18.x & npm >= 9.x
- **MySQL** >= 8.0 atau **MariaDB** >= 10.3
- **Git**

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/username/manajemen-inventori.git
cd manajemen-inventori

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Konfigurasi database di file .env
# (lihat seksi Konfigurasi Environment di bawah)

# 6. Jalankan migrasi dan seeder
php artisan migrate --seed

# 7a. Development mode (HMR)
npm run dev
php artisan serve

# 7b. Production mode
npm run build
php artisan serve
```

### Akun Default (Setelah Seeder)

| Email | Password | Role |
|-------|----------|------|
| `admin@skbu.com` | `password` | Admin (Super) |
| `operator@skbu.com` | `password` | Warehouse Operator |

> ⚠️ **Penting**: Ganti password default sebelum deploy ke production!

---

## 📁 Struktur Direktori

```
manajemen-inventori/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    # Breeze auth controllers
│   │   │   ├── DashboardController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── UnitController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── WarehouseController.php
│   │   │   ├── ItemController.php
│   │   │   ├── StockMovementController.php
│   │   │   ├── PurchaseOrderController.php
│   │   │   ├── ProductionOrderController.php
│   │   │   ├── StockOpnameController.php
│   │   │   ├── ReportController.php
│   │   │   └── UserController.php
│   │   └── Middleware/
│   │       ├── EnsureRole.php           # RBAC middleware
│   │       └── SecurityHeaders.php      # HTTP security headers
│   │
│   ├── Models/                          # 15 Eloquent Models
│   │   ├── User.php
│   │   ├── Category.php / Unit.php
│   │   ├── Supplier.php
│   │   ├── Warehouse.php / Location.php
│   │   ├── Item.php / Stock.php
│   │   ├── StockMovement.php
│   │   ├── PurchaseOrder.php / PurchaseOrderItem.php
│   │   ├── ProductionOrder.php / ProductionOrderItem.php
│   │   └── StockOpname.php / StockOpnameItem.php
│   │
│   ├── Policies/
│   │   ├── PurchaseOrderPolicy.php
│   │   └── StockMovementPolicy.php
│   │
│   └── Services/
│       ├── StockService.php             # Core: all stock mutations
│       ├── PurchaseOrderService.php     # PO lifecycle management
│       ├── ProductionOrderService.php   # WO lifecycle management
│       └── StockOpnameService.php       # Opname reconciliation
│
├── resources/
│   ├── css/
│   │   └── app.css                      # Design system & components
│   ├── js/
│   │   ├── app.js                       # Alpine.js + Turbo Drive init
│   │   └── bootstrap.js
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php            # Master layout + sidebar + toast
│       ├── components/
│       │   ├── modal.blade.php          # Reusable modal component
│       │   └── ...
│       ├── dashboard.blade.php          # Dashboard + Chart.js
│       ├── items/ categories/ units/    # Master data views
│       ├── stock-movements/             # Mutasi stok views
│       ├── purchase-orders/             # PO views
│       ├── production-orders/           # WO views
│       ├── stock-opnames/               # Opname views
│       └── reports/
│           ├── stock-summary.blade.php
│           ├── low-stock.blade.php
│           ├── movement-history.blade.php
│           └── pdf/                     # PDF export views
│
├── routes/
│   ├── web.php                          # All web routes
│   └── auth.php                         # Breeze auth routes
│
├── database/
│   ├── migrations/                      # 14 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── UserSeeder.php
│
├── bootstrap/
│   └── app.php                          # Middleware registration
│
├── vite.config.js                       # Vite bundler config
├── tailwind.config.js                   # Tailwind config
└── package.json                         # NPM dependencies
```

---

## 🔧 Konfigurasi Environment

```env
# Aplikasi
APP_NAME="SKBU Inventori"
APP_ENV=local
APP_DEBUG=true          # Set false di production!
APP_URL=http://127.0.0.1:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_skbu_inventori
DB_USERNAME=root
DB_PASSWORD=

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache
CACHE_STORE=file        # Ganti ke redis di production

# Queue
QUEUE_CONNECTION=sync   # Ganti ke database/redis di production

# Log
LOG_CHANNEL=stack
LOG_LEVEL=debug         # Set warning di production
```

### Checklist Produksi

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Ganti session driver ke `database` atau `redis`
- [ ] Jalankan `php artisan config:cache && php artisan route:cache`
- [ ] Jalankan `npm run build` (bukan `dev`)
- [ ] Konfigurasi web server (Nginx/Apache) dengan HTTPS
- [ ] Pastikan `storage/` dan `bootstrap/cache/` writable
- [ ] Ganti semua password default pengguna

---

## 📝 Catatan Pengembangan

- Proyek ini menggunakan **Playwright** untuk E2E testing (`npm run playwright`)
- Semua komponen UI dibuat dari scratch — tidak ada UI library eksternal (Bootstrap, dll.)
- Chart.js dimuat via **CDN** di dashboard (tidak di-bundle Vite) untuk efisiensi
- Linting error di IDE pada file `.blade.php` terkait CSS adalah **false positive** dari language server — tidak memengaruhi runtime

---

<div align="center">

**SKBU Sistem Manajemen Inventori**  
Dibuat dengan ❤️ menggunakan Laravel & Tailwind CSS

</div>
