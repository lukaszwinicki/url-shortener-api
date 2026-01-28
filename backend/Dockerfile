FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    linux-headers \
    autoconf \
    g++ \
    make \
    git \
    unzip

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    opcache

# Install PECL extensions
# Note: php-amqp requires rabbitmq-c-dev
RUN apk add --no-cache rabbitmq-c-dev && \
    pecl install amqp && \
    docker-php-ext-enable amqp

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Set permissions
RUN chown -R www-data:www-data /var/www/html/var

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
