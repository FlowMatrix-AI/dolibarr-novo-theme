// @ts-check
const { defineConfig } = require('@playwright/test');

// Smoke suite: asserts the theme actually loads and pages render without PHP
// errors. Unlike tests/visual/, it does no screenshot comparison, so it needs
// no committed baselines and is safe to run in CI.
module.exports = defineConfig({
	testDir: '.',
	timeout: 30000,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI ? [['list'], ['github']] : [['list']],
	use: {
		baseURL: process.env.BASE_URL || 'http://localhost:8080',
		screenshot: 'only-on-failure',
		viewport: { width: 1280, height: 900 },
	},
	projects: [
		{
			name: 'chromium',
			use: { browserName: 'chromium' },
		},
	],
	outputDir: './test-results',
});
