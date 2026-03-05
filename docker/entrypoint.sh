#!/bin/sh
set -e

echo "=========================================="
echo "  Sistem Manajemen Inventori - Startup"
echo "=========================================="

# ---- 1. Menentukan Port ----
PORT="${PORT:-8080}"
echo "[INFO] Server akan berjalan di port: $PORT"

# ---- 2. DEBUG: Tampilkan env vars DB (tanpa password) ----
echo "[DEBUG] DB_CONNECTION = ${DB_CONNECTION}"
echo "[DEBUG] DB_HOST       = ${DB_HOST}"
echo "[DEBUG] DB_PORT       = ${DB_PORT}"
echo "[DEBUG] DB_DATABASE   = ${DB_DATABASE}"
echo "[DEBUG] DB_USERNAME   = ${DB_USERNAME}"
echo "[DEBUG] DB_PASSWORD   = (hidden)"

# ---- 3. Update Nginx config dengan port yang benar ----
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

# ---- 4. Cek APP_KEY ----
if [ -z "$APP_KEY" ]; then
    echo "[INFO] APP_KEY tidak ditemukan, men-generate..."
    php artisan key:generate --force
else
    echo "[INFO] APP_KEY sudah ada."
fi

# ---- 5. Buat symlink storage ----
echo "[INFO] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ---- 6. Clear cache lama ----
echo "[INFO] Membersihkan cache lama..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# ---- 7. Jalankan database migrations ----
echo "[INFO] Menjalankan database migrations..."
if php artisan migrate --force --no-interaction; then
    echo "[INFO] Migration berhasil!"
else
    echo "[WARNING] Migration gagal - aplikasi tetap dijalankan, cek variabel DB!"
fi

# ---- 8. Cache konfigurasi untuk performance ----
echo "[INFO] Optimasi konfigurasi Laravel..."
php artisan config:cache || echo "[WARNING] config:cache gagal, lanjut tanpa cache"
php artisan route:cache  || echo "[WARNING] route:cache gagal, lanjut tanpa cache"
php artisan view:cache   || echo "[WARNING] view:cache gagal, lanjut tanpa cache"

# ---- 9. Set permissions ----
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=========================================="
echo "  Setup selesai! Menjalankan server..."
echo "=========================================="

# ---- 10. Jalankan Supervisor (Nginx + PHP-FPM) ----
exec /usr/bin/supervisord -c /etc/supervisord.conf
