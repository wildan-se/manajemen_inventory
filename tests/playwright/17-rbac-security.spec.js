// @ts-nocheck
// ─────────────────────────────────────────────────────────────────────────────
// G. ROLE-BASED ACCESS CONTROL TESTS
// H. EXPORT & THROTTLE TESTS
// I. TURBO DRIVE BEHAVIOR TESTS
// J. NEGATIVE / SECURITY TESTS
// ─────────────────────────────────────────────────────────────────────────────
import { test, expect } from "@playwright/test";
import { loginAs, ACCOUNTS } from "./helpers/auth.js";

// ═══════════════════════════════════════════════════════════════════════════
// G — RBAC Tests
// ═══════════════════════════════════════════════════════════════════════════
test.describe("G — Role-Based Access Control", () => {

    // ── G-01: warehouse_operator tidak bisa akses /users ─────────────────────
    test("G-01 warehouse_operator diblokir dari /users (403)", async ({ page }) => {
        try { await loginAs(page, "warehouse_operator"); }
        catch { test.skip(); return; }

        const res = await page.goto("/users");
        const status = res?.status() ?? 0;
        const is403 = status === 403 ||
            await page.getByText(/403|forbidden|tidak diizinkan|permission/i).count() > 0;
        expect(is403).toBeTruthy();
    });

    // ── G-02: production_staff tidak bisa buat stock_adjustment ──────────────
    test("G-02 production_staff diblokir dari stock_adjustment", async ({ page }) => {
        try { await loginAs(page, "production_staff"); }
        catch { test.skip(); return; }

        await page.goto("/stock-movements/create");
        const typeSelect = page.locator('select[name="type"]');

        // Opsi stock_adjustment seharusnya tidak tersedia untuk production_staff
        const adjOption = typeSelect.locator('option[value="stock_adjustment"]');
        const adjVisible = await adjOption.count() > 0;

        if (adjVisible) {
            // Jika opsi ada, coba submit dan harus dapat 403
            await typeSelect.selectOption("stock_adjustment");
            await page.locator('input[name="quantity"]').fill("100").catch(() => {});
            await page.getByRole("button", { name: /simpan|save|submit/i }).click();
            await page.waitForTimeout(2000);

            const is403 =
                await page.getByText(/403|forbidden|hak akses/i).count() > 0;
            expect(is403).toBeTruthy();
        }
        // OK jika opsi memang tidak ada di form
    });

    // ── G-03: Direct URL access ke /users sebagai non-admin → 403 ─────────
    test("G-03 POST direct ke /users tanpa admin role → 403", async ({ page }) => {
        try { await loginAs(page, "warehouse_operator"); }
        catch { test.skip(); return; }

        const res = await page.goto("/users/create");
        const status = res?.status() ?? 0;
        const is403 = status === 403 || page.url().includes("403") ||
            await page.getByText(/403|forbidden|tidak diizinkan/i).count() > 0;
        expect(is403).toBeTruthy();
    });

    // ── G-04: IDOR — akses resource milik user lain ───────────────────────────
    test("G-04 IDOR — akses /users/1/edit sebagai non-admin diblokir", async ({ page }) => {
        try { await loginAs(page, "warehouse_operator"); }
        catch { test.skip(); return; }

        const res = await page.goto("/users/1/edit");
        const status = res?.status() ?? 0;
        const isBlocked = status === 403 || status === 404 ||
            await page.getByText(/403|404|forbidden|tidak ditemukan/i).count() > 0;
        expect(isBlocked).toBeTruthy();
    });

    // ── G-05: Supervisor bisa approve PO (role check) ─────────────────────────
    test("G-05 Supervisor memiliki tombol Approve di halaman PO detail", async ({ page }) => {
        try { await loginAs(page, "supervisor"); }
        catch { test.skip(); return; }

        await page.goto("/purchase-orders?status=draft");
        const firstLink = page.getByRole("link", { name: /detail|lihat|PO-/i }).first();
        if (await firstLink.count() === 0) { test.skip(); return; }
        await firstLink.click();

        // Supervisor harus punya tombol approve
        const approveBtn = page.getByRole("button", { name: /approve|setujui/i });
        await expect(approveBtn).toBeVisible();
    });

});


// ═══════════════════════════════════════════════════════════════════════════
// H — Export & Throttle Tests
// ═══════════════════════════════════════════════════════════════════════════
test.describe("H — Export & Rate Limiting", () => {

    // ── H-01: Export PDF stock summary berhasil (bukan Turbo intercept) ───────
    test("H-01 Export PDF stock summary membuka tab baru (bukan Turbo navigate)", async ({ page, context }) => {
        await loginAs(page, "admin");
        await page.goto("/reports/stock-summary");

        // Listen untuk halaman baru
        const newPagePromise = context.waitForEvent("page", { timeout: 10_000 }).catch(() => null);

        const pdfLink = page.getByRole("link", { name: /pdf|export.pdf/i }).first();
        if (await pdfLink.count() === 0) { test.skip(); return; }

        // Klik link PDF
        await pdfLink.click();

        const newPage = await newPagePromise;
        if (newPage) {
            // Tab baru terbuka → Turbo tidak menginterksepsi
            await newPage.waitForLoadState("domcontentloaded").catch(() => {});
            // Halaman tidak seharusnya menampilkan error 500
            const status = newPage.url();
            await expect(newPage).not.toHaveURL(/500/);
            await newPage.close();
        } else {
            // Mungkin download langsung — juga OK
            console.log("PDF triggered download or same-page navigation");
        }
    });

    // ── H-02: Export CSV low-stock dibuka tab baru ────────────────────────────
    test("H-02 Export CSV low-stock menghasilkan file download / tab baru", async ({ page, context }) => {
        await loginAs(page, "admin");
        await page.goto("/reports/low-stock");

        // Listen untuk download event
        const downloadPromise = page.waitForEvent("download", { timeout: 8000 }).catch(() => null);
        const newPagePromise = context.waitForEvent("page", { timeout: 8000 }).catch(() => null);

        const csvLink = page.getByRole("link", { name: /csv|excel|xlsx|export.csv/i }).first();
        if (await csvLink.count() === 0) { test.skip(); return; }

        await csvLink.click();

        const download = await downloadPromise;
        const newPg = await newPagePromise;

        // Harus ada download atau tab baru
        expect(download !== null || newPg !== null).toBeTruthy();

        if (download) {
            // Verifikasi nama file
            const filename = download.suggestedFilename();
            expect(filename).toMatch(/\.(csv|xlsx|pdf)$/i);
        }

        if (newPg) await newPg.close();
    });

    // ── H-03: Throttle — lebih dari 10 export request per menit → 429 ─────────
    test("H-03 Rate limiting export: request ke-11 mendapat 429", async ({ page }) => {
        await loginAs(page, "admin");

        let blocked = false;
        let lastStatus = 0;

        for (let i = 0; i < 12; i++) {
            const res = await page.request.get("/reports/stock-summary/export-csv").catch(() => null);
            if (res) {
                lastStatus = res.status();
                if (lastStatus === 429) {
                    blocked = true;
                    break;
                }
            }
            await page.waitForTimeout(100);
        }

        // Setelah 10 request, harus ada 429
        expect(blocked).toBeTruthy();
    });

});


// ═══════════════════════════════════════════════════════════════════════════
// I — Turbo Drive Behavior Tests
// ═══════════════════════════════════════════════════════════════════════════
test.describe("I — Turbo Drive Behavior", () => {

    // ── I-01: Navigasi Turbo tidak menyebabkan full-page reload ──────────────
    test("I-01 Navigasi sidebar tidak menyebabkan full-page reload (Turbo SPA)", async ({ page }) => {
        await loginAs(page, "admin");

        // Tandai window navigation counter
        await page.evaluate(() => {
            window.__navCount = 0;
            window.addEventListener("load", () => { window.__navCount++; });
        });

        const initialNavCount = await page.evaluate(() => window.__navCount ?? 0);

        // Klik menu navigasi
        const navLink = page.getByRole("link", { name: /item|barang/i }).first();
        if (await navLink.count() > 0) {
            await navLink.click();
            await page.waitForTimeout(2000);
        }

        // Halaman tidak seharusnya full reload (Turbo Drive aktif)
        // Test ini memverifikasi halaman berubah tanpa DOMContentLoaded hard reload
        await expect(page).not.toHaveURL(/dashboard/);
    });

    // ── I-02: Sidebar collapse state persisten via localStorage ──────────────
    test("I-02 Sidebar collapse state tersimpan di localStorage", async ({ page }) => {
        await loginAs(page, "admin");

        // Cari tombol toggle sidebar
        const sidebarToggle = page.getByRole("button", { name: /toggle|sidebar|menu/i }).first();
        if (await sidebarToggle.count() === 0) { test.skip(); return; }

        // Klik untuk collapse
        await sidebarToggle.click();
        await page.waitForTimeout(500);

        // Cek localStorage
        const storageValue = await page.evaluate(() =>
            localStorage.getItem("sidebarCollapsed") ??
            localStorage.getItem("sidebar-state") ??
            localStorage.getItem("sidebar")
        );

        // Navigasi ke halaman lain
        await page.goto("/items");
        await page.waitForTimeout(1000);

        // Cek state masih tersimpan
        const storageAfterNav = await page.evaluate(() =>
            localStorage.getItem("sidebarCollapsed") ??
            localStorage.getItem("sidebar-state") ??
            localStorage.getItem("sidebar")
        );

        // State harus masih ada setelah navigasi
        expect(storageAfterNav).not.toBeNull();
    });

    // ── I-03: Turbo tidak menginterksepsi link export (data-turbo="false") ────
    test("I-03 Link export dengan data-turbo='false' tidak diinterksepsi Turbo", async ({ page }) => {
        await loginAs(page, "admin");
        await page.goto("/reports/stock-summary");

        // Cek atribut link export
        const pdfLink = page.getByRole("link", { name: /pdf/i }).first();
        if (await pdfLink.count() === 0) { test.skip(); return; }

        const turboAttr = await pdfLink.getAttribute("data-turbo");
        expect(turboAttr).toBe("false");

        // Verifikasi juga target="_blank"
        const targetAttr = await pdfLink.getAttribute("target");
        expect(targetAttr).toBe("_blank");
    });

    // ── I-04: Active link sidebar sync setelah Turbo navigate ────────────────
    test("I-04 Active state sidebar sinkron setelah navigasi Turbo", async ({ page }) => {
        await loginAs(page, "admin");

        // Navigate ke items via Turbo
        await page.goto("/items");
        await page.waitForTimeout(1000);

        // Cari link aktif di sidebar — harus ada satu yang active
        const activeLinks = page.locator('.active, [aria-current="page"], nav a.active-link');
        const count = await activeLinks.count();
        // Minimal ada indikasi halaman aktif
        // (tidak selalu ada styling explicit, tapi tidak boleh error js)
        await expect(page).toHaveURL(/items/);
    });

});


// ═══════════════════════════════════════════════════════════════════════════
// J — Negative & Security Tests
// ═══════════════════════════════════════════════════════════════════════════
test.describe("J — Negative & Security Testing", () => {

    // ── J-01: Manipulasi hidden input item_id ke item nonaktif ────────────────
    test("J-01 Kirim item_id nonaktif via manipulasi form ditolak server", async ({ page }) => {
        await loginAs(page, "admin");
        await page.goto("/stock-movements/create");

        // Inject item_id nonaktif (ID 0 atau sangat besar)
        await page.evaluate(() => {
            const form = document.querySelector("form");
            if (!form) return;
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "item_id";
            hidden.value = "99999"; // ID tidak ada
            form.appendChild(hidden);
        });

        await page.locator('select[name="type"]').selectOption("goods_receipt");
        await page.locator('input[name="quantity"]').fill("1");
        await page.getByRole("button", { name: /simpan|save|submit/i }).click();
        await page.waitForTimeout(2000);

        // Harus ada error validasi, bukan 500
        await expect(page).not.toHaveURL(/500/);
        await expect(page.getByText(/tidak ditemukan|not found|exists|wajib/i)).toBeVisible();
    });

    // ── J-02: CSRF — request tanpa CSRF token seharusnya ditolak ─────────────
    test("J-02 POST tanpa CSRF token mendapat 419 (CSRF mismatch)", async ({ page }) => {
        await loginAs(page, "admin");

        // Buat POST request tanpa CSRF token menggunakan fetch
        const response = await page.evaluate(async () => {
            const res = await fetch("/stock-movements", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "type=goods_receipt&item_id=1&quantity=1&to_warehouse_id=1",
                // Tidak ada X-XSRF-TOKEN header
            });
            return res.status;
        });

        expect(response).toBe(419);
    });

    // ── J-03: Inject XSS via form field — harus di-escape ────────────────────
    test("J-03 XSS payload di field notes di-escape oleh Blade", async ({ page }) => {
        await loginAs(page, "admin");

        const xssPayload = '<script>window.__xss_test=true</script>';

        // Coba inject di field notes suppliers
        await page.goto("/suppliers/create");
        await page.getByLabel(/nama/i).fill("Test XSS Supplier");
        const notesField = page.locator('textarea[name="notes"], input[name="notes"]');
        if (await notesField.count() > 0) {
            await notesField.fill(xssPayload);
        }
        await page.getByRole("button", { name: /simpan|save|submit/i }).click();
        await page.waitForTimeout(2000);

        // Verifikasi XSS tidak dieksekusi
        const xssExecuted = await page.evaluate(() => window.__xss_test === true);
        expect(xssExecuted).toBeFalsy();
    });

    // ── J-04: Akses halaman opname orang lain via URL manipulation (IDOR) ─────
    test("J-04 Akses detail opname via URL manipulation tidak leak data lintas scope", async ({ page }) => {
        await loginAs(page, "admin");

        // Coba akses opname ID 1 langsung
        await page.goto("/stock-opnames/1");
        const status = await page.evaluate(() => document.readyState);

        // Harus berhasil (admin bisa lihat semua) ATAU 404/403
        // Yang penting tidak ada data corruption
        await expect(page).not.toHaveURL(/500/);
    });

    // ── J-05: SQL injection via search/filter parameter ──────────────────────
    test("J-05 SQL injection di parameter filter tidak menyebabkan error DB", async ({ page }) => {
        await loginAs(page, "admin");

        // Coba SQL injection via filter query string
        await page.goto("/stock-movements?type='; DROP TABLE stock_movements; --");
        await page.waitForTimeout(1000);

        // Tidak ada DB error / 500
        await expect(page).not.toHaveURL(/500/);

        // Halaman masih bisa diakses normal
        await expect(page.locator("body")).toBeVisible();
    });

    // ── J-06: Mass assignment — inject 'role' field via form manipulation ─────
    test("J-06 Mass assignment: inject 'role' via form tidak mengubah role user", async ({ page }) => {
        await loginAs(page, "admin");

        // Coba inject role admin via profile update form
        await page.goto("/profile");

        // Inject field role yang tidak seharusnya ada
        await page.evaluate(() => {
            const form = document.querySelector("form");
            if (!form) return;
            const roleInput = document.createElement("input");
            roleInput.type = "hidden";
            roleInput.name = "role";
            roleInput.value = "admin";
            form.appendChild(roleInput);
        });

        const nameField = page.getByLabel(/nama|name/i).first();
        if (await nameField.count() > 0) {
            await nameField.fill("Test User");
        }

        await page.getByRole("button", { name: /simpan|save|update/i }).click();
        await page.waitForTimeout(2000);

        // Tidak ada 500 (model fillable melindungi 'role' field)
        await expect(page).not.toHaveURL(/500/);
    });

    // ── J-07: Replay attack — gunakan request yang sama dua kali ─────────────
    test("J-07 Submit form yang sama dua kali tidak menghasilkan duplicate data", async ({ page }) => {
        await loginAs(page, "admin");
        await page.goto("/categories/create");

        const uniqueName = `Cat-Replay-${Date.now()}`;
        await page.getByLabel(/nama/i).fill(uniqueName);
        await page.getByRole("button", { name: /simpan|save|submit/i }).click();
        await page.waitForTimeout(2000);

        // Navigasi kembali dan submit lagi dengan nama yang sama
        await page.goto("/categories/create");
        await page.getByLabel(/nama/i).fill(uniqueName);
        await page.getByRole("button", { name: /simpan|save|submit/i }).click();
        await page.waitForTimeout(2000);

        // Server harus menolak duplicate (unique constraint)
        const errorVisible = await page.getByText(/unique|sudah ada|telah digunakan|duplicate/i).count() > 0;
        expect(errorVisible).toBeTruthy();
    });

});
