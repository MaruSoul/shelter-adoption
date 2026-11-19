import { test, expect } from '@playwright/test';

// Обовʼязковий наскрізний сценарій із завдання (FR-01, FR-02, FR-03, FR-06–FR-08)
test.describe.configure({ mode: 'serial' });

const email = `e2e-${Date.now()}@example.com`;
let animalUrl;
let animalName;

test('гість фільтрує каталог, відкриває тварину й подає анкету', async ({ page }, info) => {
    test.skip(info.project.name !== 'desktop', 'анкету подаємо один раз');

    await page.goto('/tvaryny');
    await page.getByRole('link', { name: 'Котики' }).first().click();
    await expect(page).toHaveURL(/\/tvaryny\/cat/);
    await page.getByLabel('Стерилізовані').check();
    await expect(page).toHaveURL(/sterilized=(1|true)/);

    const firstCard = page.locator('.animal-grid a').first();
    animalName = (await firstCard.locator('.font-display').innerText()).trim();
    await firstCard.click();
    await expect(page.getByRole('heading', { level: 1 })).toHaveText(animalName);
    animalUrl = page.url();

    await page.getByRole('link', { name: 'Взяти додому' }).first().click();

    // Спершу порожня форма: помилки біля полів, анкета не створюється
    await page.getByRole('button', { name: /Відправити анкету/ }).click();
    await expect(page.locator('[aria-invalid="true"]').first()).toBeVisible();

    await page.getByLabel('Ваше імʼя *').fill('Тестова Людина');
    await page.getByLabel('Телефон *').fill('050 123 45 67');
    await page.getByLabel('Електронна пошта *').fill(email);
    await page.getByLabel('Область *').selectOption({ label: 'Одеська' });
    await page.getByText('Telegram', { exact: true }).click();
    await page.getByLabel(/погоджуюсь з/).check();
    await page.getByRole('button', { name: /Відправити анкету/ }).click();

    await expect(page.getByText(`${animalName} уже чекає на знайомство з вами.`)).toBeVisible();
});

test('адміністратор проводить анкету до прилаштування', async ({ page }, info) => {
    test.skip(info.project.name !== 'desktop', 'один прохід');

    await page.goto('/login');
    await page.getByLabel('Електронна пошта').fill('admin@domivka.local');
    await page.getByLabel('Пароль').fill('admin-password');
    await page.getByRole('button', { name: 'Увійти' }).click();

    await page.goto('/admin/ankety?email=' + encodeURIComponent(email));
    await page.getByRole('link', { name: /Відкрити/ }).first().click();
    await expect(page.getByRole('heading', { level: 1 })).toHaveText('Тестова Людина');
    await expect(page.getByRole('link', { name: 'Написати в Telegram' })).toBeVisible();

    await page.getByRole('button', { name: '→ В роботі' }).click();
    await expect(page.getByText('В роботі').first()).toBeVisible();
    await page.getByRole('button', { name: '→ Знайомство призначено' }).click();
    await page.getByPlaceholder('Видно лише адміністраторам').fill('Зустріч у суботу о 12:00');
    await page.getByRole('button', { name: 'Зберегти нотатки' }).click();

    page.once('dialog', (dialog) => dialog.accept());
    await page.getByRole('button', { name: '→ Прилаштовано' }).click();
    await expect(page.getByText(/Ура!/)).toBeVisible();
});

test('прилаштована тварина більше не приймає анкет', async ({ page }) => {
    test.skip(!animalUrl, 'потрібен прохід попередніх кроків');

    await page.goto(animalUrl);
    await expect(page.getByText(/вже (знайшла|знайшов) дім/).first()).toBeVisible();
    await expect(page.getByRole('link', { name: 'Взяти додому' })).toHaveCount(0);
});

test('каталог без горизонтальної прокрутки й доступний з клавіатури', async ({ page }) => {
    await page.goto('/tvaryny');
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(overflow).toBeLessThanOrEqual(0);

    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Перейти до змісту' })).toBeFocused();
});
