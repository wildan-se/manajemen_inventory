# 🌟 Rekomendasi Deployment Gratis Terbaik untuk Laravel 12 (Selain InfinityFree)

InfinityFree memang populer, tetapi batasan tidak adanya **SSH/Terminal** dan fitur terkunci membuatnya sangat menyulitkan bagi *developer* modern yang menggunakan **Laravel, Vite, & Composer**. 

Karena aplikasi SKBU Anda sebenarnya sudah memiliki **`Dockerfile` yang sangat rapi**, Anda siap menggunakan level hosting yang jauh lebih canggih (dan **gratis**) dibanding InfinityFree!

Berikut adalah 3 opsi terbaik yang saya rekomendasikan untuk Anda:

---

## 🥇 Opsi 1: Render.com (App) + TiDB Serverless (Database)
**Level: Profesional (Modern Cloud)** | **Cocok Untuk: Portofolio & CI/CD**

Render adalah platform Cloud (seperti Heroku) yang mendukung deployment berbasis Docker. Karena SKBU sudah punya `Dockerfile`, Anda tinggal *connect* ke akun GitHub Anda, dan Render akan mem-build aplikasi (`npm install` dan `composer install`) secara otomatis!
Namun, Render Web Service versi gratis me-reset database (File System tidak permanen), sehingga Anda mengatur Database di layanan terpisah, yaitu **TiDB Serverless** (kompatibel penuh dengan MySQL 8.0 dan memberi kuota gratis **5GB** selamanya tanpa kartu kredit).

### 🛠️ Kelebihan & Kekurangan:
- ✅ **Auto-Deploy:** Setiap kali Anda `git push` ke GitHub, Render otomatis memperbarui aplikasi Anda (CI/CD).
- ✅ **Tidak pakai FTP Zip:** Tidak perlu lagi upload zip manual.
- ✅ **Database 5GB Kencang:** Dibantu oleh TiDB Serverless.
- ❌ **Cold Start (Tidur):** Web akan "*tertidur*" (sleep) jika tidak diakses dalam 15 menit. Saat diakses pertama kali lagi, web butuh `40-50 detik` untuk loading membangunkan server. Setelah itu, web akan ngebut kembali.

### 📜 Cara Install (Langkah Singkat):
1. Buat repositori baru di GitHub dan upload *source code* SKBU Anda.
2. Daftar di **[TiDB Serverless](https://tidbcloud.com/)**. Buat *Cluster* gratis, salin kredensial Database, User, dan Password.
3. Daftar di **[Render.com](https://render.com/)**, buat **New Web Service**.
4. Hubungkan Render dengan repositori GitHub Anda.
5. Pada pengaturan Render, ketik `Environment Variables` sesuai file `.env` (masukkan `DB_HOST`, `DB_PASSWORD` dari TiDB, dan `APP_KEY`). Jangan lupa beri `ASSET_URL=/public` jika dirasa perlu, namun Render Docker biasanya langsung baca dari Root Public.
6. Klik Deploy! *Build* akan berjalan otomatis di latar belakang.

---

## 🥈 Opsi 2: Serv00.com
**Level: Tradisional tapi Bebas (Shared Hosting dengan Akses Terminal)** | **Cocok untuk: Ingin seperti InfinityFree tapi bebas pakai Command Prompt**

Serv00 adalah *gem* tersembunyi. Layanan dari Eropa ini memberikan Anda *Shared Hosting* gratis 10 Tahun (dengan 3GB SSD), tapi canggihnya: **Anda diberi akses sistem SSH / Terminal**. Artinya gampang jalankan `php artisan migrate` atau `npm run build` langsung dari sana.

### 🛠️ Kelebihan & Kekurangan:
- ✅ **Kapasitas Super:** 3GB Storage RAM-Sharing, include MySQL dan Email gratis.
- ✅ **Bebas Terminal:** Berjalan di OS FreeBSD. PHP & Node.js bisa Anda install dan custom port.
- ❌ **Ping (Latency):** Server di Eropa, jika buka aplikasi dari Indonesia akan butuh `+300ms` tiap halaman. (Tapi karena SPA Turbo di SKBU, ini bisa diminimalisir).
- ❌ **Aturan Login:** Anda wajib login ke panel admin setidaknya 1 kali dalam 3 bulan, jika tidak akun akan ditarik. Kadang registrasi diblokir sementara jika mendeteksi koneksi VPN Indo.

### 📜 Cara Install (Langkah Singkat):
1. Daftar di [Serv00.com](https://www.serv00.com/).
2. Login ke cPanel mereka (DevilWEB), pilih menu **MySQL**, dan buat databasenya.
3. Masuk ke menu **Port**, open port sembarang (misal 8080) jika ingin Node.js, tapi karena SKBU pakai PHP biasa, masuk ke menu **WWW Websites** dan arahkan domain ke folder `/usr/home/[user]/domains/[domain]/public` (Anda bisa mengatur Document Root sendiri, beda dengan InfinityFree).
4. Gunakan terminal (Putty/SSH) untuk login ke server.
5. Clone repository Github. Jalankan `composer install` dan `npm run build`.

---

## 🥉 Opsi 3: Oracle Cloud Free Tier (VPS Asli)
**Level: Dewa / System Administrator** | **Cocok Untuk: Production & Proyek Asli**

Oracle memberikan Anda Virtual Private Server (VPS) **sepenuhnya gratis selamanya**. Bahkan ada opsi ARM (Ampere A1) yang mana Anda akan diberi **24GB RAM dan 4 Core CPU** (setara harga $20-$40/bulan di luaran sana) secara percuma tiap bulan!

### 🛠️ Kelebihan & Kekurangan:
- ✅ **Server Sangat Gahar & Bebas:** Anda menjadi Root / Superadmin. Mau install Docker, cPanel sendiri (CyberPanel), atau Nginx murni, semua bisa! Bisa untuk ratusan ribu visitor.
- ❌ **Registrasi Sulit:** Persyaratan wajib: Harus menggunakan Kartu Kredit atau Kartu Debit dengan logo Visa/Mastercard Asli (seperti Bank Jago / Jenius). Kartu tidak akan ditarik uangnya, *hanya untuk validasi diri*.
- ❌ **Setup Manual Penuh:** Anda benar-benar diberi terminal kosong layar hitam (Ubuntu). Anda sendiri yang harus meng-instal web server, php, dan database persis selayaknya admin IT pro.

---

### 💡 Konklusi dari saya (Antigravity AI):
Jika Anda sekadar ingin aplikasi selalu online untuk portofolio namun kesulitan dengan batas memori, ambil **Render + TiDB** karena Anda tak perlu bayar apapun (tanpa Kartu Kredit) dan langsung belajar menggunakan Docker + GitHub Actions (ini skill sangat laku di industri ketimbang upload lewat Filezilla/FTP manual ala cPanel).

Jika Anda ingin "coba-coba" environment sungguhan dari layar hitam, siapkan Kartu Jenius/Jago dan incar **Oracle Cloud Free Tier**.
