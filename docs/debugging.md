# Dokumentasi Debugging & Analisis Kegagalan Sistem (Minggu 14)

> **Mata Kuliah:** Pemrograman Web (SI2514024) — Semester Ganjil 2026/2027  
> **Kelompok:** 05 — KampusLMS  
> **Penulis:** Tim Pengembang Kelompok 05 (Database Engineer & Backend)  

---

## 1. Metodologi Debugging Sistematis

Dalam pengembangan KampusLMS, perbaikan bug tidak dilakukan dengan mengubah kode secara acak, melainkan melalui 5 langkah disiplin:
1. **Reproduksi:** Mencatat langkah persis dan input yang memicu kegagalan hingga bug dapat diulang 100%.
2. **Persempit:** Mengisolasi letak kegagalan dengan membaca baris pertama pada jejak *stack trace* yang menunjuk ke folder `app/` atau `database/`, bukan pada folder `vendor/`.
3. **Rumuskan Hipotesis Tunggal:** Menyusun dugaan penyebab tunggal ("Saya menduga X karena Y").
4. **Uji Hipotesis:** Menguji hipotesis dengan pemeriksaan terisolasi (profiling query, log terstruktur, atau inspeksi skema).
5. **Perbaiki & Tulis Regression Test:** Memperbaiki akar masalah, lalu mengunci perbaikan tersebut dengan unit/feature test agar kesalahan yang sama tidak pernah berulang.

---

## 2. Tiga Kasus Bug Nyata & Pencegahannya

### Kasus 1: Ketergantungan Data Seeder pada Model Factories (Seederless DB Failure)

* **Gejala:**  
  Saat feature test baru dijalankan menggunakan trait `RefreshDatabase`, pemanggilan `Course::factory()->create()` atau `Assignment::factory()->create()` melempar exception:
  ```text
  SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: courses.lecturer_id
  ```
* **Langkah Mempersempit & Analisis:**  
  1. Ditelusuri ke berkas `database/factories/CourseFactory.php`.
  2. Ditemukan definisi kolom `lecturer_id` lama:
     ```php
     'lecturer_id' => User::where('role', 'dosen')->inRandomOrder()->value('id'),
     ```
  3. Pada lingkungan test dengan `RefreshDatabase`, database di-reset dalam keadaan kosong sempurna. Kueri `where('role', 'dosen')->value('id')` mengembalikan `NULL`. Karena kolom `lecturer_id` memiliki aturan `NOT NULL` berelasi `restrictOnDelete`, database menolak insert data tersebut.
* **Perbaikan yang Diterapkan:**  
  Menambahkan mekanisme *fallback autonomous factory* menggunakan closure:
  ```php
  'lecturer_id' => fn () => User::where('role', 'dosen')->inRandomOrder()->value('id') 
      ?? User::factory()->create(['role' => 'dosen'])->id,
  ```
  Pola yang sama diterapkan pada seluruh factory (`AssignmentFactory`, `MaterialFactory`, `SubmissionFactory`, dan `GradeFactory`).
* **Test Pencegah (Regression Test):**  
  `tests/Feature/WeekFourteenDatabaseTest.php` method `test_seederless_database_factories_can_generate_all_models_without_seeder()`.

---

### Kasus 2: Duplikasi Nama Index pada Skema Database saat Rollback (`migrate:refresh`)

* **Gejala:**  
  Saat CI atau pengembang lokal menjalankan pengujian reversibilitas migrasi dengan `php artisan migrate:refresh --force`, eksekusi berhenti di tengah jalan dengan error:
  ```text
  SQLSTATE[42000]: Syntax error or access violation: 1061 Duplicate key name 'users_role_index'
  ```
* **Langkah Mempersempit & Analisis:**  
  1. Jejak stack trace menunjuk pada berkas migrasi `2026_11_03_000001_add_performance_indexes.php`.
  2. Dilakukan penelusuran terhadap migrasi awal `0001_01_01_000000_create_users_table.php`, di mana index pada kolom `role` ternyata sudah didefinisikan sebelumnya (`$table->index('role')`).
  3. Penambahan index kedua dengan nama identik pada migrasi optimasi performa memicu duplikasi nama kunci pada DBMS MySQL/MariaDB.
* **Perbaikan yang Diterapkan:**  
  1. Dibuat migrasi penyesuaian `2026_11_03_200137_drop_duplicate_users_role_index.php` yang membersihkan duplikasi index secara aman.
  2. Seluruh definisi fungsi `down()` pada setiap berkas migrasi dipastikan simetris, menjatuhkan foreign key terlebih dahulu sebelum menjatuhkan tabel induk.
* **Test Pencegah (Regression Test):**  
  1. Otomasi CI GitHub Actions pada *step* `Verify Reversible Migrations (migrate:refresh)`.
  2. `tests/Feature/WeekFourteenDatabaseTest.php` method `test_database_cascade_and_restrict_integrity_constraints()`.

---

### Kasus 3: Kegagalan Rendering View Akibat Ketiadaan Manifest Aset Vite di Lingkungan Uji

* **Gejala:**  
  Ketika feature test pengujian halaman web dieksekusi (seperti `AuthTest::test_login_page_can_be_rendered`), seluruh pengujian gagal secara massal dengan pesan error:
  ```text
  Vite manifest not found at: public/build/manifest.json (View: resources/views/components/layout.blade.php)
  ```
* **Langkah Mempersempit & Analisis:**  
  1. Dilakukan isolasi pengujian: request berstatus HTTP 500 alih-alih HTTP 200/302.
  2. Jejak stack trace menunjuk pada komponen Blade induk `resources/views/components/layout.blade.php` pada baris pemanggilan direktif `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
  3. Laravel Vite plugin membaca `manifest.json` hasil *build bundle* untuk memetakan nama berkas aset terenkripsi hash. Di mesin baru atau workspace lokal yang belum menjalankan *build toolchain*, direktori `public/build` belum ada.
* **Perbaikan yang Diterapkan:**  
  1. Menjalankan kompilasi aset sekali untuk lingkungan lokal via `npm run build`.
  2. Memastikan skrip otomatisasi CI (`.github/workflows/ci.yml`) menempatkan langkah `Install NPM Dependencies & Build Assets` tepat sebelum perintah `php artisan test` dijalankan.
* **Test Pencegah (Regression Test):**  
  `tests/Feature/AuditPagesTest.php` method `test_pages_audit()` yang mengaudit 27 rute antarmuka pengguna untuk memastikan seluruh view ter-render tanpa kendala dependensi aset.

---

## 3. Kesimpulan

Dengan menerapkan penanganan *eager loading* (`Model::preventLazyLoading`), pembenahan *fallback factories*, serta kelengkapan *feature tests*, stabilitas basis data dan keamanan alur bisnis KampusLMS terjamin baik pada lingkungan pengujian lokal maupun pipa integrasi berkelanjutan (CI).
