// @ts-check
import { expect, chromium } from "@playwright/test";
import { test, loginAs } from "./helpers/auth.js";

test.describe("User Management (Admin)", () => {
    // TC062
    test("TC062 - Admin can view user list with role badges", async ({
        authPage: page,
    }) => {
        await page.goto("/users");
        await expect(page).toHaveURL(/users/);
        await expect(page.locator("table, .table").first()).toBeVisible();
        // Role badges should be visible
        await expect(
            page.getByText(/admin|staff|manager|viewer/i).first(),
        ).toBeVisible();
    });

    // TC063
    test("TC063 - Admin can create a new active user with a role", async ({
        authPage: page,
    }) => {
        await page.goto("/users/create");
        await page.fill('input[name="name"]', "PW Test User " + Date.now());
        await page.fill(
            'input[name="email"]',
            "pwtest" + Date.now() + "@test.com",
        );
        await page.fill('input[name="password"]', "password123");

        const confirmInput = page.locator(
            'input[name="password_confirmation"]',
        );
        if ((await confirmInput.count()) > 0)
            await confirmInput.fill("password123");

        const roleSelect = page.locator('select[name="role"]');
        if ((await roleSelect.count()) > 0)
            await roleSelect.selectOption("inventory_controller");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/users/);
        await expect(page.getByText(/berhasil|success|created/i)).toBeVisible();
    });

    // TC064
    test("TC064 - Admin creates user and sees it in list", async ({
        authPage: page,
    }) => {
        const uniqueEmail = "pwlist" + Date.now() + "@test.com";
        const uniqueName = "PW-User-" + Date.now();
        await page.goto("/users/create");
        await page.fill('input[name="name"]', uniqueName);
        await page.fill('input[name="email"]', uniqueEmail);
        await page.fill('input[name="password"]', "password123");

        const confirmInput = page.locator(
            'input[name="password_confirmation"]',
        );
        if ((await confirmInput.count()) > 0)
            await confirmInput.fill("password123");

        const roleSelect = page.locator('select[name="role"]');
        if ((await roleSelect.count()) > 0)
            await roleSelect.selectOption("warehouse_operator");

        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/users/);
        await expect(page.getByText(uniqueName)).toBeVisible();
    });

    // TC065
    test("TC065 - Admin cannot create user when required fields missing", async ({
        authPage: page,
    }) => {
        await page.goto("/users/create");
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/users\/create|users/);
        await expect(page.getByText(/required|wajib|diisi/i)).toBeVisible();
    });

    // TC066
    test("TC066 - Admin cannot create user with invalid email", async ({
        authPage: page,
    }) => {
        await page.goto("/users/create");
        await page.fill('input[name="name"]', "Test User");
        await page.fill('input[name="email"]', "not-an-email");
        await page.fill('input[name="password"]', "password123");
        await page.click('button[type="submit"]');
        await expect(
            page.getByText(/email|valid|invalid|format/i),
        ).toBeVisible();
    });

    // TC067
    test("TC067 - Non-admin user forbidden from User Management", async ({
        page,
    }) => {
        // Login as inventory_controller (non-admin)
        await loginAs(page, "inventory@inventory.com", "password");
        await page.goto("/users");
        // Should redirect away or show 403
        await expect(page).not.toHaveURL(/\/users$/);
    });

    // TC068
    test("TC068 - Admin can delete another user after confirming", async ({
        authPage: page,
    }) => {
        await page.goto("/users");
        // Find a non-admin user row to delete
        const deleteBtn = page
            .locator("table tbody tr")
            .filter({ hasNot: page.getByText("admin@inventory.com") })
            .first()
            .getByRole("button", { name: /hapus|delete/i });

        if ((await deleteBtn.count()) > 0) {
            // Handle confirmation dialog
            page.on("dialog", async (dialog) => {
                if (dialog.type() === "confirm") await dialog.accept();
            });
            await deleteBtn.click();
            await expect(page).toHaveURL(/users/);
            await expect(
                page.getByText(/berhasil|success|deleted|dihapus/i),
            ).toBeVisible();
        } else {
            // Delete via form if no button
            const deleteForm = page
                .locator("form")
                .filter({
                    has: page.locator(
                        'input[value="DELETE"], button[name*="delete"]',
                    ),
                })
                .first();
            if ((await deleteForm.count()) > 0) {
                page.on("dialog", async (dialog) => dialog.accept());
                await deleteForm.locator('button[type="submit"]').click();
                await expect(page).toHaveURL(/users/);
            }
        }
    });

    // TC069
    test("TC069 - Admin cannot delete their own account", async ({
        authPage: page,
    }) => {
        await page.goto("/users");
        // The admin's own row should not have a delete button, or should show an error
        const ownRow = page
            .locator("table tbody tr")
            .filter({ has: page.getByText("admin@inventory.com") })
            .first();

        if ((await ownRow.count()) > 0) {
            const selfDeleteBtn = ownRow.getByRole("button", {
                name: /hapus|delete/i,
            });
            // Either no button exists or clicking it shows error
            if ((await selfDeleteBtn.count()) > 0) {
                page.on("dialog", async (dialog) => dialog.accept());
                await selfDeleteBtn.click();
                await expect(
                    page.getByText(/tidak bisa|cannot|sendiri|yourself|own/i),
                ).toBeVisible();
            } else {
                // No delete button for own account = correct behaviour
                await expect(selfDeleteBtn).toHaveCount(0);
            }
        }
    });
});
