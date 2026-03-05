# Menggunakan image base PHP 8.2 resmi yang super ringan (Alpine Linux) lengkap dengan PHP-FPM
FROM php:8.2-fpm-alpine

# Set working directory di dalam container
WORKDIR /var/www

# Install dependensi sistem yang dibutuhkan Laravel (alpine package manager: apk)
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    git \
    supervisor \
    nginx \
    dos2unix

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo pdo_mysql gd xml bcmath

# Copy Composer dari image composer resmi yang super kecil
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy semua file project Anda dari lokal ke dalam container
COPY . /var/www

# Berikan hak akses (permissions) ke user web server untuk folder krusial Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Install dependencies composer (Pustaka PHP)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Install & Build Vite/NodeJS (Pustaka Frontend/CSS)
RUN npm install \
    && npm run build

# Copy konfigurasi custom Nginx (Server Web) ke dalam container
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy konfigurasi Supervisor (Menjalankan Nginx dan PHP-FPM bersamaan di satu container)
COPY ./docker/supervisor/supervisord.conf /etc/supervisord.conf

# Copy entrypoint script
COPY ./docker/entrypoint.sh /entrypoint.sh

# PENTING: Konversi SEMUA script dari Windows CRLF ke Unix LF
# Ini mencegah error "/bin/sh^M: not found" dan config parsing errors di Linux
RUN dos2unix /entrypoint.sh /etc/supervisord.conf \
    && chmod +x /entrypoint.sh

# Expose port default (Railway akan override lewat $PORT env var)
EXPOSE 8080

# Jalankan entrypoint script (setup otomatis + start server)
CMD ["/entrypoint.sh"]
