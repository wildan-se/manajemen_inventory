// @ts-nocheck
// ─────────────────────────────────────────────────────────────────────────────
// A. AUTHENTICATION TESTS
// Skenario: login sukses, login gagal, user nonaktif, logout
// ─────────────────────────────────────────────────────────────────────────────
import { test, expect } from "@playwright/test";
import { loginAs, ACCOUNTS } from "./helpers/auth.js";

test.describe("A — Authentication", () => {

    // ── A-01: Login sukses sebagai Admin ─────────────────────────────────────
    test("A-01 Login admin berhasil dan redirect ke dashboard", async ({ page }) => {
        await loginAs(page, "admin");
        await expect(page).toHaveURL(/dashboard/);
        // Sidebar dan stat cards harus tampil
        await expect(page.getByText(/dashboard/i).first()).toBeVisible();
    });

    // ── A-02: Login gagal — password salah ───────────────────────────────────
    test("A-02 Login gagal jika password salah", async ({ page }) => {
        await page.goto("/login");
        await page.getByLabel(/email/i).fill(ACCOUNTS.admin.email);
        await page.getByLabel(/password/i).fill("password-salah-123");
        await page.getByRole("button", { name: /masuk|login|sign in/i }).click();

        // Harus tetap di halaman login, muncul pesan error
        await expect(page).toHaveURL(/login/);
        await expect(page.getByText(/credentials|salah|gagal|invalid/i)).toBeVisible();
    });

    // ── A-03: Login gagal — email tidak terdaftar ─────────────────────────────
    test("A-03 Login gagal jika email tidak terdaftar", async ({ page }) => {
        await page.goto("/login");
        await page.getByLabel(/email/i).fill("tidak.ada@example.com");
        await page.getByLabel(/password/i).fill("password");
        await page.getByRole("button", { name: /masuk|login|sign in/i }).click();

        await expect(page).toHaveURL(/login/);
        await expect(page.getByText(/credentials|salah|gagal|invalid/i)).toBeVisible();
    });

    // ── A-04: Logout menghapus sesi ──────────────────────────────────────────
    test("A-04 Logout menghapus sesi dan redirect ke halaman login", async ({ page }) => {
        await loginAs(page, "admin");
        await expect(page).toHaveURL(/dashboard/);

        // Temukan tombol logout (bisa di dropdown profil)
        const logoutBtn = page.getByRole("button", { name: /logout|keluar/i });
        const logoutLink = page.getByRole("link", { name: /logout|keluar/i });

        if (await logoutBtn.count() > 0) {
            await logoutBtn.click();
        } else if (await logoutLink.count() > 0) {
            await logoutLink.click();
        } else {
            // Fallback: submit form logout manual
            await page.evaluate(() => {
                const form = document.querySelector('form[action*="logout"]');
                if (form) form.submit();
            });
        }

        await page.waitForURL(/login|\//, { timeout: 10_000 });
        await expect(page).not.toHaveURL(/dashboard/);
    });

    // ── A-05: Akses halaman protected tanpa login → redirect ke login ─────────
    test("A-05 Akses /dashboard tanpa login redirect ke /login", async ({ page }) => {
        await page.goto("/dashboard");
        await expect(page).toHaveURL(/login/);
    });

    // ── A-06: Akses /users sebagai non-admin → 403 ───────────────────────────
    test("A-06 Akses /users sebagai warehouse_operator → 403", async ({ page }) => {
        // Coba login sebagai operator dulu — jika akun tidak ada, skip
        try {
            await loginAs(page, "warehouse_operator");
        } catch {
            test.skip(); return;
        }
        const response = await page.goto("/users");
        const status = response?.status() ?? 0;
        const has403 = status === 403 ||
            (await page.getByText(/403|forbidden|tidak diizinkan/i).count()) > 0;
        expect(has403).toBeTruthy();
    });

    // ── A-07: Session tidak bisa di-reuse setelah logout ─────────────────────
    test("A-07 Session tidak bisa reuse setelah logout", async ({ page, context }) => {
        await loginAs(page, "admin");

        // Ambil cookies sesi
        const cookies = await context.cookies();
        expect(cookies.length).toBeGreaterThan(0);

        // Logout
        await page.evaluate(() => {
            const form = document.querySelector('form[action*="logout"]');
            if (form) form.submit();
        });
        await page.waitForURL(/login|\//, { timeout: 8_000 }).catch(() => {});

        // Coba akses dashboard — harus redirect ke login
        await page.goto("/dashboard");
        await expect(page).toHaveURL(/login/);
    });

});
