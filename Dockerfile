FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (better layer caching)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Cache bust to force Railway to build a fresh image
ARG CACHEBUST=1
ENV CACHEBUST=2026-03-21_05-15

# Copy the rest of the project
COPY . .

# Run composer scripts (post-install hooks)
RUN composer run-script post-autoload-dump 2>/dev/null || true

# Set permissions
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy and set permissions for entrypoint
COPY docker/entrypoint.sh /var/www/entrypoint.sh
RUN chmod +x /var/www/entrypoint.sh

EXPOSE $PORT

CMD ["/var/www/entrypoint.sh"]
