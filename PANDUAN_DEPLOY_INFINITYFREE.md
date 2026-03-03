# 🚀 Panduan Lengkap Deploy SKBU Laravel ke InfinityFree



InfinityFree (dan panel hosting gratis lainnya) memiliki beberapa pembatasan:

- TIdak memiliki akses terminal/SSH untuk menjalankan perintah `composer` atau `php artisan`.

- Root direktori web (document root) dikunci ke folder `htdocs` dan tidak bisa diubah ke folder `public`.



Oleh karena itu, ada beberapa langkah khusus yang harus Anda ikuti setiap kali ingin mempublikasikan aplikasi ini!



---



## 📦 Langkah 1: Persiapan File ZIP (Build Otomatis)



Karena tidak bisa build di InfinityFree, Anda **harus membangun aplikasi lokal Anda** terlebih dahulu. Kami telah membuatkan script otomatis untuk Anda:



1. Buka folder `manajemen-inventori` di Windows Explorer.

2. Cari file `deploy_infinityfree.bat` dan **klik dua kali (double click)** pada file tersebut.

3. Tunggu jendela Command Prompt menyelesaikan prosesnya (Download vendor PHP, NPM, dan Zipping).

4. Jika tulisan **SELESAI!** muncul, Anda akan melihat file baru bernama `skbu_infinityfree.zip` (kurang lebih ukurannya sekitar 30MB-45MB).



> **Catatan:** Jangan bagikan file ini sembarangan, karena di dalamnya terdapat source code dan sisa credentials lama Anda.



---



## ☁️ Langkah 2: Upload File ke InfinityFree



1. Login ke Client Area InfinityFree dan masuk ke **Control Panel (cPanel)**.

2. Pilih fitur **File Manager**.

3. Buka folder `htdocs`. Jika ada file bawaan (seperti `index2.html` dsb), **hapus semuanya** agar `htdocs` kosong bersih.

4. Upload file `skbu_infinityfree.zip` yang baru saja Anda buat ke dalam `htdocs`.

5. Setelah selesai di-upload, klik kanan pada file zip tersebut dan pilih menu **Extract**.

6. Jika file `.htaccess` di-extract dengan benar, Anda bisa langsung lanjut ke Langkah 3.



---



## ⚙️ Langkah 3: Konfigurasi `.env` 



Laravel tidak akan berjalan jika Anda tidak punya file `.env`. 



1. Di dalam File Manager `htdocs`, buat file baru bernama `.env`. (Atau duplikasi file `.env.example` lalu ubah namanya menjadi `.env`).

2. Buka dan edit file `.env` tersebut. Isi dengan panduan berikut:



```env

# Sesuaikan dengan nama aplikasi Anda

APP_NAME="SKBU Inventori"

# PASTIKAN APP_ENV ke production dan APP_DEBUG false AGAR ERROR TIDAK MUNCUL DI PUBLIK!

APP_ENV=production

APP_DEBUG=false

# Ubah dengan URL domain infinityfree Anda, contoh: http://namadomain.epizy.com

APP_URL=http://TULIS_NAMA_DOMAIN_ANDA_DISINI.epizy.com



# 🌟 PENANGANAN ERROR CSS & JS PADA INFINITYFREE (SANGAT PENTING!)

# Agar halaman Vite bisa terbuka dengan lancar karena document root berada di htdocs:

ASSET_URL=/public



# Data koneksi Database dari Control Panel InfinityFree

DB_CONNECTION=mysql

DB_HOST=sql10X.epizy.com  # Sesuaikan Host dari "MySQL Databases" di cPanel

DB_PORT=3306

DB_DATABASE=epiz_123456_namadb

DB_USERNAME=epiz_123456

DB_PASSWORD=passwordanDA123



# Driver lainnya

SESSION_DRIVER=database

SESSION_LIFETIME=120

CACHE_STORE=file

QUEUE_CONNECTION=database

```



---



## 🗄️ Langkah 4: Setup Database Terakhir



1. Di cPanel InfinityFree, cari menu **MySQL Databases**.

2. Buat database baru. Catat dan salin nama **Host, Database, User, dan Password Database** Anda ke file `.env` yang tadi Anda isi (di Langkah 3).

3. Kembali ke local (komputer Anda), buka **phpMyAdmin** lokal. Pilih database `db_skbu_inventori` dan lakukan **Export** ke format `.sql`.

4. Kembali ke InfinityFree, buka **phpMyAdmin** dari cPanel, lalu pilih Database Anda.

5. Klik menu **Import** dan pilih file `.sql` yang tadi Anda download.

6. Selesai! Aplikasi SKBU sekarang sudah bisa diakses melalui domain Anda.



---



## 💡 Tips & Masalah Umum Pada InfinityFree



- **Tampilan CSS/JS Pecah atau Blank Page:** Ini biasanya karena Vite gagal membaca alamat folder karena menggunakan `.htaccess` redirect. Pastikan `ASSET_URL=/public` telah direkam di `.env`.

- **500 Internal Server Error:** Periksa kembali file `.env` Anda. Pastikan nama database atau username dan password terisi valid.

- **File Zip gagal diextract di InfinityFree:** InfinityFree melarang file `.zip` berukuran lebih dari 10MB di File Manager reguler. Apabila ukuran `skbu_infinityfree.zip` lebih dari 10MB, cobalah pecah manual menjadi kecil atau gunakan Client **FileZilla (FTP)**. Namun di banyak kasus, Extract ZIP di Monsta File Manager (bawaan InfinityFree) lebih dapat diandalkan! Saran alternatif lainnya: Anda dapat mengekstrak `vendor.zip` terpisah dengan file lainnya.

