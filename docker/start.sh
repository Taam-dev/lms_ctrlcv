#!/bin/sh
set -e

echo "==> [Railway Deployment] Khởi động ứng dụng Ctrl C+V LMS..."

# lấy port railway hoặc mặc định 80
PORT="${PORT:-80}"
echo "==> Cấu hình Nginx lắng nghe tại cổng: $PORT"

if [ -f /etc/nginx/http.d/default.conf ]; then
    sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/http.d/default.conf
elif [ -f /etc/nginx/conf.d/default.conf ]; then
    sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/conf.d/default.conf
fi

# tạo thư mục cache session và cấp quyền ghi
echo "==> Cấp quyền thư mục storage và bootstrap/cache..."
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache storage/app/public/avatars storage/app/public/courses
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# link storage
echo "==> Chạy php artisan storage:link..."
php artisan storage:link --force || true

# chạy migration
echo "==> Chạy php artisan migrate --force..."
php artisan migrate --force || true

# cache config route view
echo "==> Tối ưu hóa cấu hình, routes và views cho Production..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# bật php-fpm chạy ngầm
echo "==> Bật PHP-FPM..."
php-fpm -D

# bật nginx
echo "==> Bật Nginx Web Server..."
exec nginx -g "daemon off;"
