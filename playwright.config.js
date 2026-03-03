// @ts-check
import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
    testDir: "./tests",

    // Timeout default per test
    timeout: 45_000,
    // Timeout untuk assertion expect()
    expect: { timeout: 10_000 },

    // Jalankan sequential untuk menghindari flakiness (test DB bersama)
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 1,
    workers: 1,

    reporter: [
        ["list"],
        ["html", { outputFolder: "playwright-report", open: "never" }],
    ],

    use: {
        // Gunakan port yang aktif (php artisan serve default)
        baseURL: "http://127.0.0.1:8000",

        trace: "on-first-retry",
        screenshot: "only-on-failure",
        video: "off",
        headless: true,

        // Turbo Drive memerlukan JS aktif
        javaScriptEnabled: true,
    },

    projects: [
        {
            name: "chromium",
            use: { ...devices["Desktop Chrome"] },
        },
    ],
});
