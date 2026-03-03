// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Category Management", () => {
    // TC008
    test("TC008 - Categories index page loads", async ({ authPage: page }) => {
        await page.goto("/categories");
        await expect(page).toHaveURL(/categories/);
        await expect(
            page
                .getByRole("link", { name: /tambah|create|add/i })
                .or(page.getByRole("button", { name: /tambah|create|add/i })),
        ).toBeVisible();
    });

    // TC009
    test("TC009 - Submit create category form with valid name", async ({
        authPage: page,
    }) => {
        await page.goto("/categories/create");
        await page.fill('input[name="name"]', "Kategori Playwright Test");
        const descField = page.locator(
            'textarea[name="description"], input[name="description"]',
        );
        if ((await descField.count()) > 0) {
            await descField.fill("Deskripsi test Playwright");
        }
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/categories/);
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC010
    test("TC010 - Create category end-to-end and verify in list", async ({
        authPage: page,
    }) => {
        const uniqueName = "PW-Cat-" + Date.now();
        await page.goto("/categories/create");
        await page.fill('input[name="name"]', uniqueName);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/categories/);
        await expect(page.getByText(uniqueName)).toBeVisible();
    });

    // TC011
    test("TC011 - Create category shows success message", async ({
        authPage: page,
    }) => {
        await page.goto("/categories/create");
        await page.fill(
            'input[name="name"]',
            "PW-Cat-SuccessMsg-" + Date.now(),
        );
        await page.click('button[type="submit"]');
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC012
    test("TC012 - Create category with empty name shows validation error", async ({
        authPage: page,
    }) => {
        await page.goto("/categories/create");
        // Submit without filling name
        await page.click('button[type="submit"]');
        // Should stay on same page AND show an error
        await expect(page).toHaveURL(/categories\/create|categories/);
        await expect(
            page.getByText(/required|wajib|diisi|The name field/i),
        ).toBeVisible();
    });
});
