# Dockerfile
# Use the official PHP image with FPM
FROM php:8.4-fpm

LABEL authors="abbass"

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libssl-dev \
    gettext \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pgsql \
    zip \
    intl \
    mbstring \
    exif \
    pcntl \
    bcmath \
    opcache \
    sockets \
    gd \
    gettext  # Added gettext extension

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug (for development)
# RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a non-root user and switch to it
RUN groupadd -g 1000 appuser && \
    useradd -u 1000 -g appuser -m appuser && \
    chown -R appuser:appuser /var/www/html

USER appuser

# Copy application files (commented out as you're using volumes)
# COPY --chown=appuser:appuser . .

# Expose the port that PHP-FPM is listening on
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
