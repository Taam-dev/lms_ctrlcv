# build assets vite
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# cài package composer
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# môi trường chạy php fpm và nginx
FROM php:8.3-fpm-alpine

# cài nginx và thư viện hệ thống
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    curl \
    bash

# cài php extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        gd \
        intl \
        zip \
        opcache

# thư mục làm việc
WORKDIR /var/www/html

# copy source code
COPY . /var/www/html

# copy thư viện vendor
COPY --from=vendor /app/vendor /var/www/html/vendor

# copy file build frontend
COPY --from=frontend /app/public/build /var/www/html/public/build

# cấu hình nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# cấp quyền chạy start.sh
COPY docker/start.sh /var/www/html/docker/start.sh
RUN chmod +x /var/www/html/docker/start.sh

# phân quyền storage và cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# mở port web
EXPOSE 80

# chạy web khi bật container
CMD ["/bin/sh", "/var/www/html/docker/start.sh"]
