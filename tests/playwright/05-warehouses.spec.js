// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Warehouse Management", () => {
    // TC029
    test("TC029 - Create new warehouse and verify in list", async ({
        authPage: page,
    }) => {
        const uniqueName = "PW-WH-" + Date.now();
        await page.goto("/warehouses/create");
        await page.fill('input[name="name"]', uniqueName);

        const locationInput = page.locator(
            'input[name="location"], input[name="address"]',
        );
        if ((await locationInput.count()) > 0)
            await locationInput.fill("Lokasi Test Playwright");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/warehouses/);
        await expect(page.getByText(uniqueName)).toBeVisible();
    });

    // TC030
    test("TC030 - Submit create warehouse form with valid name and location", async ({
        authPage: page,
    }) => {
        await page.goto("/warehouses/create");
        await page.fill('input[name="name"]', "PW-WH-Valid-" + Date.now());

        const locationInput = page.locator(
            'input[name="location"], input[name="address"]',
        );
        if ((await locationInput.count()) > 0)
            await locationInput.fill("Jakarta");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/warehouses/);
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC031
    test("TC031 - Complete create warehouse flow and confirm in list", async ({
        authPage: page,
    }) => {
        const uniqueName = "PW-WH-Flow-" + Date.now();
        await page.goto("/warehouses/create");
        await page.fill('input[name="name"]', uniqueName);

        const codeInput = page.locator('input[name="code"]');
        if ((await codeInput.count()) > 0) await codeInput.fill("WH-PW");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/warehouses/);
        await expect(page.getByText(uniqueName)).toBeVisible();
    });

    // TC032
    test("TC032 - Warehouse creation success state visible", async ({
        authPage: page,
    }) => {
        await page.goto("/warehouses/create");
        await page.fill('input[name="name"]', "PW-WH-Success-" + Date.now());
        await page.click('button[type="submit"]');
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC033
    test("TC033 - Warehouse detail page shows stock table", async ({
        authPage: page,
    }) => {
        await page.goto("/warehouses");
        const firstLink = page
            .locator("table tbody tr")
            .first()
            .getByRole("link")
            .first();
        if ((await firstLink.count()) > 0) {
            await firstLink.click();
            await expect(page).toHaveURL(/warehouses\/\d+/);
            await expect(
                page.locator("table").or(page.getByText(/stok|stock/i)),
            ).toBeVisible();
        }
    });

    // TC034
    test("TC034 - Create warehouse: required field validation when name missing", async ({
        authPage: page,
    }) => {
        await page.goto("/warehouses/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/warehouses\/create|warehouses/);
        await expect(
            page.getByText(/required|wajib|diisi|The name field/i),
        ).toBeVisible();
    });
});
