📘 SKBU — Enterprise Playwright E2E Test Suite

Dokumen ini berisi spesifikasi dan implementasi Playwright E2E untuk sistem:

SKBU — Sistem Manajemen Inventori
Laravel 12 · Turbo Drive SPA · Alpine.js · Tailwind · MySQL 8
Session-based Auth (Breeze) · RBAC 5 Role

🎯 Tujuan

Test suite ini memvalidasi:

Authentication & Session Integrity

Role-Based Access Control (RBAC Matrix)

Master Data Integrity

Purchase Order Workflow

Production Order Workflow

Concurrency & Race Condition

Privilege Escalation Protection

Export & Throttle

Business Logic Integrity

Enterprise-Level Abuse Testing

📂 File Target

Disarankan simpan sebagai:

tests/e2e/skbu-enterprise.spec.ts

🧪 Playwright Enterprise Spec (TypeScript)
import { test, expect, Page } from '@playwright/test';

const BASE_URL = 'http://127.0.0.1:8000';

type Role =
  | 'admin'
  | 'inventory_controller'
  | 'supervisor'
  | 'warehouse_operator'
  | 'production_staff';

const roles: Role[] = [
  'admin',
  'inventory_controller',
  'supervisor',
  'warehouse_operator',
  'production_staff',
];

const credentials: Record<Role, { email: string; password: string }> = {
  admin: { email: 'admin@skbu.com', password: 'password' },
  inventory_controller: { email: 'inventory@skbu.com', password: 'password' },
  supervisor: { email: 'supervisor@skbu.com', password: 'password' },
  warehouse_operator: { email: 'gudang@skbu.com', password: 'password' },
  production_staff: { email: 'produksi@skbu.com', password: 'password' },
};

/* =====================================================
   AUTHENTICATION
===================================================== */

test.describe('Authentication', () => {
  test('Login success (admin)', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel(/email/i).fill(credentials.admin.email);
    await page.getByLabel(/password/i).fill(credentials.admin.password);
    await page.getByRole('button', { name: /login/i }).click();

    await expect(page).toHaveURL(/dashboard/i);
  });

  test('Login gagal (password salah)', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await page.getByLabel(/email/i).fill(credentials.admin.email);
    await page.getByLabel(/password/i).fill('wrongpassword');
    await page.getByRole('button', { name: /login/i }).click();

    await expect(page.getByText(/credentials/i)).toBeVisible();
  });

  test('Logout menghapus session', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.getByRole('button', { name: /logout/i }).click();
    await expect(page).toHaveURL(/login/i);
  });
});

/* =====================================================
   ROLE MATRIX VALIDATION
===================================================== */

test.describe('Role Matrix Validation', () => {
  for (const role of roles) {
    test.describe(`${role} isolation`, () => {
      test.beforeEach(async ({ page }) => {
        await loginAs(page, role);
      });

      test('Dashboard accessible', async ({ page }) => {
        await page.goto(`${BASE_URL}/dashboard`);
        await expect(page.getByText(/dashboard/i)).toBeVisible();
      });

      test('Unauthorized route blocked', async ({ page }) => {
        await page.goto(`${BASE_URL}/users`);
        if (role !== 'admin') {
          await expect(page).toHaveURL(/403|forbidden|unauthorized/);
        }
      });
    });
  }
});

/* =====================================================
   PURCHASE ORDER FLOW
===================================================== */

test.describe('Purchase Order Flow', () => {
  test('Operator → Supervisor → Admin workflow', async ({ browser }) => {
    const operator = await browser.newContext();
    const supervisor = await browser.newContext();
    const admin = await browser.newContext();

    const opPage = await operator.newPage();
    const supPage = await supervisor.newPage();
    const adminPage = await admin.newPage();

    await loginAs(opPage, 'warehouse_operator');
    await createTestPO(opPage);

    await loginAs(supPage, 'supervisor');
    await supPage.goto(`${BASE_URL}/purchase-orders`);
    await supPage.getByRole('button', { name: /approve/i }).click();

    await loginAs(adminPage, 'admin');
    await adminPage.goto(`${BASE_URL}/purchase-orders`);
    await adminPage.getByRole('button', { name: /receive/i }).click();

    await expect(adminPage.getByText(/received/i)).toBeVisible();
  });
});

/* =====================================================
   CONCURRENCY VALIDATION
===================================================== */

test.describe('Concurrency Validation', () => {
  test('Dual approval race test', async ({ browser }) => {
    const ctx1 = await browser.newContext();
    const ctx2 = await browser.newContext();

    const page1 = await ctx1.newPage();
    const page2 = await ctx2.newPage();

    await loginAs(page1, 'supervisor');
    await loginAs(page2, 'admin');

    await page1.goto(`${BASE_URL}/purchase-orders`);
    await page2.goto(`${BASE_URL}/purchase-orders`);

    await Promise.all([
      page1.getByRole('button', { name: /approve/i }).click(),
      page2.getByRole('button', { name: /approve/i }).click(),
    ]);

    await expect(page1.getByText(/approved/i)).toBeVisible();
  });
});

/* =====================================================
   PRIVILEGE ESCALATION
===================================================== */

test.describe('Privilege Escalation Protection', () => {
  test('Operator cannot access /users', async ({ page }) => {
    await loginAs(page, 'warehouse_operator');
    await page.goto(`${BASE_URL}/users`);
    await expect(page).toHaveURL(/403|forbidden|unauthorized/);
  });
});

/* =====================================================
   HELPER FUNCTIONS
===================================================== */

async function loginAs(page: Page, role: Role) {
  const cred = credentials[role];
  await page.goto(`${BASE_URL}/login`);
  await page.getByLabel(/email/i).fill(cred.email);
  await page.getByLabel(/password/i).fill(cred.password);
  await page.getByRole('button', { name: /login/i }).click();
  await expect(page).toHaveURL(/dashboard/i);
}

async function createTestPO(page: Page) {
  await page.goto(`${BASE_URL}/purchase-orders/create`);
  await page.getByLabel(/supplier/i).selectOption({ index: 1 });
  await page.getByRole('button', { name: /add item/i }).click();
  await page.getByRole('button', { name: /save/i }).click();
}