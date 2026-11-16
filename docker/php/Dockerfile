# Етап 1: збірка фронтенду (Vite + Tailwind)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
COPY vite.config.js ./
COPY resources ./resources
COPY app ./app
COPY public ./public
RUN npm run build

# Етап 2: PHP-FPM із розширеннями для Laravel і PostgreSQL
FROM php:8.5-fpm-alpine AS app

RUN apk add --no-cache icu-libs libzip libpng libjpeg-turbo libwebp freetype libpq \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev libpng-dev \
       libjpeg-turbo-dev libwebp-dev freetype-dev libpq-dev linux-headers \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql intl zip gd exif bcmath pcntl \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

# Етап 3: образ для запуску автотестів (додає dev-залежності, зокрема Pest)
FROM app AS tests
RUN composer install --prefer-dist --no-interaction
ENTRYPOINT []
CMD ["php", "vendor/bin/pest"]

# Етап 4: веб-сервер nginx зі статичними файлами застосунку
FROM nginx:1.29-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
