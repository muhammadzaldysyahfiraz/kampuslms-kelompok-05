#!/usr/bin/env sh
set -eu

# Pastikan hanya mpm_prefork yang aktif untuk Apache + mod_php
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*

# Atur port dinamis Railway (default 8080)
PORT="${PORT:-8080}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Pastikan direktori storage dan bootstrap/cache siap dan dapat ditulis oleh www-data
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs storage/app/private storage/app/public
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Pastikan symlink storage publik tersedia
php artisan storage:link || true

# Bersihkan cache lama sebelum migrasi dan konfigurasi runtime
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Jalankan migrasi dengan retry ringan apabila database MySQL Railway baru menyala
ATTEMPT=1
MAX_ATTEMPTS=10
until php artisan migrate --force; do
    if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
        echo "Migrasi gagal setelah ${MAX_ATTEMPTS} percobaan."
        exit 1
    fi
    echo "Menunggu koneksi database... percobaan ${ATTEMPT}/${MAX_ATTEMPTS}"
    ATTEMPT=$((ATTEMPT + 1))
    sleep 3
done

# Hook Seeding Aman (Urutan 2 - Farin):
# 1. Jalankan db:seed penuh HANYA jika database kosong (first boot, User::count() == 0).
USER_COUNT=$(php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class); \$kernel->bootstrap(); echo App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "Database kosong terdeteksi (User count: 0). Menjalankan seeder awal..."
    php artisan db:seed --force
else
    echo "Database sudah berisi data (User count: ${USER_COUNT}). Melewati seeding awal."
fi

# 2. Perbarui kredensial akun demo jika variabel DEMO_*_PASSWORD disetel di server produksi
if [ -n "${DEMO_ADMIN_PASSWORD:-}" ] && [ -n "${DEMO_DOSEN_PASSWORD:-}" ] && [ -n "${DEMO_MAHASISWA_PASSWORD:-}" ]; then
    echo "Kredensial demo produksi terdeteksi. Menjalankan DemoAccountSeeder..."
    php artisan db:seed --class=DemoAccountSeeder --force
fi

# Terapkan optimisasi produksi setelah variabel runtime tersedia
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Pastikan kepemilikan log/cache baru tetap milik www-data sebelum Apache berjalan
chown -R www-data:www-data storage bootstrap/cache

exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
