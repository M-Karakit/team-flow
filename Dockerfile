FROM php:8.4-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Generate optimized caches
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

# Set permissions
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Cache config (needs runtime env vars)\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Create storage link if not exists\n\
php artisan storage:link 2>/dev/null || true\n\
\n\
# Run migrations\n\
php artisan migrate --force\n\
\n\
# Seed database (skip if already seeded)\n\
php artisan db:seed --force 2>/dev/null || true\n\
\n\
# Start the server\n\
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}\n\
' > /var/www/entrypoint.sh && chmod +x /var/www/entrypoint.sh

EXPOSE 8000

CMD ["/var/www/entrypoint.sh"]
