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

* **PHP:** `>= 8.3` (atau minimal 8.2)
* **Composer:** `>= 2.2`
* **Database:** MySQL 8.x / MariaDB 11.x (via Laragon / XAMPP / Native)
* **Web Server / CLI:** Laravel Development Server (`php artisan serve`) atau Laravel Herd
* **Node.js & NPM:** (Opsional untuk asset bundler Vite)

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
Pastikan layanan MySQL di Laragon / XAMPP sudah berjalan. Buka file `.env` dan sesuaikan kredensial database:
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

---

## 🌿 Struktur Branching & Alur Kerja Git
* `main` — Branch produksi/stabil (dilindungi branch protection).
* `dev` — Branch integrasi fitur.
* `feat/<nama-fitur>` — Branch pengembangan fitur mandiri per anggota.

---

## 📄 Lisensi
Proyek ini dikembangkan untuk kebutuhan akademik perkuliahan Pemrograman Web SI ITK.
