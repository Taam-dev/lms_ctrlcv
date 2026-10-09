# ==========================================
# GIAI ĐOẠN 1: Biên dịch Frontend Assets (Vite)
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

# ==========================================
# GIAI ĐOẠN 2: Cài đặt Composer Dependencies
# ==========================================
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# ==========================================
# GIAI ĐOẠN 3: Runtime Image (PHP 8.3 FPM + Nginx)
# ==========================================
FROM php:8.3-fpm-alpine

# Cài đặt Nginx và các thư viện hệ thống cần thiết cho extensions
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

# Cấu hình và cài đặt đầy đủ PHP extensions: pdo_mysql, mbstring, bcmath, exif, gd, intl, zip, opcache
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

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy mã nguồn dự án
COPY . /var/www/html

# Copy vendor từ giai đoạn Composer
COPY --from=vendor /app/vendor /var/www/html/vendor

# Copy build assets từ giai đoạn Frontend
COPY --from=frontend /app/public/build /var/www/html/public/build

# Cấu hình Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Cấp quyền thực thi cho start script
COPY docker/start.sh /var/www/html/docker/start.sh
RUN chmod +x /var/www/html/docker/start.sh

# Thiết lập quyền hạn cho www-data
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Cổng mặc định
EXPOSE 80

# Chạy script khởi động
CMD ["/bin/sh", "/var/www/html/docker/start.sh"]
