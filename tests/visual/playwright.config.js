// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
	testDir: '.',
	timeout: 30000,
	expect: {
		toHaveScreenshot: {
			maxDiffPixelRatio: 0.01,
		},
	},
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
	snapshotDir: './snapshots',
	outputDir: './test-results',
});
