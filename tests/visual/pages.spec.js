// @ts-check
const { test, expect } = require('@playwright/test');

const ADMIN_USER = process.env.DOLI_ADMIN_LOGIN || 'admin';
const ADMIN_PASS = process.env.DOLI_ADMIN_PASSWORD || 'admin123';

/**
 * Pages to screenshot in both light and dark modes.
 * Paths are relative to baseURL.
 */
const PAGES = [
	{ name: 'dashboard', path: '/index.php' },
	{ name: 'thirdparty-list', path: '/societe/list.php' },
	{ name: 'invoice-list', path: '/compta/facture/list.php' },
	{ name: 'project-list', path: '/projet/list.php' },
	{ name: 'hrm-leave-list', path: '/holiday/list.php' },
	{ name: 'user-card', path: '/user/card.php?id=1' },
	{ name: 'product-list', path: '/product/list.php' },
	{ name: 'setup-display', path: '/admin/ihm.php' },
	{ name: 'novoux-setup', path: '/custom/novoux/admin/setup.php' },
	{ name: 'calendar', path: '/comm/action/index.php?action=show_month' },
];

test.describe('Visual Regression', () => {
	test.beforeEach(async ({ page }) => {
		// Login
		await page.goto('/');
		// Dolibarr may redirect to /index.php if already logged in
		if (page.url().includes('login') || await page.locator('#username').isVisible().catch(() => false)) {
			await page.fill('#username', ADMIN_USER);
			await page.fill('#password', ADMIN_PASS);
			await page.click('input[type="submit"], button[type="submit"]');
			await page.waitForURL('**/index.php**');
		}
	});

	test('login-page', async ({ page, context }) => {
		// Clear session to see login page
		await context.clearCookies();
		await page.goto('/');
		await page.waitForSelector('#username');
		await expect(page).toHaveScreenshot('login-light.png');

		// Dark mode
		await page.evaluate(() => {
			document.documentElement.setAttribute('data-novo-scheme', 'dark');
		});
		await expect(page).toHaveScreenshot('login-dark.png');
	});

	for (const { name, path } of PAGES) {
		test(`${name}-light`, async ({ page }) => {
			await page.goto(path);
			await page.waitForLoadState('networkidle');
			await expect(page).toHaveScreenshot(`${name}-light.png`);
		});

		test(`${name}-dark`, async ({ page }) => {
			await page.goto(path);
			await page.waitForLoadState('networkidle');
			await page.evaluate(() => {
				document.documentElement.setAttribute('data-novo-scheme', 'dark');
			});
			// Let transitions settle
			await page.waitForTimeout(300);
			await expect(page).toHaveScreenshot(`${name}-dark.png`);
		});
	}
});
