#!/bin/bash
set -e

echo "Starting deployment..."

# Cache config (uses runtime env vars from Railway)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Create storage link if not exists
php artisan storage:link 2>/dev/null || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force || true

# Seed database only if explicitly requested
if [ "${SEED_ON_DEPLOY}" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force 2>/dev/null || true
else
    echo "Skipping seeders (set SEED_ON_DEPLOY=true to run)"
fi

echo "Starting server on port ${PORT:-8000}..."

# PHP built-in server workers to prevent bad gateway dropping
export PHP_CLI_SERVER_WORKERS=4

# Start the server — exec replaces the shell so PHP is PID 1
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}