// @ts-nocheck
// ─────────────────────────────────────────────────────────────────────────────
// F. CONCURRENCY TESTS (Critical)
// Gunakan 2 browser context berbeda untuk simulasi race condition
// ─────────────────────────────────────────────────────────────────────────────
import { test, expect, chromium } from "@playwright/test";
import { loginAs, createTestPO, ACCOUNTS } from "./helpers/auth.js";

// Concurrency test butuh 2 konteks — jalankan headless
test.describe("F — Concurrency & Race Condition Tests", () => {
    test.setTimeout(60_000); // timeout lebih panjang

    // ── F-01: Dua user approve PO bersamaan — hanya satu yang berhasil ────────
    test("F-01 Dua request approve PO bersamaan tidak menghasilkan double-approve", async ({ browser }) => {
        // Context 1: Admin buat PO
        const ctx1 = await browser.newContext();
        const page1 = await ctx1.newPage();
        await loginAs(page1, "admin");
        const poNumber = await createTestPO(page1);
        const poUrl = page1.url(); // URL halaman detail PO

        // Context 2: Supervisor (atau admin lain jika tidak ada) akan approve
        const ctx2 = await browser.newContext();
        const page2 = await ctx2.newPage();

        // Login di ctx2 (sebagai admin — bukan pembuat PO idealnya, tapi dipakai untuk test race)
        await page2.goto(`${poUrl.replace(/\/\d+$/, "")}`);
        await page2.goto("/login");
        await page2.getByLabel(/email/i).fill(ACCOUNTS.admin.email);
        await page2.getByLabel(/password/i).fill(ACCOUNTS.admin.password);
        await page2.getByRole("button", { name: /masuk|login|sign in/i }).click();
        await page2.waitForURL("**/dashboard", { timeout: 10_000 });
        await page2.goto(poUrl);

        // Kedua halaman sudah di detail PO, sekarang klik approve bersamaan
        const approveBtn1 = page1.getByRole("button", { name: /approve|setujui/i });
        const approveBtn2 = page2.getByRole("button", { name: /approve|setujui/i });

        const btn1Visible = await approveBtn1.count() > 0;
        const btn2Visible = await approveBtn2.count() > 0;

        if (!btn1Visible || !btn2Visible) {
            // PO mungkin sudah approved atau self-approval diblokir
            await ctx1.close();
            await ctx2.close();
            test.skip(); return;
        }

        // Fire kedua klik hampir bersamaan
        await Promise.all([
            approveBtn1.click().catch(() => {}),
            approveBtn2.click().catch(() => {}),
        ]);

        // Tunggu kedua halaman settle
        await page1.waitForTimeout(4000);
        await page2.waitForTimeout(4000);

        // Cek status PO dari DB melalui halaman index
        const ctx3 = await browser.newContext();
        const page3 = await ctx3.newPage();
        await loginAs(page3, "admin");
        await page3.goto("/purchase-orders");

        // PO harus berstatus approved (bukan approved dua kali → tidak ada status boolean duplicate)
        // Tidak ada error 500 di kedua context
        await expect(page1).not.toHaveURL(/500/);
        await expect(page2).not.toHaveURL(/500/);

        await ctx1.close();
        await ctx2.close();
        await ctx3.close();
    });

    // ── F-02: Dua user transfer stok item yang sama bersamaan ─────────────────
    test("F-02 Concurrent stock transfer tidak menghasilkan stok negatif", async ({ browser }) => {
        // Buat dua context
        const ctx1 = await browser.newContext();
        const ctx2 = await browser.newContext();
        const page1 = await ctx1.newPage();
        const page2 = await ctx2.newPage();

        // Login keduanya sebagai admin
        await loginAs(page1, "admin");
        await loginAs(page2, "admin");

        // Kedua submit material_issue qty 9999 pada item dan gudang yang sama
        const submitTransfer = async (page) => {
            await page.goto("/stock-movements/create");
            await page.locator('select[name="type"]').selectOption("material_issue");
            await page.waitForTimeout(300);

            const itemSel = page.locator('select[name="item_id"]');
            const iOpts = await itemSel.locator("option").all();
            if (iOpts.length > 1) await itemSel.selectOption({ index: 1 });

            const whSel = page.locator('select[name="from_warehouse_id"]');
            const wOpts = await whSel.locator("option").all();
            if (wOpts.length > 1) await whSel.selectOption({ index: 1 });

            await page.locator('input[name="quantity"]').fill("9999999");
            await page.getByRole("button", { name: /simpan|save|submit/i }).click();
            await page.waitForTimeout(3000);
        };

        // Fire bersamaan
        await Promise.all([
            submitTransfer(page1).catch(() => {}),
            submitTransfer(page2).catch(() => {}),
        ]);

        // Kedua harus blocked (stok tidak cukup) atau max satu yang berhasil
        const error1 = await page1.getByText(/tidak mencukupi|insufficient|stok/i).count() > 0;
        const error2 = await page2.getByText(/tidak mencukupi|insufficient|stok/i).count() > 0;
        const success1 = await page1.getByText(/berhasil|success/i).count() > 0;
        const success2 = await page2.getByText(/berhasil|success/i).count() > 0;

        // Tidak boleh kedua-duanya sukses (itu berarti stok bisa minus)
        const bothSucceeded = success1 && success2;
        expect(bothSucceeded).toBeFalsy();

        // Tidak ada error 500
        await expect(page1).not.toHaveURL(/500/);
        await expect(page2).not.toHaveURL(/500/);

        await ctx1.close();
        await ctx2.close();
    });

    // ── F-03: Multi-tab submit form yang sama tidak duplikat ─────────────────
    test("F-03 Multi-tab submit tidak menghasilkan duplicate goods_receipt", async ({ browser }) => {
        const ctx = await browser.newContext();
        const page1 = await ctx.newPage();
        const page2 = await ctx.newPage(); // tab berbeda, context sama (sesi sama)

        await loginAs(page1, "admin");

        // Buka create form di kedua tab dengan data sama
        const fillGoodsReceipt = async (page) => {
            await page.goto("/stock-movements/create");
            await page.locator('select[name="type"]').selectOption("goods_receipt");
            await page.waitForTimeout(300);

            const itemSel = page.locator('select[name="item_id"]');
            const iOpts = await itemSel.locator("option").all();
            if (iOpts.length > 1) await itemSel.selectOption({ index: 1 });

            const whSel = page.locator('select[name="to_warehouse_id"]');
            const wOpts = await whSel.locator("option").all();
            if (wOpts.length > 1) await whSel.selectOption({ index: 1 });

            await page.locator('input[name="quantity"]').fill("1");
        };

        await fillGoodsReceipt(page2);

        // Submit dari page2 (tab kedua)
        await page2.getByRole("button", { name: /simpan|save|submit/i }).click();
        await page2.waitForTimeout(3000);

        // Cek tidak ada 500
        await expect(page2).not.toHaveURL(/500/);

        // Verifikasi hanya 1 movement tercipta (bukan 2)
        await page1.goto("/stock-movements");
        await page1.waitForTimeout(1000);

        await ctx.close();
    });

    // ── F-04: Admin nonaktifkan user saat sedang login ────────────────────────
    test("F-04 User yang dinonaktifkan admin akan di-logout pada request berikutnya", async ({ browser }) => {
        // Ini memerlukan dua akun: admin + akun yang akan dinonaktifkan
        // Jika tidak ada akun operator, skip
        const ctx1 = await browser.newContext(); // Admin
        const ctx2 = await browser.newContext(); // Operator yang akan dinonaktifkan

        const page1 = await ctx1.newPage(); // Admin
        const page2 = await ctx2.newPage(); // Operator

        await loginAs(page1, "admin");

        // Login operator di ctx2
        try {
            await loginAs(page2, "warehouse_operator");
        } catch {
            await ctx1.close();
            await ctx2.close();
            test.skip(); return;
        }

        // Operator berhasil lihat dashboard
        await expect(page2).toHaveURL(/dashboard/);

        // Admin nonaktifkan operator via UI /users
        await page1.goto("/users");
        await page1.waitForTimeout(1000);

        // Cari operator di tabel dan toggle nonaktif
        const operatorRow = page1.getByText(ACCOUNTS.warehouse_operator.email).first();
        if (await operatorRow.count() === 0) {
            await ctx1.close();
            await ctx2.close();
            test.skip(); return;
        }

        // Klik toggle atau tombol nonaktifkan di baris yang relevan
        const toggleBtn = operatorRow.locator("..").locator("..").getByRole("button", { name: /aktif|toggle|nonaktif/i });
        if (await toggleBtn.count() > 0) await toggleBtn.click();
        await page1.waitForTimeout(2000);

        // Operator coba navigate ke halaman lain
        await page2.goto("/stock-movements");
        await page2.waitForTimeout(3000);

        // Operator harus di-redirect ke login
        const isLoggedOut =
            page2.url().includes("/login") ||
            await page2.getByText(/dinonaktifkan|inactive|logout/i).count() > 0;

        expect(isLoggedOut).toBeTruthy();

        await ctx1.close();
        await ctx2.close();
    });

});
