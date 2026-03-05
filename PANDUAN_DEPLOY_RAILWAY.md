# 🚂 Panduan Deploy Laravel ke Railway

> **Proyek:** Sistem Manajemen Inventori  
> **Stack:** PHP 8.2, Laravel 12, MySQL, Nginx, Docker  
> **Platform:** [Railway.app](https://railway.app)

---

## 📋 Daftar Isi

1. [Prasyarat](#1-prasyarat)
2. [Persiapan Kode (Sudah Selesai!)](#2-persiapan-kode)
3. [Buat Akun & Project di Railway](#3-buat-akun--project-di-railway)
4. [Setup Database MySQL di Railway](#4-setup-database-mysql-di-railway)
5. [Deploy Aplikasi Laravel](#5-deploy-aplikasi-laravel)
6. [Konfigurasi Environment Variables](#6-konfigurasi-environment-variables)
7. [Jalankan Migration Database](#7-jalankan-migration-database)
8. [Generate Domain & Akses Aplikasi](#8-generate-domain--akses-aplikasi)
9. [Troubleshooting](#9-troubleshooting)
10. [Perintah Artisan di Railway](#10-perintah-artisan-di-railway)

---

## 1. Prasyarat

Sebelum mulai, pastikan kamu sudah punya:

- [ ] **Akun GitHub** — kode harus di-push ke GitHub dulu
- [ ] **Git** terpasang di komputer
- [ ] **Akun Railway** (gratis, bisa login via GitHub)
- [ ] **Kartu kredit/debit** (opsional, untuk Railway Hobby Plan $5/bulan — free tier ada tapi terbatas)

> **Info Harga Railway:**  
> - **Free Trial:** $5 credit satu kali  
> - **Hobby Plan:** $5/bulan, cocok untuk proyek kecil-menengah  
> - **Pro Plan:** $20/bulan untuk production serius

---

## 2. Persiapan Kode

> **✅ Sudah selesai dipersiapkan!** File-file berikut sudah dibuat/diupdate:

| File | Keterangan |
|------|-----------|
| `Dockerfile` | Sudah diupdate, Railway-compatible |
| `docker/entrypoint.sh` | Script startup otomatis (migrate, cache, dll) |
| `docker/nginx/default.conf` | Nginx config |
| `docker/supervisor/supervisord.conf` | Supervisor config |

### Push Kode ke GitHub

Buka terminal di folder proyek dan jalankan:

```bash
# Tambahkan semua file
git add .

# Commit
git commit -m "feat: ready for Railway deployment"

# Jika belum ada remote origin, tambahkan dulu:
# git remote add origin https://github.com/USERNAME/manajemen-inventori.git
# git branch -M main

git push origin main
```

> **⚠️ PENTING:** Pastikan `.env` ada di `.gitignore` (sudah ada). Jangan pernah push file `.env` ke GitHub!

---

## 3. Buat Akun & Project di Railway

### Langkah 3.1 — Daftar/Login Railway

1. Buka **[railway.app](https://railway.app)**
2. Klik tombol **"Start a New Project"** atau **"Login"**
3. Pilih **"Continue with GitHub"** → Authorize Railway
4. Kamu akan masuk ke Railway Dashboard

### Langkah 3.2 — Buat Project Baru

1. Di Dashboard, klik **"New Project"**
2. Pilih **"Deploy from GitHub repo"**
3. Cari dan pilih repository **`manajemen-inventori`**
4. **JANGAN klik Deploy dulu!** — Kita perlu setup database terlebih dahulu

---

## 4. Setup Database MySQL di Railway

Railway menyediakan MySQL sebagai plugin yang bisa ditambahkan ke project.

### Langkah 4.1 — Tambah MySQL Service

1. Di dalam project Railway kamu, klik tombol **"+ New"** atau **"Add Service"**
2. Pilih **"Database"**
3. Pilih **"MySQL"**
4. Tunggu beberapa detik, MySQL service akan dibuat otomatis

### Langkah 4.2 — Lihat Kredensial MySQL

1. Klik pada service **MySQL** yang baru dibuat
2. Pergi ke tab **"Variables"**
3. Kamu akan melihat variabel-variabel berikut (catat ini!):

```
MYSQLHOST=...............
MYSQLPORT=3306
MYSQLDATABASE=railway
MYSQLUSER=root
MYSQLPASSWORD=...............
MYSQL_URL=mysql://root:password@host:3306/railway
```

---

## 5. Deploy Aplikasi Laravel

### Langkah 5.1 — Tambah Laravel Service dari GitHub

Jika belum ada service Laravel di project:
1. Klik **"+ New"** → **"GitHub Repo"**
2. Pilih repository `manajemen-inventori`
3. Railway akan otomatis mendeteksi `Dockerfile` dan mulai build

### Langkah 5.2 — Tunggu Build Selesai

- Proses build pertama memakan waktu **5-15 menit** (karena download dependencies)
- Lihat progress di tab **"Deployments"**
- Klik pada deployment untuk melihat log real-time

---

## 6. Konfigurasi Environment Variables

Ini adalah langkah **PALING PENTING**. Tanpa ini, aplikasi tidak akan berjalan.

### Langkah 6.1 — Buka Variables Service Laravel

1. Klik pada service Laravel (bukan MySQL)
2. Pergi ke tab **"Variables"**
3. Klik **"RAW Editor"** untuk input multiple variables sekaligus

### Langkah 6.2 — Copy-Paste Variables Berikut

Copy semua teks di bawah ini, lalu paste ke RAW Editor Railway:

```env
APP_NAME=Sistem Manajemen Inventori
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://YOUR-APP-URL.up.railway.app

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
```

> **⚠️ PERHATIAN VARIABEL MYSQL:**  
> Gunakan format `${{MySQL.VARIABEL}}` agar Railway otomatis mengambil nilai dari service MySQL.  
> Nama service MySQL mungkin berbeda — cek nama service MySQL di project kamu!

### Langkah 6.3 — Generate APP_KEY

APP_KEY harus diisi! Cara yang disarankan:

1. Buka terminal di komputer kamu (di folder proyek)
2. Pastikan PHP terpasang, lalu jalankan:
```bash
php artisan key:generate --show
```
3. Copy hasilnya (format: `base64:xxxx...`)
4. Paste ke variable `APP_KEY` di Railway:
```env
APP_KEY=base64:HASIL_GENERATE_DARI_ARTISAN_KEY_GENERATE
```

### Langkah 6.4 — Update APP_URL

Setelah deploy berhasil dan domain sudah dibuat:
1. Pergi ke tab **"Settings"** → **"Networking"**
2. Klik **"Generate Domain"**
3. Copy URL yang dibuat (contoh: `manajemen-inventori.up.railway.app`)
4. Update variable `APP_URL`:
```env
APP_URL=https://manajemen-inventori.up.railway.app
```

---

## 7. Jalankan Migration Database

> **✅ Ini sudah otomatis!** Script `docker/entrypoint.sh` yang kita buat akan menjalankan `php artisan migrate --force` setiap kali container start.

Tapi jika kamu ingin menjalankan **seeder** (data awal), perlu dijalankan manual via Railway CLI:

### Install Railway CLI

```bash
# Windows (PowerShell - run as Admin)
winget install Railway.RailwayCLI
```

### Login dan Link Project

```bash
railway login
railway link
# Pilih project dan service yang sesuai
```

### Jalankan Seeder

```bash
# Jalankan semua seeder
railway run php artisan db:seed

# Atau seeder spesifik
railway run php artisan db:seed --class=UserSeeder
```

---

## 8. Generate Domain & Akses Aplikasi

### Langkah 8.1 — Generate Domain Gratis

1. Klik service Laravel di Railway
2. Pergi ke tab **"Settings"**
3. Scroll ke bagian **"Networking"**
4. Klik **"Generate Domain"**
5. Railway akan memberikan domain format: `nama-random.up.railway.app`

### Langkah 8.2 — Custom Domain (Opsional)

Jika kamu punya domain sendiri:
1. Klik **"Custom Domain"**
2. Masukkan domain kamu (contoh: `inventori.namadomain.com`)
3. Railway akan memberikan CNAME record
4. Tambahkan CNAME record tersebut ke DNS domain kamu
5. Tunggu propagasi DNS (15 menit - 48 jam)

### Langkah 8.3 — Akses Aplikasi

Buka browser dan akses URL domain Railway kamu. Jika berhasil, kamu akan melihat halaman login aplikasi!

---

## 9. Troubleshooting

### ❌ Error: "Application failed to respond"

**Penyebab:** Port tidak sesuai — Railway menggunakan `$PORT` dinamis, bukan port 80 hardcoded.

**Solusi:** Pastikan `Dockerfile` menggunakan `CMD ["/entrypoint.sh"]` dan di `entrypoint.sh` ada baris:
```sh
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/http.d/default.conf
```

---

### ❌ Error: "SQLSTATE[HY000] [2002] Connection refused"

**Penyebab:** Variabel database salah atau MySQL service belum siap.

**Solusi:**
1. Cek tab Variables pastikan `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sudah benar
2. Gunakan format reference: `DB_HOST=${{MySQL.MYSQLHOST}}`
3. Pastikan MySQL service sudah running (cek di dashboard Railway)

---

### ❌ Error: "No application encryption key has been specified"

**Penyebab:** `APP_KEY` kosong.

**Solusi:** Generate dengan `php artisan key:generate --show` di lokal, lalu paste ke Railway Variables.

---

### ❌ CSS/JS tidak muncul (tampilan rusak)

**Penyebab:** Vite build gagal atau `APP_URL` tidak benar.

**Solusi:**
1. Pastikan `APP_URL` diisi dengan URL Railway yang benar (wajib pakai `https://`)
2. Cek log build untuk memastikan `npm run build` berhasil
3. Trigger redeploy setelah `APP_URL` diupdate

---

### ❌ Error: Upload file gagal / file hilang setelah deploy

**Penyebab:** Filesystem Railway bersifat ephemeral (sementara) — file akan hilang saat redeploy.

**Solusi jangka panjang:** Gunakan cloud storage seperti **AWS S3** atau **Cloudflare R2** dengan mengubah `FILESYSTEM_DISK=s3`.

---

## 10. Perintah Artisan di Railway

Setelah Railway CLI terinstall dan terhubung (`railway link`):

```bash
# Cek informasi aplikasi
railway run php artisan about

# Jalankan migration
railway run php artisan migrate

# Rollback migration
railway run php artisan migrate:rollback

# Jalankan seeder
railway run php artisan db:seed

# Clear semua cache
railway run php artisan optimize:clear

# Cek semua routes
railway run php artisan route:list

# Buka Tinker (PHP REPL interaktif)
railway run php artisan tinker

# Lihat log real-time dari Railway
railway logs -f
```

---

## 📊 Ringkasan Arsitektur di Railway

```
Railway Project
├── 🐳 Laravel Service (Docker)
│   ├── Nginx (Web Server) → port $PORT (Railway inject otomatis)
│   ├── PHP-FPM (PHP Processor) → port 9000 (internal)
│   └── Supervisor (Process Manager - menjalankan keduanya)
└── 🗄️ MySQL Service
    └── Database: railway
```

---

## ✅ Checklist Deploy

- [ ] Kode sudah di-push ke GitHub (termasuk `Dockerfile` dan `docker/entrypoint.sh`)
- [ ] Railway project sudah dibuat
- [ ] MySQL service sudah ditambahkan di Railway
- [ ] Environment variables sudah dikonfigurasi di service Laravel
- [ ] `APP_KEY` sudah diisi (bukan kosong!)
- [ ] `DB_*` variables sudah mengarah ke MySQL Railway: `${{MySQL.MYSQLHOST}}` dll.
- [ ] Deploy berhasil (status "Success" di tab Deployments)
- [ ] Database migration berhasil (cek di deploy logs)
- [ ] Domain sudah di-generate
- [ ] `APP_URL` sudah diupdate dengan URL Railway yang benar
- [ ] Aplikasi bisa diakses via browser ✓

---

*Panduan ini dibuat khusus untuk proyek Sistem Manajemen Inventori*  
*Last updated: 5 Maret 2026*
