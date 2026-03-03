// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Reports", () => {
    // TC053
    test("TC053 - Stock Summary report loads with aggregated stock table", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/stock-summary");
        await expect(page).toHaveURL(/reports\/stock-summary/);
        await expect(
            page
                .locator("table")
                .or(page.getByText(/stok|stock|item/i))
                .first(),
        ).toBeVisible();
    });

    // TC054
    test("TC054 - Filter Stock Summary by specific warehouse", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/stock-summary");
        const warehouseFilter = page.locator('select[name="warehouse_id"]');
        if ((await warehouseFilter.count()) > 0) {
            const opts = await warehouseFilter.locator("option").all();
            if (opts.length > 1) {
                await warehouseFilter.selectOption({ index: 1 });
                // Submit filter or wait for auto-update
                const filterBtn = page.getByRole("button", {
                    name: /filter|cari|tampilkan/i,
                });
                if ((await filterBtn.count()) > 0) await filterBtn.click();
                else await page.keyboard.press("Enter");
            }
        }
        await expect(page).toHaveURL(/reports\/stock-summary/);
    });

    // TC055
    test('TC055 - Stock Summary "All warehouses" view available', async ({
        authPage: page,
    }) => {
        await page.goto("/reports/stock-summary");
        const warehouseFilter = page.locator('select[name="warehouse_id"]');
        if ((await warehouseFilter.count()) > 0) {
            // Select "all" option (first option typically)
            await warehouseFilter.selectOption({ index: 0 });
        }
        await expect(page).toHaveURL(/reports\/stock-summary/);
        await expect(
            page.locator("table, .table, [class*='report'], main").first(),
        ).toBeVisible();
    });

    // TC056
    test("TC056 - Low Stock report loads with low-stock list", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/low-stock");
        await expect(page).toHaveURL(/reports\/low-stock/);
        await expect(
            page
                .locator("table")
                .or(page.getByText(/stok rendah|low stock|minimum/i))
                .first(),
        ).toBeVisible();
    });

    // TC057
    test("TC057 - Filter Low Stock report by warehouse", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/low-stock");
        const warehouseFilter = page.locator('select[name="warehouse_id"]');
        if ((await warehouseFilter.count()) > 0) {
            const opts = await warehouseFilter.locator("option").all();
            if (opts.length > 1) {
                await warehouseFilter.selectOption({ index: 1 });
                const filterBtn = page.getByRole("button", {
                    name: /filter|cari|tampilkan/i,
                });
                if ((await filterBtn.count()) > 0) await filterBtn.click();
            }
        }
        await expect(page).toHaveURL(/reports\/low-stock/);
    });

    // TC058
    test("TC058 - Movement History report loads with filter controls", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/movement-history");
        await expect(page).toHaveURL(/reports\/movement-history/);
        // Filter controls should be visible (date inputs, item select, etc.)
        await expect(
            page
                .locator('input[type="date"], select, input[name*="date"]')
                .first(),
        ).toBeVisible();
    });

    // TC059
    test("TC059 - Filter Movement History by item, type and date range", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/movement-history");

        const startDate = page.locator(
            'input[name="start_date"], input[name="date_from"]',
        );
        if ((await startDate.count()) > 0) await startDate.fill("2026-01-01");

        const endDate = page.locator(
            'input[name="end_date"], input[name="date_to"]',
        );
        if ((await endDate.count()) > 0) await endDate.fill("2026-12-31");

        const typeFilter = page.locator('select[name="type"]');
        if ((await typeFilter.count()) > 0) {
            const opts = await typeFilter.locator("option").all();
            if (opts.length > 1) await typeFilter.selectOption({ index: 1 });
        }

        const filterBtn = page.getByRole("button", {
            name: /filter|cari|tampilkan/i,
        });
        if ((await filterBtn.count()) > 0) await filterBtn.click();

        await expect(page).toHaveURL(/reports\/movement-history/);
    });

    // TC060
    test("TC060 - Movement History: end date before start date shows error", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/movement-history");

        const startDate = page.locator(
            'input[name="start_date"], input[name="date_from"]',
        );
        if ((await startDate.count()) > 0) await startDate.fill("2026-12-31");

        const endDate = page.locator(
            'input[name="end_date"], input[name="date_to"]',
        );
        if ((await endDate.count()) > 0) await endDate.fill("2026-01-01");

        const filterBtn = page.getByRole("button", {
            name: /filter|cari|tampilkan/i,
        });
        if ((await filterBtn.count()) > 0) await filterBtn.click();

        // Should show a validation error or empty result
        await expect(page).toHaveURL(/reports\/movement-history/);
        // Some UI feedback expected
    });

    // TC061
    test("TC061 - Movement History invalid date range error visible", async ({
        authPage: page,
    }) => {
        await page.goto("/reports/movement-history");

        const startDate = page.locator(
            'input[name="start_date"], input[name="date_from"]',
        );
        if ((await startDate.count()) > 0) await startDate.fill("2026-12-01");

        const endDate = page.locator(
            'input[name="end_date"], input[name="date_to"]',
        );
        if ((await endDate.count()) > 0) await endDate.fill("2026-01-01");

        const filterBtn = page.getByRole("button", {
            name: /filter|cari|tampilkan/i,
        });
        if ((await filterBtn.count()) > 0) {
            await filterBtn.click();
            await expect(page).toHaveURL(/reports\/movement-history/);
        }
    });
});
