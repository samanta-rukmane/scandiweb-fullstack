FROM php:8.2-cli

RUN apt-get update && apt-get install -y unzip git ca-certificates \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY backend/ .
RUN composer install --no-dev --optimize-autoloader

CMD php -S 0.0.0.0:${PORT:-8000} -t public
