// Наскрізні тести (Playwright). Працюють із запущеним сайтом і демо-даними:
//   docker compose exec app php artisan migrate:fresh --seed --force
//   npx playwright test
// Адресу сайту можна змінити змінною E2E_BASE_URL.
import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 60_000,
    workers: 1,
    reporter: [['list'], ['html', { open: 'never', outputFolder: 'storage/e2e-report' }]],
    use: {
        baseURL: process.env.E2E_BASE_URL ?? 'http://localhost:8080',
        locale: 'uk-UA',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        // Системний Chrome, якщо задано шлях; інакше – браузер, встановлений через npx playwright install
        launchOptions: process.env.CHROME_PATH ? { executablePath: process.env.CHROME_PATH } : {},
    },
    projects: [
        { name: 'desktop', use: { viewport: { width: 1280, height: 900 } } },
        { name: 'mobile', use: { viewport: { width: 360, height: 780 }, isMobile: true, hasTouch: true } },
    ],
});
