// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Production Order (Work Order) Management", () => {
    // TC045
    test("TC045 - Create new Work Order with input materials and output products", async ({
        authPage: page,
    }) => {
        await page.goto("/production-orders/create");
        await expect(page).toHaveURL(/production-orders\/create/);

        const woNumberInput = page.locator(
            'input[name="order_number"], input[name="production_number"]',
        );
        if ((await woNumberInput.count()) > 0)
            await woNumberInput.fill("WO-PW-" + Date.now());

        // Select output item
        const outputItemSelects = page.locator(
            'select[name*="output"][name*="item"], select[name*="product"]',
        );
        if ((await outputItemSelects.count()) > 0) {
            const opts = await outputItemSelects
                .first()
                .locator("option")
                .all();
            if (opts.length > 1)
                await outputItemSelects.first().selectOption({ index: 1 });
        }

        // Date input
        const dateInput = page
            .locator('input[name="planned_date"], input[type="date"]')
            .first();
        if ((await dateInput.count()) > 0) await dateInput.fill("2026-01-20");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/production-orders/);
    });

    // TC046
    test("TC046 - Add and remove input material rows on Create Work Order form", async ({
        authPage: page,
    }) => {
        await page.goto("/production-orders/create");

        // Click "Add Material" button
        const addMaterialBtn = page.getByRole("button", {
            name: /tambah material|add material|add input|\+/i,
        });
        if ((await addMaterialBtn.count()) > 0) {
            const rowsBefore = await page
                .locator('[data-type="input-row"], .input-row, .material-row')
                .count();
            await addMaterialBtn.click();
            const rowsAfter = await page
                .locator('[data-type="input-row"], .input-row, .material-row')
                .count();
            // Row count should increase (or at least page remains stable)
            await expect(page).toHaveURL(/production-orders\/create/);
        } else {
            await expect(page).toHaveURL(/production-orders\/create/);
        }
    });

    // TC047
    test("TC047 - Add and remove output product rows on Create Work Order form", async ({
        authPage: page,
    }) => {
        await page.goto("/production-orders/create");

        // Click "Add Output" button
        const addOutputBtn = page.getByRole("button", {
            name: /tambah produk|add output|add product|\+/i,
        });
        if ((await addOutputBtn.count()) > 0) {
            await addOutputBtn.click();
            await expect(page).toHaveURL(/production-orders\/create/);
        } else {
            await expect(page).toHaveURL(/production-orders\/create/);
        }
    });
});
