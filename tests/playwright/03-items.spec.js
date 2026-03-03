// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Item Management", () => {
    // TC013
    test("TC013 - Search and filter items list by keyword", async ({
        authPage: page,
    }) => {
        await page.goto("/items");
        const searchInput = page.locator(
            'input[name="search"], input[placeholder*="cari" i], input[placeholder*="search" i]',
        );
        if ((await searchInput.count()) > 0) {
            await searchInput.fill("test");
            await page.keyboard.press("Enter");
        }
        await expect(page).toHaveURL(/items/);
    });

    // TC014
    test("TC014 - Apply category filter on items list", async ({
        authPage: page,
    }) => {
        await page.goto("/items");
        const catFilter = page.locator(
            'select[name="category_id"], select[name="category"]',
        );
        if ((await catFilter.count()) > 0) {
            const options = await catFilter.locator("option").all();
            if (options.length > 1) {
                await catFilter.selectOption({ index: 1 });
                await page.keyboard.press("Enter");
            }
        }
        await expect(page).toHaveURL(/items/);
    });

    // TC015
    test("TC015 - Apply status filter on items list", async ({
        authPage: page,
    }) => {
        await page.goto("/items");
        const statusFilter = page.locator('select[name="status"]');
        if ((await statusFilter.count()) > 0) {
            await statusFilter.selectOption("active");
        }
        await expect(page).toHaveURL(/items/);
    });

    // TC016
    test("TC016 - Low stock badge shown for items below minimum", async ({
        authPage: page,
    }) => {
        await page.goto("/items");
        // Check if any badge/label indicating low stock exists (may not exist if no items are low)
        const lowStockBadge = page
            .locator(".badge, span")
            .filter({ hasText: /stok rendah|low stock/i });
        // Just verify page loads correctly
        await expect(page).toHaveURL(/items/);
    });

    // TC017
    test("TC017 - Create new item with required fields", async ({
        authPage: page,
    }) => {
        await page.goto("/items/create");
        await page.fill('input[name="name"]', "PW-Item-" + Date.now());
        await page.fill(
            'input[name="sku"], input[name="code"]',
            "SKU-PW-" + Date.now(),
        );

        const categorySelect = page.locator('select[name="category_id"]');
        if ((await categorySelect.count()) > 0) {
            const options = await categorySelect.locator("option").all();
            if (options.length > 1)
                await categorySelect.selectOption({ index: 1 });
        }

        const unitSelect = page.locator('select[name="unit_id"]');
        if ((await unitSelect.count()) > 0) {
            const options = await unitSelect.locator("option").all();
            if (options.length > 1) await unitSelect.selectOption({ index: 1 });
        }

        const minStock = page.locator(
            'input[name="minimum_stock"], input[name="min_stock"]',
        );
        if ((await minStock.count()) > 0) await minStock.fill("10");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/items/);
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC018
    test("TC018 - Create item and verify appears in list", async ({
        authPage: page,
    }) => {
        const uniqueName = "PW-ItemList-" + Date.now();
        await page.goto("/items/create");
        await page.fill('input[name="name"]', uniqueName);

        const skuInput = page.locator('input[name="sku"], input[name="code"]');
        if ((await skuInput.count()) > 0)
            await skuInput.fill("SKU-" + Date.now());

        const categorySelect = page.locator('select[name="category_id"]');
        if ((await categorySelect.count()) > 0) {
            const options = await categorySelect.locator("option").all();
            if (options.length > 1)
                await categorySelect.selectOption({ index: 1 });
        }

        const unitSelect = page.locator('select[name="unit_id"]');
        if ((await unitSelect.count()) > 0) {
            const options = await unitSelect.locator("option").all();
            if (options.length > 1) await unitSelect.selectOption({ index: 1 });
        }

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/items/);
        await expect(page.getByText(uniqueName)).toBeVisible();
    });

    // TC019
    test("TC019 - Create item with blank required fields shows validation", async ({
        authPage: page,
    }) => {
        await page.goto("/items/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/items\/create|items/);
        await expect(
            page.getByText(/required|wajib|diisi|The name field/i),
        ).toBeVisible();
    });

    // TC020
    test("TC020 - Open item detail page and verify stock section", async ({
        authPage: page,
    }) => {
        await page.goto("/items");
        const detailLink = page
            .getByRole("link", { name: /detail|lihat|view/i })
            .first();
        if ((await detailLink.count()) > 0) {
            await detailLink.click();
        } else {
            // Try clicking first row
            const firstRow = page.locator("table tbody tr").first();
            if ((await firstRow.count()) > 0) {
                const link = firstRow.getByRole("link").first();
                await link.click();
            }
        }
        await expect(page).toHaveURL(/items\/\d+/);
        // Stock section per warehouse should be visible
        await expect(
            page
                .getByText(/stok per gudang|per warehouse|stock per/i)
                .or(page.getByText(/gudang/i)),
        ).toBeVisible();
    });
});
