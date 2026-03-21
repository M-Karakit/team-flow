#!/bin/bash
set -e

echo "Starting deployment..."

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan storage:link 2>/dev/null || true

echo "Running migrations..."
php artisan migrate --force || true

if [ "${SEED_ON_DEPLOY}" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force 2>/dev/null || true
fi

# Use Railway's assigned PORT
echo "Starting server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=$PORT --no-reload