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
else
    echo "Skipping seeders"
fi

echo "Starting server on port ${PORT:-8000}..."

# Use --no-reload to enable multiple workers
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000} --no-reload
```

---

## Also Add to Railway Variables
```
PHP_CLI_SERVER_WORKERS = 4