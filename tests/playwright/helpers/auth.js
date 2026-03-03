// @ts-nocheck
// Auth helpers and fixtures for Playwright E2E tests

import { test as base, expect } from "@playwright/test";

// ─── Akun per Role ───────────────────────────────────────────────────────────
export const ACCOUNTS = {
    admin: { email: "admin@skbu.com", password: "password" },
    inventory_controller: { email: "ic@skbu.com", password: "password" },
    supervisor: { email: "supervisor@skbu.com", password: "password" },
    warehouse_operator: { email: "operator@skbu.com", password: "password" },
    production_staff: { email: "prodstaff@skbu.com", password: "password" },
};

/**
 * Login ke aplikasi lalu tunggu redirect ke dashboard.
 * @param {import('@playwright/test').Page} page
 * @param {'admin'|'inventory_controller'|'supervisor'|'warehouse_operator'|'production_staff'} role
 */
export async function loginAs(page, role = "admin") {
    const { email, password } = ACCOUNTS[role] ?? ACCOUNTS.admin;
    await page.goto("/login");
    await page.getByLabel(/email/i).fill(email);
    await page.getByLabel(/password/i).fill(password);
    await page.getByRole("button", { name: /masuk|login|sign in/i }).click();
    await page.waitForURL("**/dashboard", { timeout: 10_000 });
}

/**
 * Buat item master data via UI.
 * Mengembalikan nama item yang dibuat.
 * @param {import('@playwright/test').Page} page
 */
export async function createTestItem(page, suffix = Date.now()) {
    const name = `Test Item ${suffix}`;
    await page.goto("/items/create");
    await page.getByLabel(/nama/i).fill(name);
    // kode item — isi jika ada
    const codeInput = page.locator('input[name="code"]');
    if ((await codeInput.count()) > 0) await codeInput.fill(`ITM-${suffix}`);
    // pilih kategori pertama tersedia
    const catSel = page.locator('select[name="category_id"]');
    if ((await catSel.count()) > 0) {
        const opts = await catSel.locator("option").all();
        if (opts.length > 1) await catSel.selectOption({ index: 1 });
    }
    // pilih satuan pertama tersedia
    const unitSel = page.locator('select[name="unit_id"]');
    if ((await unitSel.count()) > 0) {
        const opts = await unitSel.locator("option").all();
        if (opts.length > 1) await unitSel.selectOption({ index: 1 });
    }
    await page.getByRole("button", { name: /simpan|save|submit/i }).click();
    await expect(page.getByText(/berhasil|success/i)).toBeVisible({
        timeout: 8_000,
    });
    return name;
}

/**
 * Buat Purchase Order draft via UI.
 * Mengembalikan nomor PO yang dibuat (dari text halaman).
 * @param {import('@playwright/test').Page} page
 */
export async function createTestPO(page) {
    await page.goto("/purchase-orders/create");

    // Supplier
    const supplierSel = page.locator('select[name="supplier_id"]');
    const sOpts = await supplierSel.locator("option").all();
    if (sOpts.length <= 1) return "";
    await supplierSel.selectOption({ index: 1 });

    // Warehouse
    const whSel = page.locator('select[name="warehouse_id"]');
    const wOpts = await whSel.locator("option").all();
    if (wOpts.length > 1) await whSel.selectOption({ index: 1 });

    // Tanggal order
    const orderDateInput = page.locator('input[name="order_date"]');
    if (await orderDateInput.count() > 0) await orderDateInput.fill("2026-03-01");

    // Item baris pertama — format items[0][item_id]
    const itemSel = page.locator('select[name="items[0][item_id]"]');
    const iOpts = await itemSel.locator("option").all();
    if (iOpts.length > 1) await itemSel.selectOption({ index: 1 });

    // Quantity dan harga
    const qtyInput = page.locator('input[name="items[0][quantity]"]');
    if (await qtyInput.count() > 0) await qtyInput.fill("10");

    const priceInput = page.locator('input[name="items[0][unit_price]"]');
    if (await priceInput.count() > 0) await priceInput.fill("5000");

    await page.getByRole("button", { name: /simpan|save|submit/i }).click();
    await page.waitForURL(/purchase-orders\/\d+/, { timeout: 15_000 });

    // Ambil nomor PO dari halaman
    const poText = await page.getByText(/PO-\d{6}-\d{4}/i).first().textContent().catch(() => "");
    return poText.trim();
}

// ─── Custom fixture: halaman sudah ter-autentikasi sebagai admin ──────────────
const test = base.extend({
    authPage: async ({ page }, use) => {
        await loginAs(page, "admin");
        await use(page);
    },
});

export { test };
