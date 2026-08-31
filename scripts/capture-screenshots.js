/**
 * Capture the screenshot set used for the DoliStore listing and the README.
 *
 * Prerequisites: dev stack running, `scripts/seed-dev.sh` then
 * `scripts/seed-screenshots.sql` applied. See docs/developing.md.
 *
 *   node scripts/capture-screenshots.js
 */
const { chromium } = require('../node_modules/playwright-core');

const BASE = process.env.BASE_URL || 'http://localhost:8080';
const USER = process.env.DOLI_ADMIN_LOGIN || 'admin';
const PASS = process.env.DOLI_ADMIN_PASSWORD || 'admin123';
const OUT = 'docs/screenshots';

const VIEWS = [
	{ name: 'dashboard', path: '/index.php' },
	{ name: 'thirdparty-list', path: '/societe/list.php' },
	{ name: 'invoice-card', path: '/compta/facture/list.php' },
	{ name: 'novoux-settings', path: '/custom/novoux/admin/setup.php' },
];

(async () => {
	const browser = await chromium.launch();
	const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

	// Login page first, while logged out.
	await page.goto(BASE + '/');
	await page.waitForSelector('#username');
	await page.screenshot({ path: `${OUT}/login-light.png` });
	await page.evaluate(() => document.documentElement.setAttribute('data-novo-scheme', 'dark'));
	await page.waitForTimeout(300);
	await page.screenshot({ path: `${OUT}/login-dark.png` });
	await page.evaluate(() => document.documentElement.removeAttribute('data-novo-scheme'));

	await page.fill('#username', USER);
	await page.fill('#password', PASS);
	await page.click('input[type="submit"], button[type="submit"]');
	await page.waitForURL('**/index.php**');

	for (const { name, path } of VIEWS) {
		for (const scheme of ['light', 'dark']) {
			await page.goto(BASE + path);
			await page.waitForLoadState('networkidle');
			await page.evaluate((s) => {
				if (s === 'dark') document.documentElement.setAttribute('data-novo-scheme', 'dark');
				else document.documentElement.removeAttribute('data-novo-scheme');
			}, scheme);
			await page.waitForTimeout(400);
			await page.screenshot({ path: `${OUT}/${name}-${scheme}.png` });
			console.log(`captured ${name}-${scheme}`);
		}
	}

	await browser.close();
})();
