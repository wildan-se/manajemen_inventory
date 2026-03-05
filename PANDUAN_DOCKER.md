# 🐳 Panduan Menjalankan SKBU Menggunakan Docker

Bagus sekali! Karena proyek SKBU ini **sudah dilengkapi dengan `Dockerfile` dan `docker-compose.yml`**, Anda sebenarnya sudah memiliki *superpower* untuk menjalankan aplikasi ini di mana saja tanpa pusing menginstall PHP, Composer, Node.js, atau MySQL secara manual. Semua sudah terisolasi!

Berikut adalah panduan menjalankan sistem manajemen inventori ini menggunakan Docker.

---

## 💻 1. Menjalankan Docker Secara Lokal (Di Laptop/PC)

Jika Anda ingin menjalankan aplikasi ini di komputer sendiri tanpa XAMPP/Laragon, Anda bisa menggunakan Docker Desktop.

### Persiapan:
1. Pastikan Anda telah menginstal **[Docker Desktop](https://www.docker.com/products/docker-desktop/)** di Windows/Mac Anda.
2. Buka aplikasi Docker Desktop agar *system engine* Docker berjalan di latar belakang.

### Langkah-langkah:
1. Buka folder proyek `manajemen-inventori` di Terminal (Command Prompt / PowerShell / VS Code Terminal).
2. Salin `.env.example` menjadi `.env` jika belum ada:
   ```bash
   copy .env.example .env
   ```
3. Sesuaikan konfigurasi database di file `.env` (Jika memakai `docker-compose.yml` bawaan, ubah menjadi ini):
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=manajemen_inventory
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. Jalankan perintah magis ini:
   ```bash
   docker-compose up -d --build
   ```
5. Tunggu hingga proses *download image* PHP & MySQL selesai (bisa memakan waktu beberapa menit saat pertama kali karena menginstall vendor PHP dan npm).
6. Selanjutnya, Anda perlu menjalankan migrasi dan *seeder* ke dalam wadah (container) Docker tersebut:
   ```bash
   docker exec -it skbu_inventori_app php artisan migrate --seed
   ```

🎉 **Selesai!** 
Aplikasi Anda bisa dibuka di: `http://localhost:8000`
PhpMyAdmin (Visual MySQL) bisa dibuka di: `http://localhost:8080`

---

## ☁️ 2. Deploy Docker ke VPS / Cloud (Contoh: Oracle Cloud, DigitalOcean, dll)

Jika Anda memilih *Opsi 3* (VPS Asli Linux) seperti yang disarankan sebelumnya, Docker adalah cara paling efisien untuk mem-publish project ini ke publik (Production).

### Langkah-langkah di Server Linux (VPS):

1. **Remote/Login SSH** ke dalam Server VPS Anda.
2. Install **Docker** dan **Docker Compose**.
   ```bash
   sudo apt-get update
   sudo apt-get install docker.io docker-compose -y
   ```
3. Copy/Clone kode Anda dari Github ke server:
   ```bash
   git clone https://github.com/wildan-se/manajemen_inventory.git
   cd manajemen_inventory
   ```
4. Ubah file `.env` untuk production (jangan lupa `DB_PASSWORD` diperketat di file `docker-compose.yml` dan `.env`, serta `APP_ENV=production`).
   ```bash
   cp .env.example .env
   nano .env
   ```
5. Jalankan Docker di latar belakang (Background):
   ```bash
   sudo docker-compose up -d --build
   ```
6. Jalankan Migrasi di container:
   ```bash
   sudo docker exec -it skbu_inventori_app php artisan key:generate
   sudo docker exec -it skbu_inventori_app php artisan migrate --seed
   ```
7. Aplikasi siap diakses melalui *IP Address Server* Anda di port 8000 (Pastikan firewall/security list port 8000 sudah dibuka di panel Cloud).

---

## ☁️ 3. Deploy Docker ke Layanan Serverless (Google Cloud Run / Railway.app)

Karena Anda punya `Dockerfile`, Anda bahkan tidak butuh mengurusi `docker-compose.yml` ataupun VPS. Anda bisa mendangungnya per container!

- **Di Railway.app:** Tinggal koneksikan ke Github, biarkan Railway membaca `Dockerfile`. Tambahkan *Database Service* terpisah, masukkan konfigurai `.env` nya, dan website otomatis tayang!
- **Di Google Cloud Run (Gratis Tiap Bulan 2 Juta Request):** Anda tinggal build Image menggunakan Cloud Build, lalu tayangkan Image tersebut ke Cloud Run. Google akan men-scale server berapapun ramainya trafik Anda.

### Perintah Berguna Mengelola Docker

- **Melihat log (jika terjadi error):** `docker-compose logs -f`
- **Mematikan server Docker:** `docker-compose down`
- **Mereset ulang database Docker total:** `docker-compose down -v`

> **Note:** Aplikasi Anda telah dirancang sangat canggih (Nginx + PHP-FPM + Supervisord di-bundle menjadi 1 image di Alpine OS), jadi file `Dockerfile` tersebut sudah level *Production-ready*!
