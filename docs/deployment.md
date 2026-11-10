# Dokumentasi Deployment Produksi KampusLMS (Minggu 12)

**Mata Kuliah:** SI2514024 — Pemrograman Web (Semester Ganjil 2026/2027)  
**Kelompok:** 05  
**Fokus Minggu Ini:** Audit responsivitas HP, containerisasi produksi, deployment HTTPS ke Railway.app, verifikasi pasca-deployment, dan tata cara membaca log  
**Penulis dokumen:** Nova Reskianti (`10241058`) — Frontend Developer

> **Status dokumen:** ditulis pada tahap **Urutan 1** (setelah PR Docker Rifa/`#59` di-merge ke `main`, sebelum deploy live dilakukan oleh Zaldy). Seluruh bagian yang memerlukan keluaran nyata dari server produksi sengaja dibiarkan sebagai placeholder `[ISI SETELAH ...]` dan **wajib** diisi dengan hasil perintah yang benar-benar dijalankan — bukan contoh karangan.

---

## 1. Ringkasan Arsitektur & Spesifikasi Server Produksi

| Komponen | Spesifikasi | Berkas/Implementasi |
|:---|:---|:---|
| **Platform hosting** | Railway.app (region Singapore) | Dashboard Railway |
| **Runtime aplikasi** | `php:8.4-apache` (PHP 8.4 + Apache + `mod_php`) | `Dockerfile` (stage `base`) |
| **Arsitektur image** | Multi-stage: `base` → `assets` (`node:20-alpine`) → `final` | `Dockerfile` |
| **Web server** | Apache dengan `mpm_prefork` saja, `mod_rewrite` aktif, `AllowOverride All` | `Dockerfile` langkah 2 |
| **Document root** | `/var/www/html/public` via `APACHE_DOCUMENT_ROOT` | `Dockerfile` langkah 3 |
| **Port** | Dinamis dari `$PORT` (fallback `8080`); image `EXPOSE 80 8080` | `docker/entrypoint.sh` |
| **Database** | MySQL internal Railway (plugin, satu project) | Variables `${{MySQL.*}}` |
| **Batas unggah PHP** | `upload_max_filesize=25M`, `post_max_size=30M`, `expose_php=Off` | `docker/php/uploads.ini` |
| **Antrean** | `QUEUE_CONNECTION=database` + queue worker via Supervisor | target Urutan 4 (Yuspa) |
| **Sesi** | `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true` | Variables Railway |
| **Aset frontend** | Tailwind CSS v4 + Vite, dibangun di stage `assets` (`npm ci && npm run build`) | `Dockerfile` stage `assets` |
| **Proses awal** | `CMD ["/usr/local/bin/entrypoint.sh"]` | `docker/entrypoint.sh` |
| **Health check** | `GET /up` (bawaan Laravel 12) | `bootstrap/app.php` (`health: '/up'`) |

### 1.1 URL Live

| Lingkungan | URL | Status |
|:---|:---|:---|
| Produksi (HTTPS) | `https://[ISI SETELAH DEPLOY LIVE KE RAILWAY].up.railway.app` | *Belum aktif — diisi setelah Zaldy/Rifa membuat domain di Railway* |
| Health check | `https://[ISI SETELAH DEPLOY LIVE KE RAILWAY].up.railway.app/up` | *Belum aktif* |
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
7. Membangun cache produksi: `config:cache`, `route:cache`, `view:cache`, `event:cache`. Karena langkah ini berjalan **setelah** variabel runtime tersedia, nilai `env()` di dalam `config/*` sudah terkompilasi dengan benar.
8. `chown` ulang `storage` & `bootstrap/cache` (berkas cache baru dibuat oleh proses CLI), lalu `exec apache2-foreground`.

**Yang belum otomatis (harus diperhatikan pada deployment pertama):** *seeding* data awal **tidak** dijalankan oleh `entrypoint.sh`. Database akan kosong (hanya tabel hasil migrasi), sehingga halaman login belum punya akun. Untuk mengisi data awal, jalankan sekali dari mesin yang punya akses ke variabel Railway:

```bash
# Opsional: seeding data contoh (DatabaseSeeder memakai Faker — lihat bagian 6.4)
railway run php artisan db:seed --force
```

Alternatifnya, isi data awal lewat shell container: `railway ssh` lalu `php artisan db:seed --force`. Jika nanti `DemoAccountSeeder` (Farin) sudah ada, perintahnya menjadi `php artisan db:seed --class=DemoAccountSeeder --force` — **hanya jika** seeder itu tidak memakai Faker, karena image produksi dibangun dengan `composer install --no-dev`.

### 2.6 Rujukan Setup VPS Ubuntu + Nginx (Modul 12.1)

Railway adalah target utama kelompok. Langkah berikut disertakan agar deployment dapat direproduksi pada VPS biasa (mis. Ubuntu 24.04) bila diperlukan.

1. **Paket dasar**
   ```bash
   sudo apt update && sudo apt install -y nginx mysql-server unzip git supervisor certbot python3-certbot-nginx
   sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
   sudo apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml \
       php8.4-zip php8.4-gd php8.4-bcmath php8.4-curl
   ```
2. **Kode aplikasi & dependensi produksi** (tanpa `--no-dev` bila Faker masih dipakai seeder)
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

`scripts/deploy.sh` disiapkan oleh Urutan 4 (Yuspa). Kontrak yang harus dipenuhi skrip tersebut:

```bash
set -euo pipefail          # gagal di tengah jalan harus menghentikan skrip
php artisan down --retry=60        # mode maintenance selama build
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build            # aset Vite harus dibangun ulang di server
php artisan migrate --force        # TANPA db:seed (data produksi tidak boleh di-reset)
php artisan config:cache && php artisan route:cache
php artisan view:cache && php artisan event:cache
php artisan queue:restart          # worker memuat kode lama di memori
php artisan up
curl -fsS https://[domain]/up      # health check: harus 200
```

Selama `scripts/deploy.sh` belum ada, jalankan urutan perintah di atas secara manual.

### 3.3 Queue Worker (Supervisor)

Antrean notifikasi (`SendAssignmentNotification`, `SendGradeNotification`) berjalan lewat tabel `jobs`, sehingga **worker wajib hidup** di produksi. Jika worker mati, tugas dan nilai tetap tersimpan, tetapi notifikasi menumpuk di tabel `jobs` dan pengguna tidak pernah menerimanya.

Target konfigurasi Supervisor (Urutan 4 — Yuspa):

```ini
[program:kampuslms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

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

> **Bagian ini hanya untuk penguji/dosen.** Jangan menyalin isi tabel ini ke `README.md`, ke deskripsi PR, atau ke grup chat umum. Password produksi **tidak** sama dengan password demo lokal (`password`).

| Peran | Email | Password Produksi |
|:---|:---|:---|
| Admin | `admin@kampuslms.test` | `[ISI SETELAH SET DEMO_*_PASSWORD DI RAILWAY]` |
| Dosen | `dosen@kampuslms.test` | `[ISI SETELAH SET DEMO_*_PASSWORD DI RAILWAY]` |
| Mahasiswa | `mahasiswa@kampuslms.test` | `[ISI SETELAH SET DEMO_*_PASSWORD DI RAILWAY]` |

Mekanisme yang direncanakan: `DemoAccountSeeder` (Farin) membaca `config/demo.php`, yang mengambil nilai dari `DEMO_ADMIN_PASSWORD`, `DEMO_DOSEN_PASSWORD`, dan `DEMO_MAHASISWA_PASSWORD` di Railway. Seeder dijalankan **sekali** setelah deploy pertama, lalu variabel password di Railway diganti ke nilai lain setelah pengujian selesai.

---

## 5. Template Bukti Verifikasi Pasca-Deployment

Seluruh blok di bawah ini **wajib** diisi dengan keluaran nyata setelah aplikasi live. Jangan mengisi dengan contoh.

### 5.1 Berkas Sensitif Tidak Boleh Dapat Diakses

```bash
D=https://[ISI SETELAH DEPLOY LIVE KE RAILWAY].up.railway.app
curl -I "$D/.env"
curl -I "$D/storage/logs/laravel.log"
curl -I "$D/composer.json"
curl -I "$D/switch-role/admin"
```

| URL | Status yang Diharapkan | Status Hasil Uji |
|:---|:---:|:---|
| `/.env` | `404` / `403` | `[ISI SETELAH DEPLOY LIVE]` |
| `/storage/logs/laravel.log` | `404` / `403` | `[ISI SETELAH DEPLOY LIVE]` |
| `/composer.json` | `404` / `403` | `[ISI SETELAH DEPLOY LIVE]` |
| `/switch-role/admin` | `404` / `403` (setelah ditutup Zaldy) | `[ISI SETELAH DEPLOY LIVE]` |

Keluaran lengkap:

```
[ISI SETELAH DEPLOY LIVE — tempel keluaran curl -I yang sebenarnya]
```

### 5.2 Status Queue Worker

```bash
supervisorctl status
```

```
[ISI SETELAH DEPLOY LIVE — tempel keluaran supervisorctl status]
```

### 5.3 Uji Otorisasi terhadap Server HTTPS Produksi

```bash
BASE_URL=https://[ISI SETELAH DEPLOY LIVE KE RAILWAY].up.railway.app bash scripts/test-authz.sh
```

```
[ISI SETELAH DEPLOY LIVE — tempel keluaran lengkap scripts/test-authz.sh]
```

Catatan: `scripts/test-authz.sh` menginisialisasi sesi melalui rute `/switch-role/{role}`. Setelah Zaldy menutup rute tersebut di produksi (wajib), skrip ini tidak bisa dipakai apa adanya pada server live — lihat bagian 6.5.

### 5.4 Daftar Periksa 12 Poin Keamanan Produksi (Modul 12.1)

- [ ] `APP_ENV=production` dan `APP_DEBUG=false` terpasang pada variabel Railway.
- [ ] `APP_KEY` dibuat baru untuk produksi (bukan salinan `.env` lokal) dan tidak pernah di-commit.
- [ ] Document root mengarah ke `public/`; `curl -I /…/.env` menghasilkan 404/403.
- [ ] `.git` dan berkas tersembunyi tidak dapat diakses; `curl -I /storage/logs/laravel.log` 404/403.
- [ ] `composer.json`, `composer.lock`, dan `package.json` tidak dapat diakses lewat web.
- [ ] Rute bantu `/switch-role/{role}` dinonaktifkan di produksi (404/403).
- [ ] HTTPS aktif dengan sertifikat valid; `SESSION_SECURE_COOKIE=true`; tidak ada konten campuran (`http://`) di halaman.
- [ ] Kredensial MySQL hanya dari variabel platform (tidak tertulis di repo/dokumen) dan memakai user khusus aplikasi.
- [ ] Password akun demo produksi berbeda dari `password` dan hanya dicatat di dokumen ini.
- [ ] Rate limit login (`throttle:5,1`) aktif dan terverifikasi di server live.
- [ ] Queue worker berjalan (`supervisorctl status` → `RUNNING`) dan `queue:restart` dijalankan setelah deploy.
- [ ] Backup database dijadwalkan dan satu kali proses restore pernah diuji.

### 5.5 Catatan Uji dari HP Anggota Kelompok

| Penguji | Perangkat / Browser | Lebar Layar | Halaman yang Diuji | Hasil |
|:---|:---|:---:|:---|:---|
| `[nama]` | `[mis. Redmi Note 12 / Chrome 130]` | `[360px]` | `[login, dashboard, daftar tugas]` | `[ISI SETELAH DEPLOY LIVE]` |
| `[nama]` | `[mis. iPhone 13 / Safari]` | `[390px]` | `[login, detail mata kuliah]` | `[ISI SETELAH DEPLOY LIVE]` |
| `[nama]` | `[mis. Samsung A54 / Chrome]` | `[412px]` | `[rekap nilai, notifikasi]` | `[ISI SETELAH DEPLOY LIVE]` |
| `[nama]` | `[mis. iPhone 15 Pro Max / Safari]` | `[430px]` | `[unggah materi & tugas]` | `[ISI SETELAH DEPLOY LIVE]` |

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

### 6.4 `DatabaseSeeder` memakai Faker, sedangkan image produksi `--no-dev`

**Status:** teridentifikasi, ditangani pada Urutan 3 (Farin).

**Penjelasan:** `database/seeders/DatabaseSeeder.php` membuat pengguna dengan `User::factory()->create(...)`. Faker adalah dependensi `require-dev`, sementara `Dockerfile` menjalankan `composer install --no-dev`. Bila `php artisan db:seed --force` dijalankan di dalam container produksi apa adanya, proses akan gagal dengan kelas Faker tidak ditemukan. Karena itu `entrypoint.sh` **tidak** menjalankan seeding otomatis, dan data awal di produksi harus diisi lewat seeder khusus tanpa Faker (`DemoAccountSeeder`).

**Rencana penanganan:** `DemoAccountSeeder` + `config/demo.php` yang membaca `DEMO_*_PASSWORD`, dijalankan manual sekali setelah deploy. Sampai itu tersedia, isi akun awal hanya lewat mesin yang memiliki Faker (staging/lokal yang terhubung ke MySQL staging).

**Hasil akhir setelah diperbaiki:** `[ISI SETELAH URUTAN 3 SELESAI]`

### 6.5 `scripts/test-authz.sh` bergantung pada `/switch-role`, rute yang wajib ditutup di produksi

**Status:** konsekuensi lintas urutan (Nova mencatat, ditangani Zaldy pada Urutan 4).

**Penjelasan:** `routes/web.php` menyediakan `GET /switch-role/{role}` yang langsung melakukan `auth()->login($user)` untuk akun demo **tanpa** autentikasi apa pun. Di produksi, siapa pun yang membuka `/switch-role/admin` menjadi admin — ini celah eskalasi hak akses. Namun `scripts/test-authz.sh` memakai rute tersebut untuk membuat sesi mahasiswa dan dosen sebelum menguji 8 titik rawan IDOR. Setelah rute ditutup, skrip kehilangan cara login.

**Penanganan:** jalankan `scripts/test-authz.sh` pada lingkungan **staging/lokal** (di mana `/switch-role` masih boleh aktif), lalu untuk produksi ganti mekanisme inisialisasi sesinya dengan login form biasa (`POST /login` + cookie jar). Opsi lain: rute hanya diaktifkan bila variabel lingkungan eksplisit (mis. `APP_ENV=local`) menyetujuinya.

**Bukti setelah rute ditutup:** `[ISI SETELAH URUTAN 4 SELESAI]`

### 6.6 Ruang untuk kendala lanjutan (diisi Farin, Zaldy, Yuspa, dan tim)

7. **[Farin]** `[tulis kendala nyata saat menyiapkan `config/demo.php` / `DemoAccountSeeder` — mis. ketidakcocokan password hash, urutan seeder, atau reset data]`
8. **[Zaldy]** `[tulis kendala nyata saat hardening keamanan & deploy Railway pertama — mis. variabel tidak terbaca, koneksi MySQL ditolak, atau domain belum aktif]`
9. **[Yuspa]** `[tulis kendala nyata saat menyiapkan Supervisor, `scripts/deploy.sh`, dan verifikasi final pasca-live]`
10. **[Tim]** `[tulis kendala nyata saat uji dari HP pada server live — mis. cookie sesi tidak terkirim karena `SESSION_SECURE_COOKIE`, atau aset ter-cache versi lama]`

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
