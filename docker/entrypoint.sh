#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache

exec "$@"