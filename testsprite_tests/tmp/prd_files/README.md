# Manajemen Inventori Manufaktur

Sistem manajemen inventori berbasis web untuk lingkungan manufaktur. Dibangun dengan Laravel 12, aplikasi ini mencakup seluruh siklus operasional inventori mulai dari penerimaan bahan baku, proses produksi, stock opname, hingga laporan manajerial.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Auth | Laravel Breeze |
| Frontend | Blade, Tailwind CSS, Vite |
| Database | MySQL |
| ORM | Eloquent |
| Testing | PHPUnit 11 |

---

## Fitur Utama

- **Dashboard** - ringkasan stok, pergerakan terkini, dan indikator stok kritis
- **Master Data** - manajemen kategori, satuan unit, supplier, gudang, lokasi, dan item
- **Pergerakan Stok** - pencatatan masuk/keluar/transfer stok dengan audit trail lengkap
- **Purchase Order (PO)** - buat PO, approval, penerimaan barang, dan pembatalan
- **Production Order (Work Order)** - buat WO, proses produksi (start/complete), dan pembatalan
- **Stock Opname** - penghitungan fisik stok, pemuatan data, penyimpanan hasil hitung, dan finalisasi
- **Laporan** - ringkasan stok, stok menipis (low stock), dan riwayat pergerakan
- **Manajemen User** - CRUD user dan pengaturan role (khusus admin)

---

## Role Pengguna

| Role | Deskripsi |
|---|---|
| admin | Akses penuh termasuk manajemen user |
| inventory_controller | Kelola master data, PO, opname, dan laporan |
| warehouse_operator | Catat pergerakan stok dan penerimaan barang |
| supervisor | Akses baca dan approval |
| production_staff | Buat dan proses production order |

---

## Instalasi

### Prasyarat

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL

### Langkah Setup

Clone repository dan masuk ke direktori:

    git clone <repository-url>
    cd manajemen-inventori

Install dependensi PHP:

    composer install

Siapkan file environment:

    cp .env.example .env
    php artisan key:generate

Konfigurasi database di file .env:

    DB_DATABASE=manajemen_inventori
    DB_USERNAME=root
    DB_PASSWORD=

Jalankan migrasi dan seeder:

    php artisan migrate --seed

Install dependensi frontend dan build asset:

    npm install
    npm run build

Atau gunakan perintah setup otomatis:

    composer run setup

---

## Menjalankan Aplikasi

### Mode Development

    composer run dev

Perintah ini menjalankan secara bersamaan:
- php artisan serve - Laravel dev server (http://localhost:8000)
- npm run dev - Vite HMR
- php artisan queue:listen - Queue worker
- php artisan pail - Log viewer

### Mode Production

    npm run build
    php artisan serve

---

## Akun Default (Seeder)

| Role | Email | Password |
|---|---|---|
| Administrator | admin@inventory.com | password |
| Inventory Controller | inventory@inventory.com | password |
| Warehouse Operator | warehouse@inventory.com | password |
| Supervisor | supervisor@inventory.com | password |
| Production Staff | produksi@inventory.com | password |

---

## Menjalankan Test

    php artisan test

Atau dengan Composer:

    composer run test

---

## Struktur Direktori Penting

    app/
    Http/Controllers/   - Controller per modul
    Models/             - Eloquent models
    Services/           - Business logic (Stock, PO, Production, Opname)
    Providers/

    database/
    migrations/         - Definisi skema database
    seeders/            - Data awal

    resources/views/    - Blade templates (Tailwind CSS)
    routes/web.php      - Definisi routing

---

## Alur Bisnis Utama

### Purchase Order

Draft > Approved > Partially Received / Received (atau Cancelled)

### Production Order

Draft > In Progress > Completed (atau Cancelled)

### Stock Opname

Draft > In Progress > Completed (atau Cancelled)

---

## Integritas Data

- Stok tidak dapat bernilai negatif
- Setiap perubahan stok tercatat sebagai StockMovement
- Operasi multi-langkah menggunakan database transaction
- Seluruh aksi dilindungi middleware autentikasi dan otorisasi role
