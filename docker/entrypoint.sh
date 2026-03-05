#!/bin/sh
set -e

echo "=========================================="
echo "  Sistem Manajemen Inventori - Startup"
echo "=========================================="

# ---- 1. Menentukan Port ----
# Railway menyediakan $PORT secara dinamis. Default ke 8080 jika tidak ada.
PORT="${PORT:-8080}"
echo "[INFO] Server akan berjalan di port: $PORT"

# ---- 2. Update Nginx config dengan port yang benar ----
# Tulis ulang config file agar tidak bergantung pada 'sed' BusyBox Alpine
cat > /etc/nginx/http.d/default.conf << NGINX_EOF
server {
    listen ${PORT};
    server_name localhost;
    root /var/www/public;

    index index.php index.html;

    server_tokens off;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX_EOF
echo "[INFO] Nginx dikonfigurasi untuk listen di port $PORT"

# ---- 3. Cek apakah APP_KEY sudah ada ----
if [ -z "$APP_KEY" ]; then
    echo "[INFO] APP_KEY tidak ditemukan, men-generate..."
    php artisan key:generate --force
else
    echo "[INFO] APP_KEY sudah ada."
fi

# ---- 4. Buat symlink storage ----
echo "[INFO] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ---- 5. Clear cache lama ----
echo "[INFO] Membersihkan cache lama..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# ---- 6. Jalankan database migrations ----
echo "[INFO] Menjalankan database migrations..."
php artisan migrate --force --no-interaction

# ---- 7. Cache konfigurasi untuk performance (setelah DB siap) ----
echo "[INFO] Optimasi konfigurasi Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---- 8. Set permissions ----
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=========================================="
echo "  Setup selesai! Menjalankan server..."
echo "=========================================="

# ---- 9. Jalankan Supervisor (Nginx + PHP-FPM) ----
exec /usr/bin/supervisord -c /etc/supervisord.conf
