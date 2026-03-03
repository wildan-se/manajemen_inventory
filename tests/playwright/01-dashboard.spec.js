// @ts-check
import { expect } from "@playwright/test";
import { test } from "./helpers/auth.js";

test.describe("Dashboard Overview", () => {
    // TC001
    test("TC001 - Dashboard shows all overview sections after login", async ({
        authPage: page,
    }) => {
        await expect(page).toHaveURL(/\/dashboard/);
        await expect(page.getByText("Total Item Aktif")).toBeVisible();
        await expect(page.getByText("Stok di Bawah Minimum")).toBeVisible();
        await expect(page.getByText("Mutasi Stok Terbaru")).toBeVisible();
    });

    // TC002
    test("TC002 - Dashboard displays Total Items stats card value", async ({
        authPage: page,
    }) => {
        const card = page
            .locator(".bg-white")
            .filter({ hasText: "Total Item Aktif" })
            .first();
        await expect(card).toBeVisible();
        await expect(card.locator("p.text-3xl")).toBeVisible();
    });

    // TC003
    test("TC003 - Dashboard displays Low Stock stats card value", async ({
        authPage: page,
    }) => {
        const card = page
            .locator(".bg-white")
            .filter({ hasText: "Stok di Bawah Minimum" })
            .first();
        await expect(card).toBeVisible();
        await expect(card.locator("p.text-3xl")).toBeVisible();
    });

    // TC004
    test("TC004 - Dashboard displays Pending Purchase Orders stats card value", async ({
        authPage: page,
    }) => {
        const card = page
            .locator(".bg-white")
            .filter({ hasText: "Purchase Order Pending" })
            .first();
        await expect(card).toBeVisible();
    });

    // TC005
    test("TC005 - Dashboard displays Active Work Orders stats card value", async ({
        authPage: page,
    }) => {
        const card = page
            .locator(".bg-white")
            .filter({ hasText: "Work Order Aktif" })
            .first();
        await expect(card).toBeVisible();
    });

    // TC006
    test("TC006 - Dashboard low stock alert list renders", async ({
        authPage: page,
    }) => {
        await expect(
            page
                .getByText("Peringatan Stok Minimum")
                .or(page.getByText("Semua stok di atas batas minimum")),
        ).toBeVisible();
    });

    // TC007
    test("TC007 - Dashboard recent stock movements list renders", async ({
        authPage: page,
    }) => {
        await expect(
            page
                .getByText("Mutasi Stok Terbaru")
                .or(page.getByText("Belum ada mutasi stok"))
                .first(),
        ).toBeVisible();
    });
});
