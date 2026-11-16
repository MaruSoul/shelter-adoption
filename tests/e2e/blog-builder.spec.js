import { test, expect } from '@playwright/test';

// FR-15: блоковий конструктор – додавання, переставляння, попередній перегляд, публікація, екранування HTML
test('адміністратор збирає статтю з блоків і публікує її', async ({ page }, info) => {
    test.skip(info.project.name !== 'desktop', 'один прохід');

    await page.goto('/login');
    await page.getByLabel('Електронна пошта').fill('admin@domivka.local');
    await page.getByLabel('Пароль').fill('admin-password');
    await page.getByRole('button', { name: 'Увійти' }).click();

    await page.goto('/admin/statti/nova');
    // Адреса сторінки формується із заголовка автоматично
    await page.getByLabel('Заголовок *').fill(`Перевірка конструктора ${Date.now()}`);
    await Promise.all([
        page.waitForResponse((r) => r.url().includes('/livewire') && r.request().method() === 'POST'),
        page.getByLabel(/Анонс/).click(),
    ]);
    await expect(page.getByLabel(/Адреса сторінки/)).toHaveValue(/perevirka-konstruktora-\d+/, { timeout: 15000 });
    const slug = await page.getByLabel(/Адреса сторінки/).inputValue();
    await page.getByLabel('Рубрика *').selectOption({ index: 1 });

    // Нова стаття вже має порожній блок тексту
    await page.getByLabel('Текст абзацу').first().fill('<script>alert(1)</script> Другий за порядком абзац');
    await page.getByRole('button', { name: 'Заголовок', exact: true }).click();
    await page.getByPlaceholder('Текст заголовка').fill('Перший після переставляння');
    // Заголовок додано другим – переносимо його вгору
    await page.getByRole('button', { name: 'Перемістити вгору' }).last().click();
    await expect(page.getByPlaceholder('Текст заголовка')).toHaveValue('Перший після переставляння');

    await page.getByRole('tab', { name: 'Попередній перегляд' }).click();
    await expect(page.getByRole('heading', { name: 'Перший після переставляння' })).toBeVisible();

    await page.getByRole('tab', { name: 'Редактор' }).click();
    await page.getByRole('button', { name: 'Опублікувати' }).click();
    await expect(page.getByText('Статтю опубліковано.')).toBeVisible();

    await page.goto('/blog/' + slug);
    const text = await page.locator('article').innerText();
    expect(text.indexOf('Перший після переставляння')).toBeLessThan(text.indexOf('Другий за порядком абзац'));
    await expect(page.getByText('<script>alert(1)</script>', { exact: false })).toBeVisible();
});
