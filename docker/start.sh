#!/bin/sh
set -e

echo "==> [Railway Deployment] Khởi động ứng dụng Ctrl C+V LMS..."

# Thiết lập cổng linh hoạt theo biến PORT của Railway (mặc định 80)
PORT="${PORT:-80}"
echo "==> Cấu hình Nginx lắng nghe tại cổng: $PORT"

if [ -f /etc/nginx/http.d/default.conf ]; then
    sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/http.d/default.conf
elif [ -f /etc/nginx/conf.d/default.conf ]; then
    sed -i "s/LISTEN_PORT/$PORT/g" /etc/nginx/conf.d/default.conf
fi

# Đảm bảo các thư mục cache và session tồn tại và có quyền ghi
echo "==> Cấp quyền thư mục storage và bootstrap/cache..."
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Tạo symlink storage công khai
echo "==> Chạy php artisan storage:link..."
php artisan storage:link --force || true

# Chạy migration an toàn
echo "==> Chạy php artisan migrate --force..."
php artisan migrate --force || true

# Tối ưu hóa bộ nhớ đệm (caching) cho môi trường production
echo "==> Tối ưu hóa cấu hình, routes và views cho Production..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Khởi động PHP-FPM chạy nền
echo "==> Bật PHP-FPM..."
php-fpm -D

# Khởi động Nginx chạy chính (foreground process)
echo "==> Bật Nginx Web Server..."
exec nginx -g "daemon off;"
