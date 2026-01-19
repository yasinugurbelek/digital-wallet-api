FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    redis-tools

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Dockerfile içindeki 21. satırı bununla değiştirin:
RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]
