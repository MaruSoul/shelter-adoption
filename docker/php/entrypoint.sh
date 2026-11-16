#!/bin/sh
set -e

# Каталоги сховища у томі (том монтується порожнім при першому запуску)
mkdir -p storage/app/public storage/app/private
chown -R www-data:www-data storage/app

# Міграції запускає лише основний контейнер застосунку
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
