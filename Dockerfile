FROM php:8.4-fpm-alpine

# Install system dependencies, Nginx, and Supervisor
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    oniguruma-dev \
    bash \
    curl \
    git

# Configure and install PHP extensions
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    intl \
    opcache \
    zip \
    mbstring \
    gd \
    exif \
    pcntl

# PHP production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Override default upload limits
RUN echo "upload_max_filesize = 25M" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "post_max_size = 30M" >> $PHP_INI_DIR/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> $PHP_INI_DIR/conf.d/uploads.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies securely and efficiently
ENV COMPOSER_ALLOW_SUPERUSER=1

# Copy composer files to leverage Docker cache
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --no-scripts --no-autoloader

# Copy application files
COPY . .

# Generate autoloader and run scripts (e.g., package:discover)
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set strict permissions for storage and cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Copy server configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Setup entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose HTTP port
EXPOSE 80

# Define the entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
