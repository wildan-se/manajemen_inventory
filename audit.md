Lakukan audit keamanan komprehensif terhadap aplikasi web manajemen inventori berbasis Laravel 11 (PHP 8.2, MySQL, Blade, Tailwind, Vite).

Konteks Sistem:
Aplikasi digunakan oleh perusahaan untuk mengelola supply chain internal, termasuk gudang, stok, procurement (Purchase Order), produksi (Work Order), mutasi barang, dan laporan.

Fitur utama:
- Role-Based Access Control (Administrator & Staff Gudang)
- Multi-warehouse inventory
- Stock movements (In/Out/Transfer)
- Stock opname & variance
- Purchase Orders dengan approval flow
- Work Orders produksi internal
- Reporting & audit trail
- Toggle active/inactive pada master data dan user
- Soft delete untuk menjaga histori
- Session-based authentication Laravel
- Asynchronous toast notifications & modals
- DataTables reporting

Tujuan audit:
Mengidentifikasi kerentanan keamanan yang dapat menyebabkan:
- Kebocoran data perusahaan
- Manipulasi stok atau transaksi
- Privilege escalation
- Akses tidak sah antar role
- Penyalahgunaan business logic
- Gangguan operasional

Analisis harus mengacu pada OWASP Top 10 terbaru dan praktik keamanan aplikasi enterprise modern.

Evaluasi secara mendalam pada area berikut:

1. Authentication & Session Security
- Keamanan login Laravel (hashing, brute force protection)
- Session fixation / hijacking
- Cookie security (HttpOnly, Secure, SameSite)
- Logout behavior & session invalidation
- Proteksi terhadap akun inactive yang masih memiliki session aktif

2. Authorization & RBAC Integrity
- Validasi role pada server-side (bukan hanya UI)
- Privilege escalation antar Administrator dan Staff
- Insecure Direct Object Reference (IDOR)
- Akses lintas gudang tanpa izin
- Proteksi endpoint sensitif (Users Management, Master Data)

3. Business Logic Vulnerabilities (KRITIS untuk sistem inventori)
- Manipulasi stok melalui mutasi ilegal
- Double submission / race condition pada transaksi stok
- Bypass approval flow Purchase Order
- Produksi tanpa ketersediaan bahan baku
- Penghapusan atau modifikasi histori transaksi
- Abuse terhadap toggle active/inactive untuk menghindari kontrol sistem

4. Data Integrity & Database Security
- SQL Injection risk
- Konsistensi perhitungan stok berbasis mutasi
- Risiko data corruption akibat concurrent transactions
- Validasi foreign key & referential integrity
- Backup security

5. Input Validation & Injection
- XSS pada form input dan DataTables
- Stored XSS pada master data (supplier, item, dll)
- File upload attack surface (jika ada)
- Command injection atau template injection

6. API & AJAX Security
- CSRF protection pada semua request state-changing
- Validasi endpoint asynchronous
- Data exposure via JSON responses
- Rate limiting

7. Sensitive Data Protection
- Ekspos data harga, supplier, margin, atau internal info
- Leakage via error messages atau debug mode
- Penyimpanan secret di source code
- Logging yang mengandung data sensitif

8. Frontend & Client-Side Risks
- Manipulasi request melalui DevTools
- Bypass validasi client-side
- Penyalahgunaan DataTables parameters
- Risiko localStorage/sessionStorage

9. Infrastructure & Deployment Security
- HTTPS enforcement
- Security headers (CSP, HSTS, X-Frame-Options, dll)
- Laravel configuration (APP_DEBUG, APP_KEY)
- Dependency vulnerabilities (Composer & NPM)
- Server misconfiguration

10. Audit Trail & Monitoring
- Apakah semua aksi penting tercatat
- Kemampuan mendeteksi aktivitas mencurigakan
- Proteksi terhadap penghapusan log

Output yang diharapkan:

A. Daftar kerentanan berdasarkan tingkat risiko:
   - Critical (dapat menyebabkan manipulasi stok atau akses admin)
   - High
   - Medium
   - Low

B. Untuk setiap temuan berikan:
   - Deskripsi kerentanan
   - Dampak bisnis pada sistem inventori
   - Contoh skenario eksploitasi realistis
   - Rekomendasi mitigasi spesifik untuk Laravel

C. Berikan juga:
   - Daftar prioritas perbaikan (Top Security Fixes)
   - Quick wins dengan dampak besar
   - Risiko terbesar bagi sistem supply chain internal

Jika detail implementasi tidak tersedia, gunakan asumsi praktik umum Laravel dan sebutkan asumsi tersebut.
Fokus pada kerentanan yang realistis, bukan teoritis.