# Домівка – вебсистема прилаштування тварин притулку

Каталог котів і собак, які шукають дім, анкета «Взяти додому» без реєстрації, кабінет волонтера з розміщенням знайдених тварин, панель адміністратора притулку з обробкою анкет, модерацією та блоговим конструктором статей.

## Стек

| Рівень | Технології |
|---|---|
| Сервер | PHP 8.5, Laravel 13, Livewire 4, Laravel Fortify (автентифікація) |
| Інтерфейс | Blade, Livewire, Alpine.js, Tailwind CSS 4, шрифти Nunito та Inter (локально) |
| Дані | PostgreSQL 17 |
| Інфраструктура | Docker Compose: nginx, php-fpm, черга, PostgreSQL, Mailpit |
| Тести | Pest 4 (модульні та функціональні тести на PostgreSQL) |

Точні версії залежностей зафіксовано в `composer.lock` і `package-lock.json`.

## Запуск через Docker (рекомендовано)

Потрібні лише [Docker Desktop](https://www.docker.com/products/docker-desktop/) і Git.

```bash
git clone https://github.com/MaruSoul/shelter-adoption.git
cd shelter-adoption

# 1. Налаштування середовища і ключ шифрування застосунку
cp .env.example .env
sed -i.bak "s|^APP_KEY=.*|APP_KEY=base64:$(openssl rand -base64 32)|" .env && rm .env.bak

# 2. Збирання образів і запуск (перший раз – кілька хвилин)
docker compose up -d --build

# 3. Схема бази й демонстраційні дані
docker compose exec app php artisan migrate --seed --force
```

Після запуску:

| Адреса | Що там |
|---|---|
| http://localhost:8080 | сайт |
| http://localhost:8080/admin | панель адміністратора притулку |
| http://localhost:8025 | Mailpit – усі листи, які надсилає сайт |

Міграції бази даних виконуються автоматично під час старту контейнера `app`.

### Демонстраційні акаунти

| Роль | E-mail | Пароль |
|---|---|---|
| Адміністратор притулку | admin@domivka.local | admin-password |
| Волонтер (має розміщені тварини та анкету) | volunteer@domivka.local | user-password |
| Інший користувач (для перевірки прав доступу) | andrii@domivka.local | user-password |

> Дані синтетичні. Фото тварин у демонстраційному наборі – згенеровані заглушки.

### Корисні команди

```bash
docker compose logs -f app                       # журнал застосунку
docker compose exec app php artisan migrate:fresh --seed --force   # перестворити базу з демо-даними
docker compose down                              # зупинити
docker compose down -v                           # зупинити й видалити дані (база, фото)
```

## Автоматичні тести

Тести працюють з окремою базою `shelter_test` (створюється автоматично під час першого запуску PostgreSQL) і ніколи не чіпають основну базу.

```bash
docker compose --profile tests run --rm tests
```

Що покривають тести:

- каталог: видимість за статусами, фільтри за видом і віковими групами, порожня вибірка, пагінація;
- анкета: успішне подання гостем, перевірка полів, нормалізація телефону, захист від дублікатів, поле-пастка для ботів, обмеження частоти, прилаштована тварина;
- бізнес-процес: матриця переходів статусів (усі 36 пар), прилаштування із закриттям інших анкет, гонка двох прилаштувань на рівні СУБД;
- права доступу: панель лише для адміністратора, кабінет лише з підтвердженою поштою, чужі анкети й картки, роль через реєстрацію;
- модерація карток волонтера, завантаження фото, блоковий конструктор, екранування HTML (XSS), sitemap.

## Локальний запуск без Docker

Потрібні PHP 8.3+ з розширеннями `pdo_pgsql`, `gd`, `intl`, `zip`, `exif`, Composer, Node.js 22 і PostgreSQL.

```bash
composer install
npm ci && npm run build
cp .env.example .env            # змінити DB_HOST=127.0.0.1, MAIL_MAILER=log
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve               # http://127.0.0.1:8000
php artisan queue:work          # в окремому терміналі – мініатюри фото й листи
./vendor/bin/pest               # тести (потрібна база shelter_test)
```

## Структура проєкту

```
app/
  Enums/          статуси й довідники-переліки (матриця переходів анкети – ApplicationStatus)
  Livewire/       сторінки з інтерактивністю: каталог, анкета, кабінет, панель адміністратора
  Services/       бізнес-логіка: подання анкети, зміна статусів і прилаштування, фото, блоки статей
  Policies/       правила доступу до тварин і анкет
  Models/         моделі Eloquent
database/
  migrations/     схема БД (зокрема часткові унікальні індекси)
  seeders/        демонстраційні дані
resources/views/  шаблони Blade
docker/           Dockerfile, nginx, PostgreSQL
tests/            автотести Pest
```

## Розгортання на сервері

На VPS з Docker достатньо тих самих кроків, що й локально, але в `.env`:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ваш-домен
MAIL_MAILER=smtp        # і реквізити транзакційного поштового сервісу
```

HTTPS зручно забезпечити зворотним проксі (наприклад, Caddy) перед контейнером `web`.
