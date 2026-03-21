FROM php:8.4-apache

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

# Apache configurations
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN echo "<Directory ${APACHE_DOCUMENT_ROOT}>\n\tAllowOverride All\n</Directory>" > /etc/apache2/conf-available/laravel.conf && a2enconf laravel

# Set working directory
WORKDIR /var/www

# Copy composer files first (better layer caching)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Cache bust to force Railway to build a fresh image
ARG CACHEBUST=1
ENV CACHEBUST=2026-03-21_05-00

# Copy the rest of the project
COPY . .

# Run composer scripts (post-install hooks)
RUN composer run-script post-autoload-dump 2>/dev/null || true

# Fix permissions for apache and framework
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Copy and set permissions for entrypoint
COPY docker/entrypoint.sh /var/www/entrypoint.sh
RUN chmod +x /var/www/entrypoint.sh

# Fallback default port
EXPOSE 80

CMD ["/var/www/entrypoint.sh"]
