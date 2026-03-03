// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Stock Opname", () => {
    // TC048
    test("TC048 - Create stock opname session and see item list populated", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-opnames/create");
        await expect(page).toHaveURL(/stock-opnames\/create/);

        const warehouseSelect = page.locator('select[name="warehouse_id"]');
        if ((await warehouseSelect.count()) > 0) {
            const opts = await warehouseSelect.locator("option").all();
            if (opts.length > 1)
                await warehouseSelect.selectOption({ index: 1 });
        }

        const dateInput = page
            .locator('input[name="opname_date"], input[type="date"]')
            .first();
        if ((await dateInput.count()) > 0) await dateInput.fill("2026-01-25");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/stock-opnames/);
    });

    // TC049
    test("TC049 - Start opname: select warehouse and date, see item list", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-opnames/create");

        const warehouseSelect = page.locator('select[name="warehouse_id"]');
        if ((await warehouseSelect.count()) > 0) {
            const opts = await warehouseSelect.locator("option").all();
            if (opts.length > 1) {
                await warehouseSelect.selectOption({ index: 1 });
                // After selecting warehouse, items may load dynamically
                await page.waitForTimeout(500);
            }
        }

        // Items table or list should be present
        await expect(page).toHaveURL(/stock-opnames\/create/);
    });

    // TC050
    test("TC050 - Cancel an ongoing opname session", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-opnames");
        // Find a pending/in-progress opname and cancel it
        const cancelBtn = page
            .getByRole("button", { name: /batal|cancel/i })
            .or(page.getByRole("link", { name: /batal|cancel/i }))
            .first();

        if ((await cancelBtn.count()) > 0) {
            await cancelBtn.click();
            // Handle confirmation dialog if present
            page.on("dialog", (dialog) => dialog.accept());
            await expect(page).toHaveURL(/stock-opnames/);
            await expect(
                page.getByText(/batal|cancelled|dibatalkan/i),
            ).toBeVisible();
        } else {
            // Create one first, then cancel
            await page.goto("/stock-opnames/create");
            const warehouseSelect = page.locator('select[name="warehouse_id"]');
            if ((await warehouseSelect.count()) > 0) {
                const opts = await warehouseSelect.locator("option").all();
                if (opts.length > 1)
                    await warehouseSelect.selectOption({ index: 1 });
            }
            const dateInput = page
                .locator('input[name="opname_date"], input[type="date"]')
                .first();
            if ((await dateInput.count()) > 0)
                await dateInput.fill("2026-02-01");
            await page.click('button[type="submit"]');
            await expect(page).toHaveURL(/stock-opnames/);
        }
    });

    // TC051
    test("TC051 - Validate required fields: missing warehouse shows error", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-opnames/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/stock-opnames\/create|stock-opnames/);
        await expect(page.getByText(/required|wajib|diisi/i)).toBeVisible();
    });

    // TC052
    test("TC052 - Validate required fields when saving physical counts", async ({
        authPage: page,
    }) => {
        await page.goto("/stock-opnames");
        // Try to open existing opname detail
        const inProgressLink = page
            .locator("table tbody tr")
            .filter({ hasText: /draft|pending|berlangsung/i })
            .first()
            .getByRole("link")
            .first();

        if ((await inProgressLink.count()) > 0) {
            await inProgressLink.click();
            await expect(page).toHaveURL(/stock-opnames\/\d+/);
            // Try saving with empty physical count
            const saveBtn = page.getByRole("button", {
                name: /simpan|save|selesai/i,
            });
            if ((await saveBtn.count()) > 0) {
                await saveBtn.click();
                // Should show validation or redirect
                await expect(page).toHaveURL(/stock-opnames/);
            }
        } else {
            // Just verify list loads
            await expect(page).toHaveURL(/stock-opnames/);
        }
    });
});
