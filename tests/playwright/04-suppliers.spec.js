// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Supplier Management", () => {
    // TC021
    test("TC021 - Create new supplier and verify in list", async ({
        authPage: page,
    }) => {
        const uniqueName = "PW-Supplier-" + Date.now();
        await page.goto("/suppliers/create");
        await page.fill('input[name="name"]', uniqueName);

        const emailInput = page.locator('input[name="email"]');
        if ((await emailInput.count()) > 0)
            await emailInput.fill("supplier@test.com");

        const phoneInput = page.locator(
            'input[name="phone"], input[name="contact"]',
        );
        if ((await phoneInput.count()) > 0)
            await phoneInput.fill("08123456789");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/suppliers/);
        await expect(page.getByText(uniqueName)).toBeVisible();
    });

    // TC022
    test("TC022 - Submit create supplier form with valid data", async ({
        authPage: page,
    }) => {
        await page.goto("/suppliers/create");
        await page.fill('input[name="name"]', "PW-SupplierValid-" + Date.now());

        const emailInput = page.locator('input[name="email"]');
        if ((await emailInput.count()) > 0)
            await emailInput.fill("valid@supplier.com");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/suppliers/);
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC023
    test("TC023 - Create supplier validation: required fields empty", async ({
        authPage: page,
    }) => {
        await page.goto("/suppliers/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/suppliers\/create|suppliers/);
        await expect(
            page.getByText(/required|wajib|diisi|The name field/i),
        ).toBeVisible();
    });

    // TC024
    test("TC024 - Create supplier: invalid contact format shows error", async ({
        authPage: page,
    }) => {
        await page.goto("/suppliers/create");
        await page.fill('input[name="name"]', "Test Supplier");

        const emailInput = page.locator('input[name="email"]');
        if ((await emailInput.count()) > 0)
            await emailInput.fill("invalid-email-format");

        await page.click('button[type="submit"]');
        await expect(
            page.getByText(/email|valid|invalid|format/i),
        ).toBeVisible();
    });

    // TC025
    test("TC025 - Suppliers list loads with table content", async ({
        authPage: page,
    }) => {
        await page.goto("/suppliers");
        await expect(page).toHaveURL(/suppliers/);
        await expect(page.locator("table, .table").first()).toBeVisible();
    });

    // TC026
    test("TC026 - Open supplier detail page", async ({ authPage: page }) => {
        await page.goto("/suppliers");
        const detailLink = page
            .locator("table tbody tr")
            .first()
            .getByRole("link")
            .first();
        if ((await detailLink.count()) > 0) {
            await detailLink.click();
            await expect(page).toHaveURL(/suppliers\/\d+/);
            await expect(
                page.getByText(/detail|informasi|information/i),
            ).toBeVisible();
        }
    });

    // TC027
    test("TC027 - Supplier detail shows purchase orders section", async ({
        authPage: page,
    }) => {
        await page.goto("/suppliers");
        const firstLink = page
            .locator("table tbody tr")
            .first()
            .getByRole("link")
            .first();
        if ((await firstLink.count()) > 0) {
            await firstLink.click();
            await expect(page).toHaveURL(/suppliers\/\d+/);
            // Should show PO section or empty state
            await expect(
                page.getByText(/purchase order|pembelian/i),
            ).toBeVisible();
        }
    });

    // TC028
    test("TC028 - Navigate back to suppliers list from detail", async ({
        authPage: page,
    }) => {
        await page.goto("/suppliers");
        const firstLink = page
            .locator("table tbody tr")
            .first()
            .getByRole("link")
            .first();
        if ((await firstLink.count()) > 0) {
            await firstLink.click();
            await expect(page).toHaveURL(/suppliers\/\d+/);
            const backLink = page.getByRole("link", {
                name: /kembali|back|list|semua/i,
            });
            if ((await backLink.count()) > 0) {
                await backLink.click();
                await expect(page).toHaveURL(/suppliers/);
            }
        }
    });
});
