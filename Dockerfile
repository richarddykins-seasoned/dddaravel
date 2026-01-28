# Use the official FrankenPHP image as base
FROM dunglas/frankenphp:latest as base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    aspell \
    aspell-en \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Development stage
FROM base as development

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-cache --prefer-dist --no-interaction --no-scripts --no-autoloader

# Copy application code
COPY . .

# Generate autoloader and run scripts
RUN composer dump-autoload --optimize && \
    php artisan package:discover --ansi

# Copy FrankenPHP configuration
COPY .frankenphp.yaml /app/.frankenphp.yaml

# Copy Caddyfile for web server
COPY Caddyfile /etc/caddy/Caddyfile

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Expose ports (HTTP app, HTTPS, HTTP/3 UDP, Caddy admin)
EXPOSE 8000 443 2019
EXPOSE 443/udp

# Start Laravel Octane with FrankenPHP using the custom Caddyfile
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000", "--admin-port=2019", "--caddyfile=/etc/caddy/Caddyfile", "--no-interaction"]

# Production stage
FROM base as production

# Copy composer files
COPY composer.json composer.lock ./

# Install production dependencies
RUN composer install --no-cache --prefer-dist --no-interaction --no-scripts --no-autoloader --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Generate optimized autoloader and cache config
RUN composer dump-autoload --optimize && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Set permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage \
    && chmod -R 755 /app/bootstrap/cache

# Copy FrankenPHP configuration
COPY .frankenphp.yaml /app/.frankenphp.yaml

# Expose ports (HTTP app, HTTPS, Caddy admin)
EXPOSE 8000 443 2019
EXPOSE 443/udp

# Health check (use the app on port 8000 which our Caddyfile serves for localhost)
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -fsS http://localhost:8000/ || exit 1

# Use www-data user
USER www-data

# Start Laravel Octane with FrankenPHP using the custom Caddyfile
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000", "--admin-port=2019", "--caddyfile=/etc/caddy/Caddyfile", "--no-interaction"]
