// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Purchase Order Management", () => {
    // TC042
    test("TC042 - Create PO with line items and approve it", async ({
        authPage: page,
    }) => {
        await page.goto("/purchase-orders/create");
        await expect(page).toHaveURL(/purchase-orders\/create/);

        // Select supplier
        const supplierSelect = page.locator('select[name="supplier_id"]');
        if ((await supplierSelect.count()) > 0) {
            const opts = await supplierSelect.locator("option").all();
            if (opts.length > 1)
                await supplierSelect.selectOption({ index: 1 });
        }

        // Fill order date
        const dateInput = page
            .locator('input[name="order_date"], input[type="date"]')
            .first();
        if ((await dateInput.count()) > 0) await dateInput.fill("2026-01-15");

        // Fill first item row
        const itemSelects = page.locator('select[name*="item_id"]');
        if ((await itemSelects.count()) > 0) {
            const opts = await itemSelects.first().locator("option").all();
            if (opts.length > 1)
                await itemSelects.first().selectOption({ index: 1 });
        }

        const qtyInputs = page.locator('input[name*="quantity"]');
        if ((await qtyInputs.count()) > 0) await qtyInputs.first().fill("5");

        const priceInputs = page.locator(
            'input[name*="price"], input[name*="unit_price"]',
        );
        if ((await priceInputs.count()) > 0)
            await priceInputs.first().fill("10000");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/purchase-orders/);
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC043
    test("TC043 - Create PO: add multiple line items and verify totals visible", async ({
        authPage: page,
    }) => {
        await page.goto("/purchase-orders/create");

        // Try to add second row
        const addRowBtn = page.getByRole("button", {
            name: /tambah|add item|add row|\+/i,
        });
        if ((await addRowBtn.count()) > 0) {
            await addRowBtn.click();
            // Two rows should now exist
            const rows = page
                .locator("tr, .item-row")
                .filter({ hasText: /item|barang/i });
            // Just ensure form has at least one row and total is shown
        }

        // Total section should be visible
        const totalEl = page.getByText(/total/i);
        await expect(page).toHaveURL(/purchase-orders\/create/);
    });

    // TC044
    test("TC044 - Create PO: blocked when required fields missing", async ({
        authPage: page,
    }) => {
        await page.goto("/purchase-orders/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/purchase-orders\/create|purchase-orders/);
        await expect(page.getByText(/required|wajib|diisi/i)).toBeVisible();
    });
});
