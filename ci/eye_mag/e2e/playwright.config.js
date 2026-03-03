// @ts-check
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 90 * 1000,
  expect: {
    timeout: 20 * 1000
  },
  fullyParallel: false,
  retries: 0,
  reporter: [['list']],
  use: {
    headless: true,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure'
  }
});
