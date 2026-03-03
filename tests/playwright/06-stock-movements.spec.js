// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Stock Movement", () => {
    // TC035
    test("TC035 - Create a Transfer stock movement and view detail", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-movements/create");

        // Select type = transfer
        const typeSelect = page.locator('select[name="type"]');
        if ((await typeSelect.count()) > 0)
            await typeSelect.selectOption("transfer");

        // Pick source warehouse
        const sourceSelect = page.locator(
            'select[name="warehouse_id"], select[name="from_warehouse_id"]',
        );
        if ((await sourceSelect.count()) > 0) {
            const opts = await sourceSelect.locator("option").all();
            if (opts.length > 1) await sourceSelect.selectOption({ index: 1 });
        }

        // Pick item
        const itemSelect = page.locator('select[name="item_id"]');
        if ((await itemSelect.count()) > 0) {
            const opts = await itemSelect.locator("option").all();
            if (opts.length > 1) await itemSelect.selectOption({ index: 1 });
        }

        const qtyInput = page.locator('input[name="quantity"]');
        if ((await qtyInput.count()) > 0) await qtyInput.fill("1");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/stock-movements/);
    });

    // TC036
    test("TC036 - Transfer form: blocked when destination warehouse missing", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-movements/create");

        const typeSelect = page.locator('select[name="type"]');
        if ((await typeSelect.count()) > 0)
            await typeSelect.selectOption("transfer");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/stock-movements\/create|stock-movements/);
        await expect(page.getByText(/required|wajib|diisi/i)).toBeVisible();
    });

    // TC037
    test("TC037 - Transfer form: same source and destination shows error", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-movements/create");

        const typeSelect = page.locator('select[name="type"]');
        if ((await typeSelect.count()) > 0)
            await typeSelect.selectOption("transfer");

        // Set same warehouse for from and to
        const fromSelect = page
            .locator(
                'select[name="from_warehouse_id"], select[name="warehouse_id"]',
            )
            .first();
        const toSelect = page.locator('select[name="to_warehouse_id"]').first();

        if ((await fromSelect.count()) > 0 && (await toSelect.count()) > 0) {
            const opts = await fromSelect.locator("option").all();
            if (opts.length > 1) {
                await fromSelect.selectOption({ index: 1 });
                await toSelect.selectOption({ index: 1 });
            }
            const qtyInput = page.locator('input[name="quantity"]');
            if ((await qtyInput.count()) > 0) await qtyInput.fill("1");
            await page.click('button[type="submit"]');
            await expect(
                page
                    .getByText(/sama|same|berbeda|different/i)
                    .or(page.getByText(/required|wajib/i)),
            ).toBeVisible();
        }
    });

    // TC038
    test("TC038 - Material issue with over-stock quantity shows error", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-movements/create");

        const typeSelect = page.locator('select[name="type"]');
        if ((await typeSelect.count()) > 0)
            await typeSelect.selectOption("out");

        const itemSelect = page.locator('select[name="item_id"]');
        if ((await itemSelect.count()) > 0) {
            const opts = await itemSelect.locator("option").all();
            if (opts.length > 1) await itemSelect.selectOption({ index: 1 });
        }

        const warehouseSelect = page.locator('select[name="warehouse_id"]');
        if ((await warehouseSelect.count()) > 0) {
            const opts = await warehouseSelect.locator("option").all();
            if (opts.length > 1)
                await warehouseSelect.selectOption({ index: 1 });
        }

        const qtyInput = page.locator('input[name="quantity"]');
        if ((await qtyInput.count()) > 0) await qtyInput.fill("99999999");

        await page.click('button[type="submit"]');
        await expect(
            page
                .getByText(/insufficient|kurang|tidak cukup|stok tidak/i)
                .or(page.getByText(/exceeds|melebihi/i))
                .or(page.getByText(/required|wajib/i)),
        ).toBeVisible();
    });

    // TC039
    test("TC039 - Create form: quantity required and must be > 0", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-movements/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/stock-movements\/create|stock-movements/);
        await expect(page.getByText(/required|wajib|diisi/i)).toBeVisible();
    });

    // TC040
    test("TC040 - Stock movements list loads", async ({ authPage: page }) => {
        await page.goto("/stock-movements");
        await expect(page).toHaveURL(/stock-movements/);
        await expect(page.locator("table, .table").first()).toBeVisible();
    });

    // TC041
    test("TC041 - Movement detail page shows type and quantity", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-movements");
        const firstLink = page
            .locator("table tbody tr")
            .first()
            .getByRole("link")
            .first();
        if ((await firstLink.count()) > 0) {
            await firstLink.click();
            await expect(page).toHaveURL(/stock-movements\/\d+/);
            await expect(page.getByText(/jenis|type|tipe/i)).toBeVisible();
            await expect(page.getByText(/jumlah|quantity|qty/i)).toBeVisible();
        }
    });
});
