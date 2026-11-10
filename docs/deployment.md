# Dokumentasi Deployment Produksi KampusLMS (Minggu 12)

**Mata Kuliah:** SI2514024 — Pemrograman Web (Semester Ganjil 2026/2027)  
**Kelompok:** 05  
**Fokus Minggu Ini:** Audit responsivitas HP, containerisasi produksi, deployment HTTPS ke Railway.app, verifikasi pasca-deployment, dan tata cara membaca log  
**Penulis dokumen:** Nova Reskianti (`10241058`) — Frontend Developer

> **Status dokumen:** bukti live telah diisi dari keluaran nyata deployment Railway `51ca3610-2108-439a-b191-206add21c3f6` (commit `2037008`, 10 November 2026 UTC / 11 November 2026 WITA), mencakup uji API/HTTP, `scripts/test-authz.sh`, serta audit live viewport mobile lewat **Chrome DevTools MCP** (`360px`–`430px`). Bagian yang tetap menunggu eksekusi terpisah (catatan HP fisik anggota bila ditambahkan, opsi CLI `supervisorctl status` via SSH, uji backup/restore DB, serta BREAK 1–3 bila dijalankan) dibiarkan jujur agar tidak diisi data rekayasa.

---

## 1. Ringkasan Arsitektur & Spesifikasi Server Produksi

| Komponen | Spesifikasi | Berkas/Implementasi |
|:---|:---|:---|
| **Platform hosting** | Railway.app (database & container `europe-west4-drams3a`, edge `railway-hikari` `sin1`) | Dashboard Railway |
| **Runtime aplikasi** | `php:8.4-apache` (PHP 8.4 + Apache + `mod_php`, ekstensi `pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`, `intl`, `opcache`) | `Dockerfile` (stage `base`) |
| **Arsitektur image** | Multi-stage: `base` → `assets` (`node:20-alpine`) → `final` | `Dockerfile` |
| **Web server** | Apache dengan `mpm_prefork` saja, `mod_rewrite` aktif, `AllowOverride All` | `Dockerfile` langkah 2 |
| **Document root** | `/var/www/html/public` via `APACHE_DOCUMENT_ROOT` | `Dockerfile` langkah 3 |
| **Port** | Dinamis dari `$PORT` (fallback `8080`); image `EXPOSE 80 8080` | `docker/entrypoint.sh` |
| **Database** | MySQL 8 internal Railway (plugin, satu project, volume 500 MB) | Variables `${{MySQL.*}}` |
| **Batas unggah & OPcache PHP** | `upload_max_filesize=25M`, `post_max_size=30M`, `expose_php=Off`, `opcache.enable=1` (`opcache.enable_cli=0`) | `docker/php/uploads.ini`, `docker/php/opcache.ini` |
| **Antrean** | `QUEUE_CONNECTION=database` + 2 queue worker via Supervisor (`kampuslms-worker_00` & `_01`) | `docker/supervisor/kampuslms-worker.conf` |
| **Sesi** | `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true` | Variables Railway |
| **Aset frontend** | Tailwind CSS v4 + Vite (`app-BYXPwYZk.css`, `app-D85z6fHB.js`), dibangun di stage `assets` (`npm ci && npm run build`) | `Dockerfile` stage `assets` |
| **Proses awal** | `CMD ["/usr/local/bin/entrypoint.sh"]` → `/usr/bin/supervisord` | `docker/entrypoint.sh` |
| **Health check** | `GET /up` (bawaan Laravel 12) | `bootstrap/app.php` (`health: '/up'`) |

### 1.1 URL Live

| Lingkungan | URL | Status |
|:---|:---|:---|
| Produksi (HTTPS) | `https://kampuslms-production.up.railway.app` | **Aktif** (`GET /login` → `200 OK`, deployment `51ca3610`) |
| Health check | `https://kampuslms-production.up.railway.app/up` | **Aktif** (`GET /up` & `HEAD /up` → `200 OK`) |
| Lokal | `http://127.0.0.1:8000` | Aktif (pengembangan) |

### 1.2 Alur Permintaan Produksi

```
Browser (HP/desktop, HTTPS)
        │
        ▼
Railway Edge (TLS terminator, domain *.up.railway.app)
        │  meneruskan ke container pada $PORT
        ▼
Apache (mpm_prefork, mod_php) ── document root: /var/www/html/public
        │
        ├── berkas statis  → public/build/assets/* (hasil Vite, hash cache-busting)
        └── request PHP    → public/index.php → Laravel 12
                                   │
                                   ├── MySQL internal Railway (SESSION/CACHE/QUEUE database)
                                   └── tabel `jobs` ──► queue worker (Supervisor)
```

---

## 2. Langkah Deployment dari Nol (Railway.app)

### 2.1 Provision MySQL

1. Buka project Railway → **+ New** → **Database** → **Add MySQL**.
2. Tunggu status plugin menjadi **Active**, lalu catat nama service-nya (mis. `MySQL`). Nama service ini dipakai pada sintaks referensi variabel `${{MySQL.*}}` di langkah 2.3.

### 2.2 Hubungkan Repositori GitHub

1. **+ New** → **GitHub Repo** → pilih `muhammadzaldysyahfiraz/kampuslms-kelompok-05`.
2. Pada **Settings → Source**, pastikan **Branch** = `main` dan **Root Directory** kosong (Dockerfile ada di root repo).
3. Railway otomatis mendeteksi `Dockerfile` dan memakai builder **Dockerfile** (bukan Nixpacks). Jika tidak, set **Settings → Build → Builder = Dockerfile**.

### 2.3 Isi Variables Produksi

Buka service aplikasi → tab **Variables** → **Raw Editor**, tempel pasangan berikut (sesuaikan nama service MySQL bila berbeda):

```env
APP_NAME="KampusLMS"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:[ISI HASIL php artisan key:generate --show]
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

LOG_CHANNEL=stack
LOG_STACK=single,errorlog
LOG_LEVEL=warning
```

Catatan penting:

* **`APP_KEY` harus dibuat baru untuk produksi.** Jalankan di lokal `php artisan key:generate --show`, lalu tempel satu baris `base64:...` tersebut ke Railway. **Jangan** memakai `APP_KEY` milik `.env` lokal dan **jangan** menaruh nilainya di dokumen ini, di README, maupun di commit.
* **`APP_DEBUG=false` tidak boleh diubah** di server publik. Pesan error Laravel menampilkan stack trace, path file, potongan query, dan nilai variabel lingkungan — cukup untuk memetakan struktur database penyerang.
* `APP_URL` memakai `${{RAILWAY_PUBLIC_DOMAIN}}` agar tidak perlu diedit manual ketika domain berubah.
* Variabel `DEMO_ADMIN_PASSWORD` / `DEMO_DOSEN_PASSWORD` / `DEMO_MAHASISWA_PASSWORD` ditambahkan **setelah** `config/demo.php` dan `DemoAccountSeeder` tersedia (Urutan 3 — Farin). Selama berkas itu belum ada, variabel tersebut belum dibaca aplikasi.

### 2.4 Generate Domain

1. Service aplikasi → **Settings → Networking** → **Generate Domain**.
2. Saat diminta port, isi **`8080`** (nilai default `$PORT` pada `docker/entrypoint.sh`; Railway menyuntikkan `PORT` sendiri saat runtime).
3. Buka domain tersebut — harus tampil halaman login KampusLMS melalui **HTTPS**.

### 2.5 Apa yang Dijalankan `docker/entrypoint.sh` Saat Container Hidup

Urutan sebenarnya pada berkas `docker/entrypoint.sh` di `main`:

1. Menghapus `mpm_event.*` dan `mpm_worker.*` yang mungkin aktif, sehingga hanya **`mpm_prefork`** yang berjalan bersama `mod_php` (mencegah `AH00534: More than one MPM loaded`).
2. Menetapkan port dinamis: `PORT="${PORT:-8080}"`, lalu menulis ulang `Listen` di `/etc/apache2/ports.conf` dan `<VirtualHost *:PORT>` di `sites-available/000-default.conf`.
3. Membuat seluruh direktori `storage/framework/*`, `storage/logs`, `storage/app/{private,public}`, lalu `chown -R www-data:www-data` dan `chmod -R 775` pada `storage` dan `bootstrap/cache`.
4. `php artisan storage:link || true` — symlink `public/storage` (kegagalan tidak menghentikan container).
5. Membersihkan cache lama: `config:clear`, `route:clear`, `view:clear`.
6. **`php artisan migrate --force`** dengan retry ringan (maksimal 10 percobaan, jeda 3 detik) — penting karena container aplikasi sering hidup beberapa detik sebelum MySQL Railway siap menerima koneksi.
7. Menghitung pengguna; hanya bila jumlahnya nol, menjalankan `php artisan db:seed --force` untuk mengisi basis data awal.
8. Jika ketiga variabel `DEMO_*_PASSWORD` tersedia, menjalankan `DemoAccountSeeder` untuk mengatur password akun demo produksi.
9. Membangun cache produksi: `config:cache`, `route:cache`, `view:cache`, `event:cache`. Karena langkah ini berjalan **setelah** variabel runtime tersedia, nilai konfigurasi sudah terkompilasi dengan benar.
10. `chown` ulang `storage` & `bootstrap/cache` (berkas cache baru dibuat oleh proses CLI), lalu `exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf` untuk menjalankan Apache dan queue workers.

### 2.6 Rujukan Setup VPS Ubuntu + Nginx (Modul 12.1)

Railway adalah target utama kelompok. Langkah berikut disertakan agar deployment dapat direproduksi pada VPS biasa (mis. Ubuntu 24.04) bila diperlukan.

1. **Paket dasar**
   ```bash
   sudo apt update && sudo apt install -y nginx mysql-server unzip git supervisor certbot python3-certbot-nginx
   sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
   sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml \
       php8.4-zip php8.4-gd php8.4-bcmath php8.4-curl php8.4-intl php8.4-opcache
   ```
2. **Kode aplikasi & dependensi produksi** (seeder produksi tidak memerlukan Faker)
   ```bash
   sudo mkdir -p /var/www/kampuslms && sudo chown -R "$USER":www-data /var/www/kampuslms
   git clone https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05.git /var/www/kampuslms
   cd /var/www/kampuslms
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build          # WAJIB: public/build tidak masuk repositori (.gitignore)
   cp .env.example .env && php artisan key:generate
   ```
3. **Permission & symlink**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   php artisan storage:link
   ```
4. **Nginx — document root WAJIB ke `public/`**
   ```nginx
   server {
       listen 80;
       server_name lms.kelompok05.example;
       root /var/www/kampuslms/public;

       index index.php index.html;
       charset utf-8;
       client_max_body_size 30M;   # sejalan dengan post_max_size=30M

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.4-fpm.sock;
       }

       # Blokir berkas tersembunyi (.env, .git) kecuali /.well-known (ACME)
       location ~ /\.(?!well-known).* {
           deny all;
       }

       location ~* \.(?:css|js|jpg|jpeg|png|svg|webp|woff2?)$ {
           expires 30d;
           access_log off;
       }
   }
   ```
5. **SSL (HTTPS)** — `sudo certbot --nginx -d lms.kelompok05.example`, lalu pastikan `SESSION_SECURE_COOKIE=true` dan `APP_URL` memakai `https://`.
6. **Queue worker** — Supervisor (lihat potongan konfigurasi pada bagian 3.3). Di VPS, jalankan juga `php artisan migrate --force`, `php artisan config:cache`, `route:cache`, `view:cache`, `event:cache` sebelum worker dinyalakan.

---

## 3. Cara Deployment Ulang & Cara Membaca Log

### 3.1 Deployment Ulang di Railway

* **Otomatis:** setiap push/merge ke `main` memicu build baru. Pantau tab **Deployments** → status `Building` → `Deploying` → **`Active`**.
* **Manual:** tab **Deployments** → menu **⋮** pada deployment terakhir → **Redeploy**. Ini berguna setelah mengubah **Variables** (Railway tidak me-restart container secara otomatis untuk perubahan variabel pada beberapa kasus).
* **Setelah mengganti variabel/`APP_KEY`:** lakukan redeploy agar `entrypoint.sh` menjalankan `config:cache` ulang. Mengganti `APP_KEY` pada database yang sudah berisi sesi akan membuat sesi lama tidak valid — pengguna cukup login ulang.

### 3.2 Deployment Ulang di VPS (target skrip `deploy.sh`)

`deploy.sh` di root repositori menjalankan langkah deployment ulang berikut:

```bash
set -euo pipefail          # gagal di tengah jalan harus menghentikan skrip
php artisan down --retry=60        # mode maintenance selama build
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build            # aset Vite harus dibangun ulang di server
php artisan migrate --force        # TANPA db:seed (data produksi tidak boleh di-reset)
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart          # worker memuat kode lama di memori
# deploy.sh meminta health check memakai cookie bypass sementara saat maintenance masih aktif
php artisan up                     # hanya dijalankan setelah health check lolos
```

Skrip gagal untuk respons health check di luar HTTP 200–399. Skrip ini ditujukan untuk deployment VPS/SSH; deployment Railway yang dibangun dari commit `main` tetap berjalan melalui pipeline Railway.
Untuk menjalankan `/up` sebelum maintenance berakhir, skrip membuat secret bypass sekali pakai, menyimpan cookie di berkas sementara, memeriksa endpoint, lalu menghapus cookie dan mengakhiri maintenance hanya setelah pemeriksaan berhasil. Bila perintah gagal, skrip memberi peringatan dan membiarkan maintenance aktif agar versi yang belum sehat tidak melayani pengguna.

### 3.3 Queue Worker (Supervisor)

Antrean notifikasi (`SendAssignmentNotification`, `SendGradeNotification`) berjalan lewat tabel `jobs`, sehingga **worker wajib hidup** di produksi. Jika worker mati, tugas dan nilai tetap tersimpan, tetapi notifikasi menumpuk di tabel `jobs` dan pengguna tidak pernah menerimanya.

Konfigurasi Supervisor di `docker/supervisor/kampuslms-worker.conf` menjalankan Apache dan dua queue worker:

```ini
[program:apache2]
command=/usr/local/bin/apache2-foreground
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:kampuslms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

Periksa status proses di container dengan `supervisorctl status`; Apache serta `kampuslms-worker_00` dan `_01` seharusnya berstatus `RUNNING`.

`queue:restart` **wajib** dijalankan setiap selesai deploy: worker `queue:work` memuat kode PHP ke memori dan **tidak** membaca berkas baru hingga di-restart. Tanpa `queue:restart`, job baru tetap dieksekusi oleh kode lama.

### 3.4 Membaca Log Tanpa Menyalakan `APP_DEBUG=true`

| Sumber log | Cara mengakses | Kapan dipakai |
|:---|:---|:---|
| **Deploy Logs** | Railway → service → **Deployments** → pilih deployment → **View Logs** | Build gagal, `npm ci`/`composer install` error, entrypoint berhenti sebelum Apache |
| **HTTP Logs** | Railway → service → tab **Logs** (runtime) | Request 500, PHP fatal error, `migrate` gagal saat start |
| **Laravel log** | `railway ssh` lalu `tail -n 200 storage/logs/laravel.log` | Exception aplikasi, stack trace lengkap tanpa membocorkannya ke browser |
| **Worker log** | `tail -n 200 storage/logs/worker.log` (atau `supervisorctl status`) | Notifikasi tidak terkirim, job gagal |
| **Job gagal** | `php artisan queue:failed` lalu `php artisan queue:retry all` | Setelah worker diperbaiki |

Pada server produksi, pengguna **hanya** melihat halaman "Server Error" generik (500). Detail teknis dibaca dari log di atas. Jika terjadi error, **jangan** menyalakan `APP_DEBUG=true` untuk "melihat pesan aslinya" — cara aman mereproduksi di lokal: jalankan aplikasi dengan `.env` lokal (`APP_DEBUG=true`) memakai dump database staging, atau tambahkan logging terarah (`Log::error($e)`) lalu baca `storage/logs/laravel.log`. Menyalakan debug di server publik membocorkan kredensial database dan struktur kode ke siapa pun yang memicu error.

---

## 4. Kredensial Akun Demo Produksi (Khusus Penguji)

> **Jangan simpan password produksi di repositori atau chat AI.** Kredensial akun demo produksi dibagikan langsung kepada penguji melalui kanal privat yang disetujui tim. Password produksi tidak sama dengan password demo lokal (`password`).

| Peran | Email |
|:---|:---|
| Admin | `admin@kampuslms.test` |
| Dosen | `dosen@kampuslms.test` |
| Mahasiswa | `mahasiswa@kampuslms.test` |

`DemoAccountSeeder` membaca `DEMO_ADMIN_PASSWORD`, `DEMO_DOSEN_PASSWORD`, dan `DEMO_MAHASISWA_PASSWORD` dari konfigurasi server. Atur kredensial hanya di pengelola variabel Railway, lalu berikan kepada penguji melalui jalur privat; jangan menambahkannya ke file yang di-commit.

---

## 5. Bukti Verifikasi Pasca-Deployment (`51ca3610`, 10–11 Nov 2026)

Seluruh keluaran di bawah ini diambil langsung dari eksekusi terhadap server produksi `https://kampuslms-production.up.railway.app` pada deployment `51ca3610-2108-439a-b191-206add21c3f6` (commit `2037008`).

### 5.1 Berkas Sensitif Tidak Boleh Dapat Diakses

```bash
D=https://kampuslms-production.up.railway.app
curl -I "$D/.env"
curl -I "$D/storage/logs/laravel.log"
curl -I "$D/composer.json"
curl -I "$D/switch-role/admin"
```

| URL | Status yang Diharapkan | Status Hasil Uji |
|:---|:---:|:---|
| `/.env` | `404` / `403` | **`404 Not Found`** |
| `/storage/logs/laravel.log` | `404` / `403` | **`404 Not Found`** |
| `/composer.json` | `404` / `403` | **`404 Not Found`** |
| `/switch-role/admin` | `404` / `403` (ditutup Zaldy) | **`404 Not Found`** |
| `/composer.lock`, `/package.json`, `/.git/HEAD` | `404` / `403` | **`404 Not Found`** |
| `/submissions/1/download` (tanpa login) | `302` / `401` / `403` | **`302 Found`** (`location: .../login`) |

Keluaran lengkap `curl -I` (`Tue, 10 Nov 2026 18:21:52–18:21:56 GMT`):

```http
=== curl -I https://kampuslms-production.up.railway.app/up ===
HTTP/1.1 200 OK
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 18:21:52 GMT
Server: railway-hikari
x-hikari-trace: sin1.tr00
x-railway-edge: sin1

=== curl -I https://kampuslms-production.up.railway.app/.env ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 18:21:53 GMT
Server: railway-hikari
x-hikari-trace: sin1.hs0s

=== curl -I https://kampuslms-production.up.railway.app/storage/logs/laravel.log ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 18:21:53 GMT
Server: railway-hikari
x-hikari-trace: sin1.nzn2

=== curl -I https://kampuslms-production.up.railway.app/composer.json ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 18:21:54 GMT
Server: railway-hikari
x-hikari-trace: sin1.d1nj

=== curl -I https://kampuslms-production.up.railway.app/switch-role/admin ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 18:21:55 GMT
Server: railway-hikari
x-hikari-trace: sin1.d1nj

=== curl -I https://kampuslms-production.up.railway.app/submissions/1/download ===
HTTP/1.1 302 Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Tue, 10 Nov 2026 18:21:56 GMT
location: https://kampuslms-production.up.railway.app/login
Set-Cookie: ...; path=/; secure; httponly; samesite=lax
```

### 5.2 Status Queue Worker (Supervisor)

Dari log deploy nyata Railway pada container aktif `51ca3610-2108-439a-b191-206add21c3f6`, proses `supervisord` memuat konfigurasi `/etc/supervisor/conf.d/kampuslms.conf` dan seluruh worker sukses berstatus `RUNNING`:

```text
2026-11-10T18:21:18.426137317Z info 2026-11-10 18:21:18,409 INFO Included extra file "/etc/supervisor/conf.d/kampuslms.conf" during parsing
2026-11-10T18:21:18.426149967Z info 2026-11-10 18:21:18,412 INFO supervisord started with pid 1
2026-11-10T18:21:19.439439044Z info 2026-11-10 18:21:19,415 INFO spawned: 'apache2' with pid 41
2026-11-10T18:21:19.439445214Z info 2026-11-10 18:21:19,416 INFO spawned: 'kampuslms-worker_00' with pid 42
2026-11-10T18:21:19.439449344Z info 2026-11-10 18:21:19,417 INFO spawned: 'kampuslms-worker_01' with pid 43
2026-11-10T18:21:20.501699100Z info 2026-11-10 18:21:20,495 INFO success: apache2 entered RUNNING state, process has stayed up for > than 1 seconds (startsecs)
2026-11-10T18:21:20.501707120Z info 2026-11-10 18:21:20,495 INFO success: kampuslms-worker_00 entered RUNNING state, process has stayed up for > than 1 seconds (startsecs)
2026-11-10T18:21:20.501712020Z info 2026-11-10 18:21:20,495 INFO success: kampuslms-worker_01 entered RUNNING state, process has stayed up for > than 1 seconds (startsecs)
```

> *(Opsional bila perlu tangkapan layar CLI langsung di container: jalankan `npx @railway/cli ssh` lalu `supervisorctl status`.)*

### 5.3 Uji Otorisasi, Halaman Terautentikasi & Rate Limit terhadap Server HTTPS Produksi

```bash
BASE_URL=https://kampuslms-production.up.railway.app \
MHS_PASSWORD="<rahasia>" \
DOSEN_PASSWORD="<rahasia>" \
bash scripts/test-authz.sh
```

```text
Running authorization checks against: https://kampuslms-production.up.railway.app

==============================================================================
  STAGE 1: Web login and Sanctum token setup
==============================================================================
  [PASS] Mahasiswa web login (HTTP 200)
  [PASS] Dosen web login (HTTP 200)

==============================================================================
  STAGE 2: Submission authorization and downloads
==============================================================================
  [PASS] Mahasiswa cannot view another student's submission (HTTP 403)
  [PASS] Mahasiswa can view their own submission (HTTP 200)
  [PASS] Guest cannot download a submission (HTTP 302)
  [PASS] Mahasiswa cannot download another student's submission (HTTP 403)
  [PASS] Mahasiswa cannot grade a submission through the API (HTTP 403)

==============================================================================
  STAGE 3: Cross-lecturer assignment authorization
==============================================================================
  [PASS] Dosen cannot update another lecturer's assignment (HTTP 403)
  [PASS] Dosen can update their own assignment (HTTP 200)

==============================================================================
  STAGE 4: Scoped route binding
==============================================================================
  [PASS] Cross-course assignment route binding returns 404 (HTTP 404)
  [PASS] Cross-course material route binding returns 404 (HTTP 404)

==============================================================================
  STAGE 5: User management RBAC
==============================================================================
  [PASS] Mahasiswa cannot access /users (HTTP 403)
  [PASS] Dosen cannot access /users (HTTP 403)

==============================================================================
  STAGE 6: Production exposure checks
==============================================================================
  [PASS] Sensitive path /.env is not publicly accessible (HTTP 404)
  [PASS] Sensitive path /storage/logs/laravel.log is not publicly accessible (HTTP 404)
  [PASS] Sensitive path /composer.json is not publicly accessible (HTTP 404)
  [PASS] Production switch-role route is absent (HTTP 404)

==============================================================================
  Authorization results: 17 passed, 0 failed
==============================================================================
All authorization checks passed.
```

Verifikasi tambahan yang dijalankan pada deployment yang sama (`51ca3610`):

* **29/29 rute web terautentikasi (Mahasiswa, Dosen, Admin)** — `/dashboard`, `/courses`, `/courses/1`, `/assignments/1`, `/materials/1`, `/submissions/1`, `/mahasiswa/submissions`, `/mahasiswa/grades`, `/dosen/courses`, `/dosen/courses/1`, `/dosen/courses/1/grades`, `/dosen/submissions`, `/assignments/4`, `/users`, `/users/create`, `/courses/create`, `/admin/submissions`, `/admin/grades`, `/notifications`, `/tentang` — seluruhnya mengembalikan **HTTP `200`** dan 0 baris `production.ERROR` di log deploy.
* **Rate limit login (`throttle:5,1`):** pengiriman 6 percobaan gagal berturut-turut ke `POST /api/v1/auth/login` pada koneksi HTTP yang sama memicu pembatas laju setelah 5 percobaan (`x-ratelimit-limit: 5`, `x-ratelimit-remaining: 0` lalu **`HTTP/1.1 429 Too Many Requests`**, `retry-after: 28`).
* **Aset Vite HTTPS:** HTML `/login` merujuk `https://kampuslms-production.up.railway.app/build/assets/app-BYXPwYZk.css` (terverifikasi `HTTP/1.1 200 OK`) dan `app-D85z6fHB.js` tanpa referensi `http://localhost` maupun campuran konten HTTP.

### 5.4 Daftar Periksa 12 Poin Keamanan Produksi (Modul 12.1)

- [x] `APP_ENV=production` dan `APP_DEBUG=false` terpasang pada variabel Railway (terverifikasi `404` & halaman error generik tanpa stack trace).
- [x] `APP_KEY` dibuat baru khusus untuk produksi dan disimpan hanya di Variables Railway (tidak pernah di-commit).
- [x] Document root mengarah ke `/var/www/html/public`; `curl -I /.env` menghasilkan **`404 Not Found`**.
- [x] `.git` dan berkas tersembunyi tidak dapat diakses; `curl -I /storage/logs/laravel.log` dan `/.git/HEAD` menghasilkan **`404 Not Found`**.
- [x] `composer.json`, `composer.lock`, dan `package.json` tidak dapat diakses lewat web (ketiganya **`404 Not Found`**).
- [x] Rute bantu `/switch-role/{role}` dinonaktifkan di produksi (**`404 Not Found`**).
- [x] HTTPS aktif dengan sertifikat valid; `SESSION_SECURE_COOKIE=true` (terlihat pada header `Set-Cookie: ... secure; httponly; samesite=lax`); tidak ada konten campuran (`http://`) pada HTML & aset Vite.
- [x] Kredensial MySQL hanya dari referensi variabel Railway (`${{MySQL.MYSQL*}}`, tidak tertulis di repo/dokumen).
- [x] Password akun demo produksi diganti lewat `DemoAccountSeeder` (`DEMO_*_PASSWORD` di Variables Railway), berbeda dari `password` lokal, dan dibagikan lewat kanal privat tanpa di-commit ke repo publik.
- [x] Rate limit login (`throttle:5,1`) aktif dan terverifikasi pada server live (`HTTP/1.1 429 Too Many Requests`).
- [x] Queue worker berjalan (`apache2`, `kampuslms-worker_00`, `kampuslms-worker_01` berstatus `RUNNING` di Supervisor) dan `deploy.sh` memuat `php artisan queue:restart`.
- [ ] Backup database terjadwal dan uji satu kali proses restore MySQL *(belum dilakukan terpisah pada plugin MySQL Free Railway).*

### 5.5 Catatan Uji Tampilan Mobile (Chrome DevTools MCP & HP Anggota)

#### A. Uji Emulasi Mobile Live — Chrome DevTools MCP (`51ca3610`)

Pengujian langsung pada URL produksi `https://kampuslms-production.up.railway.app` menggunakan **Chrome DevTools MCP** (`mobile, touch`, `deviceScaleFactor=2`, konteks browser terisolasi):

| Viewport | Halaman yang Diuji | Hasil Pengukuran Nyata (`evaluate_script` & Interaksi) | Console / Network |
|:---:|:---|:---|:---|
| `360×800` | `/login`, `/dashboard` (login Mahasiswa), `#notif-dropdown`, `#mobile-nav-drawer` | `scrollWidth=360` (`overflowX=false`); `#notif-dropdown` terbuka di `left=16px, right=344px, width=328px`; seluruh 6 tombol/tautan di `#mobile-nav-drawer` setinggi `44px` | 0 console error/warn; semua aset Vite/font `200`; `httpLinks=[]` |
| `390×844` | `/courses/1` (Detail MK Pemrograman Web) | `scrollWidth=390` (`overflowX=false`); ukuran berkas materi (`Number::fileSize`) ter-render (`hasFileSizeText=true`) | 0 console error/warn; `httpLinks=[]` |
| `412×915` | `/mahasiswa/grades` (Rekap Nilai) | `scrollWidth=412` (`overflowX=false`); kontainer `<table>` terbawa wrapper overflow horizontal (`tablesWrapped=true`) | 0 console error/warn; `httpLinks=[]` |
| `430×932` | `/submissions/1`, `#notif-dropdown`, `/notifications` | `scrollWidth=430` (`overflowX=false`); `#notif-dropdown` terbuka di `left=16px, right=414px, width=398px`; tampil `Berkas Jawaban: Laporan_Tugas1_Web_10241000.pdf (1 MB)` | 0 console error/warn; seluruh request `200`/`304`; `httpLinks=[]` |
| Konteks tamu terisolasi | `/submissions/1/download` (tanpa sesi) | Otomatis redirect ke `https://kampuslms-production.up.railway.app/login` (berkas tidak terunduh) | 0 kebocoran akses |

#### B. Catatan Uji Langsung dari Perangkat HP Fisik Anggota *(Opsional — Tambah Jika Diuji)*

| Penguji | Perangkat / Browser | Lebar Layar | Halaman yang Diuji | Hasil |
|:---|:---|:---:|:---|:---|
| `[nama]` | `[mis. Redmi Note 12 / Chrome]` | `[360px]` | `[login, dashboard, notifikasi]` | `[ISI BILA DIUJI DARI HP FISIK]` |

### 5.6 Catatan Percobaan BREAK 1–3

Percobaan BREAK hanya boleh dilakukan di belakang **pagar akses** (IP allowlist Railway, basic-auth Nginx, atau environment staging) — **bukan** pada situs terbuka. Setelah percobaan selesai, wajib mengganti password database dan menjalankan `php artisan key:generate`.

| BREAK | Yang Dicoba | Jam Mulai | Jam Kembali | Pagar Akses yang Dipakai | Tindakan Pemulihan |
|:---:|:---|:---:|:---:|:---|:---|
| 1 | `[mis. APP_DEBUG=true + picu error 500]` | `[—]` | `[—]` | `[—]` | `[APP_DEBUG=false + redeploy]` |
| 2 | `[mis. hapus .htaccess / ubah AllowOverride]` | `[—]` | `[—]` | `[—]` | `[—]` |
| 3 | `[mis. matikan queue worker]` | `[—]` | `[—]` | `[—]` | `[supervisorctl start + queue:retry all]` |

---

## 6. Masalah yang Ditemui & Cara Mengatasinya

Bagian ini adalah catatan teknis nyata dari rancangan container dan kode proyek pada `main` (commit `68e5b6a`, merge PR `#59`), bukan daftar teoretis.

### 6.1 Aset Vite hilang di server (`public/build` tidak ada → tampilan tanpa CSS)

**Gejala:** halaman tampil polos tanpa gaya, konsol browser menampilkan `Unable to locate file in Vite manifest: resources/css/app.css`. Laravel melempar `ViteManifestNotFoundException` saat `@vite(...)` dirender.

**Penyebab:** `public/build` tercantum di `.gitignore` **dan** di `.dockerignore`. Artinya aset tidak pernah ikut ke repositori maupun ke konteks build Docker. Jika `Dockerfile` hanya melakukan `composer install`, `manifest.json` tidak akan pernah ada di image.

**Solusi yang dipakai:** stage `assets` berbasis `node:20-alpine` menjalankan `npm ci` lalu `npm run build`, dan hasilnya disalin ke image final:

```dockerfile
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY --from=base /var/www/html/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build
```

**Detail yang mudah terlewat:** `resources/css/app.css` memuat `@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php'`. Karena stage `assets` hanya menyalin `resources/`, `public`, dan `vite.config.js`, direktori pagination milik vendor **tidak ada** di stage itu. Tanpa baris `COPY --from=base`, Tailwind v4 tidak menemukan kelas-kelas pagination sehingga tombol navigasi halaman kehilangan gaya. Karena itu direktori tersebut disalin dari stage `base` (yang sudah menjalankan `composer install`).

### 6.2 Apache gagal start: `AH00534: More than one MPM loaded` + port dinamis Railway

**Gejala:** container langsung mati, log deploy berisi `AH00534: httpd: Configuration error: More than one MPM loaded.`

**Penyebab:** image `php:8.4-apache` resmi mengaktifkan `mpm_event`. Menambahkan `a2enmod mpm_prefork` tanpa mematikan `mpm_event`/`mpm_worker` membuat dua MPM aktif sekaligus — konfigurasi Apache menolak jalan. Masalah kedua: Railway memberi port lewat variabel `PORT`, sedangkan Apache mendengarkan `80` secara hardcode.

**Solusi:** hapus kedua modul MPM lain sebelum mengaktifkan prefork (dilakukan di `Dockerfile` **dan** diulang di `entrypoint.sh` agar tetap benar walau image diubah), lalu tulis ulang `Listen` dan `VirtualHost` mengikuti `${PORT:-8080}`:

```sh
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
PORT="${PORT:-8080}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
```

### 6.3 Unggahan materi 20 MB ditolak padahal validasi aplikasi mengizinkan

**Gejala:** unggahan materi ±20 MB gagal dengan pesan validasi `The file failed to upload` atau `maksimal 20 MB` meski berkasnya di bawah batas. Pada berkas 25 MB respons bisa kosong/`POST Content-Length ... exceeds the limit`.

**Penyebab:** batas bawaan PHP (`upload_max_filesize=2M`, `post_max_size=8M`) lebih kecil daripada aturan validasi aplikasi (materi maks 20 MB, tugas maks 5 MB). PHP menolak berkas **sebelum** validasi Laravel berjalan, sehingga pesan errornya membingungkan. `post_max_size` juga harus mencakup total body multipart, bukan hanya berkasnya.

**Solusi:** `docker/php/uploads.ini` (`upload_max_filesize=25M`, `post_max_size=30M`) disalin ke `/usr/local/etc/php/conf.d/` pada image. Untuk VPS+Nginx, `client_max_body_size` disetel sejalan (`30M`) karena Nginx punya batas sendiri di depan PHP-FPM. Bukti uji: `[ISI SETELAH DEPLOY LIVE — unggah berkas ±20 MB lalu catat hasilnya]`.

### 6.4 Seeder produksi berjalan tanpa dependensi Faker

`DatabaseSeeder` tidak memakai Faker, sehingga berjalan di image produksi `--no-dev`. Pada deployment pertama Railway (`ca33731a`), `docker/entrypoint.sh` mendeteksi `User count: 0` lalu mengisi 34 akun pengguna beserta data kelas, materi, tugas, 125 pengumpulan, dan 75 nilai; `DemoAccountSeeder` langsung mengganti password 3 akun demo dari `DEMO_*_PASSWORD`. Pada deploy ulang selanjutnya (`75ba2150` dan `51ca3610`), entrypoint mencatat `Database sudah berisi data (User count: 34). Melewati seeding awal.` dan hanya memperbarui password demo sesuai variabel.

### 6.5 `scripts/test-authz.sh` menggunakan login web dan token CSRF

**Status:** lulus **17/17 checks** di server HTTPS produksi (`51ca3610`).

**Penjelasan:** `routes/web.php` hanya mendaftarkan `/switch-role/{role}` pada lingkungan lokal dan testing. Skrip pengujian membuat sesi mahasiswa dan dosen dengan `GET /login`, token CSRF, dan `POST /login`, serta meminta token Sanctum untuk uji API tanpa membuka `/switch-role`. Keluaran lengkap tercantum di Bagian 5.3. *(Catatan Windows Git Bash: bila `curl` mencoba IPv6 lebih dulu hingga timeout terhadap host `*.up.railway.app`, paksa IPv4 lewat flag `-4` atau wrapper `curl` sebelum menjalankan skrip).*

### 6.6 Catatan Kendala Nyata Saat Deploy Live ke Railway (PR #63–#65)

7. **Error 500 seluruh rute `web` pada deploy awal (`Table 'railway.sessions' doesn't exist`) — PR #63 (`fix/sessions-table-migration`):**
   * **Gejala & diagnosis aman:** `/up` dan `POST /api/v1/auth/login` mengembalikan `200`, tetapi `/login` dan `/tentang` mengembalikan `500`. Tanpa menyalakan `APP_DEBUG=true`, pengamatan `LOG_CHANNEL=errorlog` di Railway Deploy Logs mengungkap `SQLSTATE[42S02]: Table 'railway.sessions' doesn't exist`.
   * **Penyebab:** `phpunit.xml` memakai `SESSION_DRIVER=array` sehingga tes lokal lolos meski migrasi awal `0001_01_01_000000_create_users_table.php` belum membuat tabel `sessions`, sedangkan produksi memakai `SESSION_DRIVER=database`.
   * **Solusi:** menambahkan migrasi idempoten `2026_11_10_000001_create_sessions_table.php`, tes regresi `test_database_session_driver_can_serve_web_routes` di `WeekTwelveSecurityTest.php`, serta menyetel `LOG_CHANNEL=stack` + `LOG_STACK=single,errorlog` di Railway agar log tertulis ke berkas sekaligus muncul di Deploy Logs.
8. **MySQL internal Railway bangun lebih lambat & pemicu auto-deploy lintas kepemilikan repo — PR #64 (`b72c326`):**
   * **Gejala:** Saat container start, percobaan migrasi pertama mendapat `SQLSTATE[HY000] [2002] Connection refused (Host: mysql.railway.internal)` karena plugin MySQL baru bangun.
   * **Solusi:** Mekanisme retry di `docker/entrypoint.sh` (`percobaan 1/10`, jeda 3 detik) langsung menghubungkan ulang dan menuntaskan migrasi + Supervisor (`apache2`, `kampuslms-worker_00`, `kampuslms-worker_01` → `RUNNING`). Selain itu, karena repo GitHub dimiliki akun anggota lain sementara Railway berada di akun berbeda, penyinkronan sumber service memastikan commit terbaru ter-build.
9. **Error 500 pada halaman materi, tugas, kelas, dan pengumpulan + latensi `/dashboard` — PR #65 (`fix/docker-intl-opcache`):**
   * **Gejala:** Saat uji `scripts/test-authz.sh` pertama kali (`75ba2150`), `GET /submissions/1`, `/courses/1`, `/assignments/1`, dan `/materials/1` mengembalikan `500` dengan log `The "intl" PHP extension is required to use the [format] method.`, dan `/dashboard` memakan waktu ~16 detik.
   * **Penyebab:** Enam view Blade memanggil `\Illuminate\Support\Number::fileSize(...)`, sementara `Dockerfile` (`php:8.4-apache`) belum menginstal `libicu-dev` / ekstensi `intl` maupun `opcache`.
   * **Solusi:** Memasang `libicu-dev`, `intl`, dan `opcache` plus konfigurasi `docker/php/opcache.ini`. Setelah deploy `51ca3610`, 29/29 rute web terautentikasi mengembalikan `200`, `scripts/test-authz.sh` lulus **17 passed, 0 failed**, dan `/dashboard` turun ke ~9,2 detik (halaman lain ~2,0–3,8 detik).
10. **Uji tampilan mobile live di Chrome DevTools MCP (`360px`–`430px`):** seluruh halaman kunci (`/login`, `/dashboard`, `/courses/1`, `/mahasiswa/grades`, `/submissions/1`, `/notifications`) tampil tanpa overflow horizontal (`scrollWidth == innerWidth`), dropdown `#notif-dropdown` menempel aman dengan margin `16px` kiri-kanan (`328px` pada `360px`; `398px` pada `430px`), seluruh item `#mobile-nav-drawer` memenuhi tinggi sentuh `44px`, nol console error, dan nol konten campuran `http://`. *(Catatan HP fisik anggota di Bagian 5.5.B tetap disediakan bila ada temuan tambahan dari perangkat langsung.)*

---

## 7. Audit Responsivitas Mobile (HP) — Minggu 12

Target minggu ini: *"KampusLMS bisa dibuka siapa pun dari HP, lewat HTTPS, dengan domain yang layak."* Audit dilakukan pada `resources/views/components/layout.blade.php` dan `resources/views/auth/login.blade.php`, ditambah pemeriksaan menyeluruh seluruh view untuk pola yang berisiko di layar sempit.

### 7.1 Hasil Pemeriksaan

| Butir yang Diperiksa | Temuan |
|:---|:---|
| `<meta name="viewport">` | Ada di `layout.blade.php` dan `login.blade.php` (`width=device-width, initial-scale=1.0`) |
| Asset CSS/JS hardcoded ke `http://localhost` atau mode dev | **Tidak ada.** `grep -rn "localhost\|127.0.0.1" resources/views` tidak menemukan hasil; seluruh aset dimuat lewat `@vite(['resources/css/app.css', 'resources/js/app.js'])` |
| `public/hot` tertinggal (memaksa HMR di produksi) | Tidak ada; `public/hot` juga di-`.gitignore` dan `.dockerignore` |
| Navigasi mobile (`#mobile-nav-toggle` + `#mobile-nav-drawer`) | Berfungsi: tombol 44×44px (`w-11 h-11`), `aria-expanded`/`aria-controls` terpasang, drawer menampilkan tautan sesuai peran + tombol Keluar |
| Target sentuh menu drawer | Setiap tautan memakai `min-h-11` (≥ 44px) sesuai panduan target sentuh |
| Tombol logout & notifikasi di header | Ikon tetap tampil, label teks "Keluar" disembunyikan di bawah `sm` (`hidden sm:inline`) sehingga header tetap muat pada 360px |
| Container tabel (`overflow-x-auto`) | Seluruh view yang memuat `<table>` sudah membungkusnya dengan `overflow-x-auto`; tidak ada tabel "telanjang" |
| Grid statistik dashboard | `grid-cols-1 sm:grid-cols-3` — menumpuk vertikal di HP |
| Latar ilustrasi (`#notion-campus-backdrop`) | `position: fixed; inset: 0; overflow: hidden; pointer-events: none` — tidak menambah lebar halaman |
| Halaman login | Form satu kolom, `overflow-x-hidden`, `min-h-dvh` untuk browser mobile; kartu akun demo memakai `grid-cols-3` yang masih terbaca pada 360px |

### 7.2 Bug yang Ditemukan dan Diperbaiki

**Dropdown notifikasi melewati tepi kiri layar HP.** Panel `#notif-dropdown` memakai `absolute right-0 w-80` terhadap pembungkus tombol lonceng. Karena lonceng tidak berada di tepi kanan layar (masih ada avatar, tombol keluar, dan hamburger), panel 320px itu memanjang ke kiri melewati batas viewport.

Pengukuran pada markup asli (CSS produksi hasil `npm run build`, pengukuran `getBoundingClientRect()`):

| Lebar Viewport | Tepi Kiri Panel | Bagian di Luar Layar |
|:---:|:---:|:---:|
| 360px | −114px | **114px** |
| 430px | −43px | **43px** |

Karena dokumen tidak memiliki `overflow-x` yang dapat di-scroll ke arah negatif, bagian kiri panel (judul "Pemberitahuan" dan awal setiap baris) benar-benar tidak dapat dilihat maupun disentuh.

**Perbaikan minimal** pada `resources/views/components/layout.blade.php` — satu atribut `class`:

```blade
{{-- Sebelum --}}
class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-xl bg-white ..."

{{-- Sesudah --}}
class="hidden fixed left-4 right-4 top-16 rounded-xl bg-white ... sm:absolute sm:left-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96"
```

Perilaku setelah perbaikan:

* **HP (< 640px):** panel `fixed` dengan `left-4 right-4` mengikuti lebar layar (328px pada 360px), muncul tepat di bawah header (`top-16`, header `h-14`).
* **Tablet/desktop (≥ 640px):** kembali `absolute` menempel di bawah tombol lonceng (`sm:top-full sm:mt-2`), rata kanan (`sm:right-0`), lebar tetap `sm:w-96`.

**Hasil pengukuran ulang:**

| Lebar Viewport | Tepi Kiri | Tepi Kanan | Lebar Panel | Overflow Horizontal | Uji Klik |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 360px | 16px | 344px | 328px | Tidak ada (`scrollWidth` = 360) | Terjangkau di kedua tepi |
| 430px | 16px | 414px | 398px | Tidak ada (`scrollWidth` = 430) | — |
| 900px | 229px | 613px (rata dengan tombol lonceng) | 384px | Tidak ada | `absolute`, jarak 8px di bawah lonceng |

Metode verifikasi: harness HTML statis berisi markup header yang identik dengan layout asli, dimuat bersama CSS hasil `npm run build`, lalu diukur pada lebar 360/430/900px. Halaman aplikasi lengkap (dengan data dan sesi login) diverifikasi setelah deploy live pada bagian 5.5.

### 7.3 Catatan

* Tidak ada perubahan lain pada view: bagian yang sudah benar (viewport, `@vite`, drawer mobile, target sentuh, tabel `overflow-x-auto`) sengaja **tidak** diubah.
* Setelah mengubah view, `npm run build` dijalankan ulang agar `public/build` sesuai.
