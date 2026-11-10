# KampusLMS — Kelompok 05

**Mata Kuliah:** SI2514024 — Pemrograman Web (Semester Ganjil 2026/2027)  
**Program Studi:** Sistem Informasi — Institut Teknologi Kalimantan (ITK)  
**Dosen Pengampu:** Pak Aidil Saputra Kirsan  
**Asisten Dosen:** Kak Achmad Zaki Zaidan  

---

## 📌 Deskripsi Proyek
**KampusLMS** adalah platform Learning Management System (LMS) berbasis web yang dibangun menggunakan **Laravel 12** dan **MySQL**. Sistem ini dirancang untuk menangani alur inti perkuliahan akademik secara terstruktur: autentikasi multi-role (Admin, Dosen, Mahasiswa), manajemen mata kuliah, enrollment, distribusi materi pembelajaran, pengumpulan dan penilaian tugas, serta sistem notifikasi otomatis.

---

## 👥 Tim Pengembang (Kelompok 05)

| No | Nama Anggota | NIM | Peran / Pembagian Tugas | Akun GitHub |
|:--:|:---|:---:|:---|:---|
| 1 | **Muhammad Rifa Al Rizqul Aulia** | 10241050 | Frontend Developer | [@rifarizqul](https://github.com/rifarizqul) |
| 2 | **Nova Reskianti** | 10241058 | Frontend Developer | [@Novares06](https://github.com/Novares06) |
| 3 | **Muhammad Zaldy Syah Firaz** | 10241054 | Backend Developer | [@muhammadzaldysyahfiraz](https://github.com/muhammadzaldysyahfiraz) |
| 4 | **Muhammad Yuspa Ardiansyah** | 10241052 | Backend Developer | [@ardiansyahyus24](https://github.com/ardiansyahyus24) |
| 5 | **Muhammad Farin Murtadho Syafiq** | 10241046 | Database Engineer | [@muhammadfarin18](https://github.com/muhammadfarin18) |

---

## 🛠️ Persyaratan Lingkungan (Prerequisites)

* **PHP:** `^8.4.1` (mengikuti versi minimum paket yang terkunci di `composer.lock`)
* **Composer:** `>= 2.2`
* **Database:** MySQL 8.x / MariaDB 11.x
* **Web Server / CLI:** Laravel Development Server (`php artisan serve`) atau Laravel Herd
* **Node.js & NPM:** diperlukan untuk membangun asset frontend dengan Vite

---

## 🚀 Panduan Instalasi & Menjalankan Proyek di Lokal

### 1. Kloning Repositori
```bash
git clone https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05.git
cd kampuslms-kelompok-05
```

### 2. Instalasi Dependensi PHP
```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)
Salin berkas konfigurasi template `.env.example`:
```bash
copy .env.example .env
```
Lalu buat Application Key baru:
```bash
php artisan key:generate
```

### 4. Pengaturan Database
Pastikan layanan MySQL/MariaDB sudah berjalan. Buka file `.env` dan sesuaikan kredensial database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kampus_db
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan database `kampus_db` sudah dibuat di phpMyAdmin / DBMS Anda)*.

### 5. Jalankan Migrasi Database
```bash
php artisan migrate
```

### 6. Menjalankan Server Lokal
```bash
php artisan serve
```
Akses aplikasi melalui web browser di:
👉 **[http://localhost:8000](http://localhost:8000)** atau **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

Halaman informasi kelompok dapat diakses di:
👉 **[http://localhost:8000/tentang](http://localhost:8000/tentang)**

### Menjalankan antrean notifikasi

Alurnya sederhana: controller menyimpan tugas atau nilai, lalu mengirim event. Listener menerima event itu di antrean agar request tidak menunggu proses pengiriman. `SendAssignmentNotification` memberi tahu mahasiswa yang terdaftar ketika tugas terbit; `SendGradeNotification` hanya memberi tahu mahasiswa pemilik submission. Notification menyimpan ID, judul, mata kuliah, dan tautan. Nilai serta feedback tidak dimasukkan ke data notifikasi.

Template `.env.example` sudah memakai `QUEUE_CONNECTION=database`; setelah migrasi, jalankan worker di terminal tersendiri:

```bash
php artisan migrate
php artisan queue:work --sleep=3 --tries=3
```

Tanpa worker, tugas dan nilai tetap tersimpan, tetapi notifikasinya hanya menunggu di tabel `jobs`. Periksa kegagalan dengan `php artisan queue:failed`, lalu coba ulang dengan `php artisan queue:retry all`.

---

## 🌐 Aplikasi Live & Deployment Produksi (Minggu 12)

**URL aplikasi live (HTTPS):**

👉 `https://[ISI SETELAH DEPLOY LIVE KE RAILWAY].up.railway.app`

> Alamat di atas masih berupa *placeholder* dan akan diperbarui segera setelah domain Railway aktif. Aplikasi produksi dijalankan sebagai container Docker multi-stage (PHP 8.4 + Apache, aset Vite dibangun di stage Node 20) di atas MySQL, sehingga siapa pun dapat membukanya dari HP melalui HTTPS.

Panduan lengkap — arsitektur server, variabel produksi, langkah deploy dari nol, cara deployment ulang, cara membaca log, kredensial akun demo untuk penguji, checklist 12 poin keamanan, serta daftar masalah teknis beserta solusinya — tersedia di:

* 📄 **[`docs/deployment.md`](docs/deployment.md)**
* 🐳 **[`Dockerfile`](Dockerfile)** — image produksi (multi-stage); pendampingnya `docker/entrypoint.sh`, `docker/php/uploads.ini`, dan `.dockerignore`

### Akun Demo

Di lingkungan **lokal**, tiga akun demo berikut tersedia dengan password yang sama, yaitu `password` (tombol isi-cepat juga tersedia di halaman login):

| Peran | Email | Password (lokal) |
|:---|:---|:---|
| Admin | `admin@kampuslms.test` | `password` |
| Dosen | `dosen@kampuslms.test` | `password` |
| Mahasiswa | `mahasiswa@kampuslms.test` | `password` |

Pada lingkungan **produksi**, password akun demo **diganti** melalui `DemoAccountSeeder` (membaca variabel `DEMO_*_PASSWORD` di Railway). Password produksi tersebut hanya dicatat di [`docs/deployment.md`](docs/deployment.md) untuk keperluan pengujian dosen/penguji, dan **tidak ditulis di README ini**.

---

## 🌿 Struktur Branching & Alur Kerja Git
* `main` — Branch produksi/stabil (dilindungi branch protection).
* `dev` — Branch integrasi fitur.
* `feat/<nama-fitur>` — Branch pengembangan fitur mandiri per anggota.

---

## 📄 Lisensi
Proyek ini dikembangkan untuk kebutuhan akademik perkuliahan Pemrograman Web SI ITK.
