#!/bin/bash
set -e

echo "Starting deployment..."

# Cache config (uses runtime env vars from Railway)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Create storage link if not exists
php artisan storage:link 2>/dev/null || true

# Fix permissions for runtime-generated cache files so www-data can read/write them
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

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

echo "Setting up Apache port..."
PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

echo "Starting server with Apache on port ${PORT}..."

# Start Apache in the foreground correctly
exec apache2-foreground