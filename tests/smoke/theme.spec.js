// @ts-check
const { test, expect } = require('@playwright/test');

const ADMIN_USER = process.env.DOLI_ADMIN_LOGIN || 'admin';
const ADMIN_PASS = process.env.DOLI_ADMIN_PASSWORD || 'admin123';

/**
 * Markers that indicate PHP blew up rather than rendering. Note that
 * display_errors is off in the dolibarr image, so a fatal usually surfaces as a
 * 500 with an empty body — the status and content-type assertions are the
 * primary guard and this regex is a secondary net for when output does leak.
 * Diagnostics are matched in their PHP-prefixed form; bare "Warning:" and
 * friends occur in legitimate theme comments and Dolibarr UI copy.
 */
const PHP_ERROR = /(Fatal error|Parse error|Uncaught Error|Recoverable fatal error|Failed opening required|Include of main fails|PHP Warning|PHP Notice|PHP Deprecated)/;

/**
 * The module must work from both supported install roots (Dolibarr packaging
 * rule). htdocs/custom/novoux/ is the compose bind mount and always present;
 * htdocs/novoux/ has to be deployed separately, so it is opt-in via
 * TEST_SECOND_ROOT=1 (CI sets it after copying the module there).
 */
const INSTALL_ROOTS = ['/custom/novoux'];
if (process.env.TEST_SECOND_ROOT === '1') {
	INSTALL_ROOTS.push('/novoux');
}

/** Pages that must render with the theme active. */
const PAGES = [
	'/index.php',
	'/societe/list.php',
	'/compta/facture/list.php',
	'/user/card.php?id=1',
	'/admin/ihm.php',
	'/custom/novoux/admin/setup.php',
];

test.describe('Theme entrypoints', () => {
	for (const root of INSTALL_ROOTS) {
		test(`style.css.php serves CSS from ${root}`, async ({ request }) => {
			const res = await request.get(`${root}/theme/novo/style.css.php?theme=novo&entity=1`);
			expect(res.status(), `${root}/theme/novo/style.css.php must not 500`).toBe(200);
			expect(res.headers()['content-type']).toContain('text/css');

			const body = await res.text();
			expect(body).not.toMatch(PHP_ERROR);
			// Proves the token layer actually rendered, not just that PHP exited 0.
			expect(body).toContain('--novo-primary');
		});

		test(`manifest.json.php serves JSON from ${root}`, async ({ request }) => {
			const res = await request.get(`${root}/theme/novo/manifest.json.php?entity=1`);
			expect(res.status()).toBe(200);

			const body = await res.text();
			expect(body).not.toMatch(PHP_ERROR);
			expect(() => JSON.parse(body)).not.toThrow();
		});

	}
});

test.describe('Authenticated pages with the novo theme active', () => {
	test.beforeEach(async ({ page }) => {
		await page.goto('/');
		if (await page.locator('#username').isVisible().catch(() => false)) {
			await page.fill('#username', ADMIN_USER);
			await page.fill('#password', ADMIN_PASS);
			await page.click('input[type="submit"], button[type="submit"]');
			await page.waitForURL('**/index.php**');
		}
		// Dolibarr redirects a failed login back to a 200 login page, and every
		// protected page then 302s to it — so without this the page assertions
		// below would all pass while logged out.
		await expect(page.locator('#username')).toHaveCount(0);
	});

	for (const path of PAGES) {
		test(`${path} loads without PHP errors`, async ({ page }) => {
			/** @type {string[]} */
			const failed = [];
			// A 500 on the stylesheet is exactly how #12 presented: the page still
			// returns 200 while the theme silently fails to load.
			page.on('response', (res) => {
				if (res.status() >= 400 && /novoux/.test(res.url())) {
					failed.push(`${res.status()} ${res.url()}`);
				}
			});

			const res = await page.goto(path);
			expect(res?.status()).toBe(200);
			expect(await page.content()).not.toMatch(PHP_ERROR);
			expect(failed, 'novoux assets must all load').toEqual([]);
		});
	}

	// Unlike style.css.php this page does not set NOLOGIN, so it is only
	// reachable with a session — hence the authenticated context.
	for (const root of INSTALL_ROOTS) {
		test(`novo-inject.css.php serves CSS from ${root}`, async ({ page }) => {
			const res = await page.request.get(`${root}/css/novo-inject.css.php?entity=1`);
			expect(res.status()).toBe(200);
			expect(res.headers()['content-type']).toContain('text/css');
			expect(await res.text()).not.toMatch(PHP_ERROR);
		});
	}

	test('novo stylesheet is the one actually applied', async ({ page }) => {
		await page.goto('/index.php');
		const hrefs = await page.locator('link[rel="stylesheet"]').evaluateAll(
			(els) => els.map((e) => e.getAttribute('href') || '')
		);
		expect(hrefs.some((h) => h.includes('novoux') && h.includes('style.css.php'))).toBe(true);
		// module_parts['css'] must be registered too, otherwise the palette /
		// density / logo override layer is dead on every real page.
		expect(hrefs.some((h) => h.includes('novo-inject.css.php'))).toBe(true);

		// The --novo-* custom properties must resolve in the browser, which only
		// happens if style.css.php returned real CSS.
		const primary = await page.evaluate(
			() => getComputedStyle(document.documentElement).getPropertyValue('--novo-primary').trim()
		);
		expect(primary).not.toBe('');
	});
});
