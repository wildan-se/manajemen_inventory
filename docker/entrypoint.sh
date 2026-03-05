#!/bin/sh
set -e

echo "=========================================="
echo "  Sistem Manajemen Inventori - Startup"
echo "=========================================="

# ---- 1. Menentukan Port ----
# Railway menyediakan $PORT secara dinamis. Default ke 80 jika tidak ada.
PORT="${PORT:-80}"
echo "[INFO] Server akan berjalan di port: $PORT"

# ---- 2. Update Nginx config dengan port yang benar ----
sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/http.d/default.conf
echo "[INFO] Nginx dikonfigurasi untuk listen di port $PORT"

# ---- 3. Cek apakah APP_KEY sudah ada ----
if [ -z "$APP_KEY" ]; then
    echo "[INFO] APP_KEY tidak ditemukan, men-generate..."
    php artisan key:generate --force
else
    echo "[INFO] APP_KEY sudah ada."
fi

# ---- 4. Clear dan Cache config untuk performance ----
echo "[INFO] Optimasi konfigurasi Laravel..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---- 5. Buat symlink storage ----
echo "[INFO] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ---- 6. Jalankan database migrations ----
echo "[INFO] Menjalankan database migrations..."
php artisan migrate --force --no-interaction

# ---- 7. Set permissions ----
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=========================================="
echo "  Setup selesai! Menjalankan server..."
echo "=========================================="

# ---- 8. Jalankan Supervisor (Nginx + PHP-FPM) ----
exec /usr/bin/supervisord -c /etc/supervisord.conf
