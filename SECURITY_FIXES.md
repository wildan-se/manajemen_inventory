# Laporan Perbaikan Keamanan Sistem (Security Fixes Report)

Sesuai dengan kerentanan krusial yang diidentifikasi dari rencana audit keamanan, berikut adalah seluruh daftar perbaikan yang telah diemplementasikan secara komprehensif pada sistem inventori:

## 1. Perbaikan Race Condition pada Kalkulasi Stok (Kritis)
**Masalah:** Saat ada banyak permintaan transaksi dalam waktu yang sama persis (misal: multi-user atau double-click), stok akhir dapat terjadi korupsi karena kueri `select` membaca data stok yang sama sebelum sempat ter-`update`.
**Solusi:**
- Diimplementasikan **Pessimistic Locking** (`lockForUpdate()`) di seluruh transaksi `StockService.php` (Goods Receipt, Material Issue, Stock Transfer, Stock Adjustment).
- Laravel akan mengunci baris stok terkait di level database (MySQL InnoDB) selama siklus transaksi berlangsung, memastikan operasi *math* (tambah/kurang) selalu menggunakan kuantitas stok yang mutakhir dan mencegah nilai negatif.

## 2. Insecure Direct Object Reference (IDOR) & Bypass Alur Approval (Kritis)
**Masalah:** Pengguna standar dapat mengubah ID dari URL atau menggunakan tool seperti Postman untuk membypass alur `PurchaseOrder` (misal: memanggil `/purchase-orders/1/approve` sebagai operator gudang biasa) atau `ProductionOrder`.
**Solusi:**
- **Penerapan Policy & Gate Authorization**: Menambahkan `PurchaseOrderPolicy` dan `ProductionOrderPolicy`.
- Fungsi `approve`, `receive`, dan `cancel` pada `PurchaseOrderController` telah dibatasi secara ketat berdasarkan Gate. (Hanya Admin dan Supervisor yang dapat Approve/Cancel).
- Fungsi `start`, `complete`, dan `cancel` pada `ProductionOrderController` juga dibatasi.
- Mencegah manipulasi paksa pada API maupun request AJAX liar (Authorization Exception akan memblokir aksi tersebut).

## 3. Session Fixation & Celah Akun Inaktif (Tinggi)
**Masalah:** Apabila akun dinonaktifkan (`is_active = false`), session dan API token lama bisa jadi masih valid sehingga *malicious user* masih bisa mengeksekusi operasi transaksi diam-diam. Jika akses dilakukan menggunakan API atau AJAX, sistem salah merespon redirect dan tidak efektif.
**Solusi:**
- Memperkuat middleware **EnsureRole**. Jika akun terdeteksi tidak aktif saat beroperasi, session saat ini akan segera dihapus (`invalidate` dan `regenerateToken`), serta `Auth::logout()` langsung dijalankan dengan paksa.
- Middleware ini sekarang men-support environment **API / JSON**. Jika request AJAX, akan melempar kode `403 Forbidden` (`Akses Ditolak. Akun Anda dinonaktifkan.`).

## 4. Perlindungan Mandiri User Controller (Tinggi)
**Masalah:** Seorang administrator yang terotorisasi secara tidak sengaja atau sengaja merubah status `is_active` miliknya sendiri menjadi *false* sehingga mengunci dirinya dari sistem.
**Solusi:**
- **Validasi Back-End Controller**: `UserController` (baik di Web maupun API) secara eksplisit memeriksa identitas pengguna untuk memastikan Administrator tidak dapat me-nonaktifkan akunnya sendiri.
- Validasi ini tidak bergantung pada UI front-end (sehingga sangat aman terhadap modifikasi DOM payload).

## 5. Celah Serangan Bruteforce / Credential Stuffing API (Menengah)
**Masalah:** Form Login web bawaan telah diotorisasi dengan Rate Limit bawaan (menggunakan `App\Http\Requests\Auth\LoginRequest`). Namun perlintasan otentikasi `routes/api.php` tidak dilindungi.
**Solusi:**
- Endpoint API `/login` disematkan middleware `throttle:5,1`, sehingga membatasi akses login brutal ke maksimal 5 percobaan dalam 1 menit per IP Address.
- Filter ini menekan resiko bot attack dari pembobolan password karyawan sistem inventori.

---
### Rekomendasi Selanjutnya untuk Perawatan (Next Steps)
1. Aktifkan koneksi database transaksional (Isolation Level: `READ COMMITTED` atau `REPEATABLE READ`) di *production* MySQL untuk hasil gembok table inventory yang paling optimal.
2. Tambahkan Cloudflare WAF apabila sistem dituju dari akses publik global supaya dapat meminimalisir DoS dari pihak eksternal.
3. Selalu periksa pembagian hak `roles` jika menugaskan staf baru agar tidak salah memberikan role eksklusif `admin` ataupun `supervisor`.
