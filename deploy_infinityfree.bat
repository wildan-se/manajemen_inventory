@echo off
echo ==========================================
echo  Membuat build untuk InfinityFree...
echo ==========================================

echo [1/4] Menghapus sisa cache konfigurasi...
call php artisan optimize:clear

echo [2/4] Menginstall PHP vendor (Tanpa dependensi dev)...
call composer install --optimize-autoloader --no-dev

echo [3/4] Membangun aset frontend (Vite)...
call npm install
call npm run build

echo [4/4] Membuat archive skbu_infinityfree.zip...
if exist skbu_infinityfree.zip del skbu_infinityfree.zip
tar -a -c -f skbu_infinityfree.zip app bootstrap config database public resources routes storage vendor artisan composer.json server.php .env.example .htaccess vite.config.js package.json

echo.
echo ==========================================
echo  SELESAI! 
echo  File 'skbu_infinityfree.zip' sudah dibuat.
echo  Silakan upload file ini ke folder htdocs di InfinityFree dan extract.
echo ==========================================
pause
