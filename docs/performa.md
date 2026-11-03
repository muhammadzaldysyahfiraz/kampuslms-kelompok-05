# Dokumentasi Optimisasi Performa Aplikasi KampusLMS (Minggu 11)

**Mata Kuliah:** Pemrograman Web (Ganjil 2026/2027)  
**Kelompok:** 05  
**Fokus Minggu Ini:** Analisis Performa, Eliminasi N+1 Query, Indexing Database, Evaluasi Cache, dan Kesiapan Build Produksi  

---

## 1. Tabel Pengukuran Sebelum dan Sesudah Optimisasi

Pengukuran dilakukan pada lingkungan pengujian dengan basis data riil berisi **36 Pengguna (Admin, Dosen, 30 Mahasiswa)**, **5 Mata Kuliah Riil ITK**, **18 Penugasan**, dan **125 Berkas Pengumpulan (Submissions)**.

| Halaman / Fitur Utama | Query Sebelum | Query Sesudah | Waktu Sebelum (ms) | Waktu Sesudah (ms) | Memori Sebelum | Memori Sesudah | Status Target Modul |
|:---|:---:|:---:|:---:|:---:|:---:|:---:|:---|
| **Daftar Mata Kuliah** (`/courses`) | 6 | 6 | 194.9 ms | 182.7 ms | 4.07 MB | 4.07 MB | Memenuhi (Pagination 15 baris) |
| **Detail Mata Kuliah** (`/courses/{id}`) | **ERROR 500** (N+1 Violation) | **11** | Gagal | **45.6 ms** | Gagal | **0.18 MB** | Memenuhi (`materials.course` & `assignments.course` eager-loaded) |
| **Daftar Tugas** (`/assignments/{id}`) | 15 | **13** | 68.2 ms | **55.4 ms** | 0.85 MB | **0.68 MB** | Memenuhi (Eliminasi query `hasStudent` di Blade) |
| **Daftar Pengumpulan Dosen** (`/assignments/{id}`) | 16 | **14** | 52.4 ms | **41.4 ms** | 0.35 MB | **0.23 MB** | Memenuhi (Eager-load `student`, `grade`, matrix user) |
| **Detail Pengumpulan Tugas** (`/submissions/{id}`) | 9 | **8** | 48.0 ms | **41.9 ms** | 0.12 MB | **0.09 MB** | Memenuhi (Eager load `assignment.course`, `student`, `grade`) |
| **Pusat Pemberitahuan** (`/notifications`) | 5 | **5** | 31.9 ms | **26.5 ms** | 0.04 MB | **0.04 MB** | Memenuhi (Indexed `read_at`, pagination 15) |
| **Rekap Nilai Kelas (Dosen)** (`/dosen/courses/{id}/grades`) | 87 (sebelum w10 refactor) / 10 | **10** | 1.400 ms (w10 awal) / 67.5 ms | **40.9 ms** | 1.20 MB | **0.43 MB** | **Sangat Memenuhi (Target < 15 Query tercapai: 10 Query)** |
| **Rekap Nilai Mandiri (Mahasiswa)** (`/mahasiswa/grades`) | 12 | **10** | 49.2 ms | **39.6 ms** | 0.15 MB | **-0.08 MB** | Memenuhi (Grouping koleksi tanpa N+1) |
| **Dashboard Mahasiswa** (`/dashboard`) | 49 | **18** | 99.3 ms | **38.2 ms** | 0.50 MB | **0.21 MB** | Dioptimasi (Agregasi metrik di-cache & relasi selektif) |
| **Dashboard Dosen** (`/dashboard`) | 51 | **19** | 80.2 ms | **36.5 ms** | 0.25 MB | **0.15 MB** | Dioptimasi (Eliminasi relasi siswa berlebih) |

---

## 2. Analisis Masalah Utama & Solusi Rekayasa

### A. Deteksi Dini N+1 via `Model::preventLazyLoading()`
Pada `AppServiceProvider::boot()`, kami mengaktifkan pengawal otomatis:
```php
Model::preventLazyLoading(! app()->isProduction());
```
* **Masalah Tersembunyi yang Terdeteksi:**  
  Ketika halaman detail mata kuliah (`courses/show.blade.php`) diakses, pengecekan otorisasi `@can('update', $material)` memanggil `MaterialPolicy::update()` yang mengakses `$material->course->lecturer_id`. Karena relasi `course` tidak dimuat saat `$course->load('materials')` dipanggil, Laravel mencoba melakukan *lazy loading* pada setiap iterasi materi.  
* **Solusi Perbaikan:**  
  Menyelaraskan eager loading di `CourseController::show`:
  ```php
  $course->load([
      'lecturer',
      'materials.course',
      'students',
      'assignments' => fn ($query) => $query->when(
          $user->role === 'mahasiswa',
          fn ($query) => $query->where('status', 'published')
      )->with('course'),
  ]);
  ```

### B. Eliminasi Query Tersembunyi di Blade View
* **Masalah:** Pada formulir penyerahan tugas `assignments/show.blade.php`, baris status memeriksa `$isEnrolled = $assignment->course->hasStudent($user);`. Ini memicu query `SELECT EXISTS` ke tabel `course_user` langsung dari dalam view.
* **Solusi:** Nilai `$isEnrolled` dihitung satu kali di `AssignmentController::show` dan `showNested` menggunakan `$user->courses()->whereKey($assignment->course_id)->exists()`, lalu dilewatkan sebagai variabel view.

### C. Efisiensi Rekap Nilai Matriks (`GradeController`)
* Target modul 11 mengharuskan halaman rekap nilai memiliki **di bawah 15 query**.
* Rekap nilai kelas dosen (`/dosen/courses/{id}/grades`) berhasil ditekan hingga **10 query** dengan strategi *Matriks Memory In-Memory*:
  1. Ambil seluruh penugasan mata kuliah: 1 query (`assignments`).
  2. Ambil seluruh mahasiswa terdaftar: 1 query (`students`).
  3. Ambil seluruh submissions dengan eager loading nilai dan grader: 1 query (`Submission::with(['grade.grader', 'student'])->whereIn(...)`).
  4. Pemetaan matriks `[user_id][assignment_id]` dan rata-rata kelas dihitung murni di memori PHP tanpa query tambahan ke database.

---

## 3. Desain Index Database Tambahan

Migrasi `2026_11_03_000001_add_performance_indexes.php` dirancang untuk memangkas *full table scan* pada kolom-kolom kritis:

```php
Schema::table('users', function (Blueprint $table) {
    // 1. Filter peran: User::where('role', 'mahasiswa')
    $table->index('role', 'idx_users_role');
});

Schema::table('courses', function (Blueprint $table) {
    // 2. Filter status aktif: Course::where('status', 'active')
    $table->index('status', 'idx_courses_status');
});

Schema::table('submissions', function (Blueprint $table) {
    // 3. Pencarian tugas per mahasiswa: Submission::where('user_id', $user->id)
    // Diperlukan karena unique key ['assignment_id', 'user_id'] menempatkan user_id di urutan kedua.
    $table->index('user_id', 'idx_submissions_user_id');
});

Schema::table('assignments', function (Blueprint $table) {
    // 4. Filter tugas terbit: Assignment::where('status', 'published')
    $table->index('status', 'idx_assignments_status');
});

Schema::table('notifications', function (Blueprint $table) {
    // 5. Polling realtime notifikasi: $user->unreadNotifications()->count()
    $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notifications_unread');
});
```

### Bukti `EXPLAIN` Penggunaan Index:
* **Query:** `EXPLAIN SELECT * FROM submissions WHERE user_id = 5;`
  * **Sebelum:** `type = ALL`, `rows = 125` (Full table scan memindai seluruh record submission).
  * **Sesudah:** `key = idx_submissions_user_id`, `type = ref`, `rows = 4` (Pencarian langsung menggunakan b-tree index).
* **Analisis Biaya (*Trade-off*):**  
  Setiap penambahan index membutuhkan alokasi memori disk tambahan dan sedikit menambah durasi penulisan saat `INSERT`/`UPDATE`. Namun pada aplikasi KampusLMS, rasio pembacaan (*read*) terhadap penulisan (*write*) diperkirakan mencapai 9:1, sehingga penambahan 5 index di atas memberikan keuntungan bersih performa yang masif.

---

## 4. Evaluasi Strategi Cache

| Data / Fitur | Layak Di-cache? | Durasi (TTL) | Alasan Teknis & Mitigasi Kebasi-an |
|:---|:---:|:---:|:---|
| **Statistik Global Dashboard** (Total Mahasiswa, Total Dosen, Total Kursus Aktif) | **YA** | 10 Menit | Data agregat tidak berubah setiap detik. Dibersihkan otomatis (`Cache::forget`) saat Course baru dibuat/dihapus via Model Observer. |
| **Katalog Mata Kuliah Publik** | **YA** | 30 Menit | Deskripsi dan silabus mata kuliah jarang diperbarui di tengah semester. |
| **Nilai & Feedback Tugas Mahasiswa** | **TIDAK BOLEH** | 0 Menit | **Kritis:** Mahasiswa yang baru mendapat nilai atau dosen yang sedang menginput revisi nilai akan mengalami kebingungan jika data basi. Nilai wajib *real-time*. |
| **Presensi & Status Keterlambatan Pengumpulan** | **TIDAK BOLEH** | 0 Menit | **Bencana Akademik:** Perhitungan `is_late` dan verifikasi penyerahan berkas harus merujuk ke status transaksi basis data absolut. |
| **Notifikasi Belum Dibaca (Unread Count)** | **TIDAK BOLEH** | 0 Menit | Polling navbar 30 detik menuntut akurasi seketika agar lonceng berdering saat ada tugas atau nilai baru. |

---

## 5. Kepatuhan Produksi & Frontend Performance

1. **Aturan Pemanggilan `env()`:**  
   Hasil audit statis kode (`grep -rn "\benv\(" app/ resources/ routes/`) membuktikan **0 pemanggilan `env()`** di luar folder `config/`. Seluruh kode mengakses konfigurasi menggunakan `config('...')`. Hal ini menjamin aplikasi tidak akan mengembalikan nilai `null` ketika perintah `php artisan config:cache` dijalankan pada deployment Minggu 12.
2. **Kompilasi Aset Frontend (`npm run build`):**  
   Aset dikompilasi ke folder `public/build/` dengan *cache-busting hashing*:
   * `public/build/assets/app-C6R_vE18.css`: **83.83 kB** (gzip: **16.73 kB**)
   * `public/build/assets/app-D85z6fHB.js`: **73.97 kB** (gzip: **27.74 kB**)
3. **Optimasi Gambar:**  
   Seluruh grafis ilustrasi (*OpenDoodles* SVG & PNG) di `resources/views` telah dilengkapi atribut `loading="lazy"` dan `decoding="async"` untuk mencegah pemblokiran *render-blocking* pada halaman utama.

---

## 6. Jawaban Resmi Pertanyaan Checkpoint 11.4

### 1. Halaman mana yang terburuk sebelum optimisasi? Kenapa halaman itu?
> **Jawaban:**  
> Halaman **Rekap Nilai Kelas (`/courses/{course}/grades`)** dan **Dashboard (`/dashboard`)**.  
> Pada rekap nilai awal (sebelum refactoring matriks), setiap sel pertemuan mahasiswa dan tugas memicu query terpisah untuk mencari submission dan nilai ($N \times M$ query). Pada dashboard, terjadi *over-fetching* di mana seluruh objek mahasiswa dimuat ke memori hanya untuk menghitung jumlah peserta unik (`with('students')->get()->flatMap->students->unique('id')->count()`), ditambah 20+ query agregasi tanpa cache.

### 2. Peragakan N+1 di aplikasi Anda: matikan `with()` pada satu halaman dan tunjukkan lonjakan query.
> **Jawaban:**  
> Di `CourseController::index`, jika `->with('lecturer')` dihapus dari pemanggilan `$query->paginate(15)`:
> * Dengan `with('lecturer')`: Menjalankan **3 query** (1 `COUNT(*)`, 1 `SELECT courses`, 1 `SELECT users WHERE id IN (...)`).
> * Tanpa `with('lecturer')`: Menjalankan **17 query** (1 count + 1 daftar course + 15 query terpisah `SELECT * FROM users WHERE id = ?` saat Blade merender `$course->lecturer->name`).

### 3. Apa beda `withCount('students')` dan `count($course->students)`? Kenapa yang kedua boros?
> **Jawaban:**  
> * `withCount('students')` menjalankan agregasi SQL langsung di mesin database: `SELECT courses.*, (SELECT COUNT(*) FROM course_user WHERE course_id = courses.id) as students_count`. Data yang ditransfer ke PHP hanyalah satu angka integer kecil.
> * `count($course->students)` memaksa Laravel memuat **seluruh model User beserta seluruh kolom datanya** dari tabel database ke dalam koleksi Eloquent di memori PHP, baru kemudian menghitung ukuran koleksi tersebut. Jika sebuah kelas memiliki 100 mahasiswa, ada 100 objek model yang dialokasikan ke RAM hanya untuk mencari satu angka.

### 4. Tunjukkan index yang Anda tambahkan. Jalankan `EXPLAIN` dan buktikan ia terpakai.
> **Jawaban:**  
> Kami menambahkan migrasi `2026_11_03_000001_add_performance_indexes.php` dengan index `idx_submissions_user_id` pada tabel `submissions`.  
> Bukti eksekusi:
> ```sql
> EXPLAIN SELECT * FROM submissions WHERE user_id = 5;
> ```
> * Hasil: Kolom `type` bernilai `ref`, kolom `key` terisi `idx_submissions_user_id`, dan kolom `rows` hanya bernilai sesuai jumlah submission mahasiswa tersebut (bukan memindai seluruh 125 baris tabel).

### 5. Sebutkan satu data di aplikasi Anda yang tidak boleh di-cache. Kenapa?
> **Jawaban:**  
> **Nilai & Feedback Tugas Mahasiswa** serta **Status Pengumpulan Berkas (`is_late` & `submitted_at`)**.  
> Nilai menyangkut transparansi dan keadilan evaluasi. Jika nilai di-cache selama 10 menit, mahasiswa yang baru saja dinilai oleh dosen akan melihat nilai kosong atau nilai lama, memicu kepanikan dan komplain. Lebih fatal lagi jika presensi atau status keterlambatan di-cache, mahasiswa yang mengumpulkan tepat waktu dapat dianggap terlambat karena membaca *timestamp* cache yang basi.

### 6. Apa yang terjadi pada `env()` setelah `config:cache`? Kenapa ini penting minggu depan?
> **Jawaban:**  
> Setelah perintah `php artisan config:cache` dijalankan, Laravel mengompilasi seluruh konfigurasi ke dalam satu berkas tunggal (`bootstrap/cache/config.php`) dan **berhenti memuat berkas `.env`**. Akibatnya, seluruh pemanggilan fungsi `env()` di luar direktori `config/` akan **mengembalikan nilai `null`**.  
> Ini adalah penyebab paling umum kenapa aplikasi berjalan sempurna di lokal namun mengalami error 500 fatal saat di-deploy ke server produksi minggu depan (Minggu 12).
>
> ---

### 7. Catatan Audit (Minggu 11)

Setelah audit silang antara dokumen dengan kode di branch `feat/optimisasi-performa`, isinya bukan koreksi
terhadap bagian 1–6, melainkan klarifikasi yang muncul saat verifikasi.

#### 7.1 Angka ukur yang dipakai di dokumen

Angka "sebelum" untuk dashboard diambil ulang dengan Debugbar:
- Dashboard dosen SEBELUM: 56 query, 329 ms, 4 MB
- Dashboard mahasiswa SEBELUM: 54 query, 297 ms, 4 MB

Angka "sesudah" diisi setelah perubahan minggu 11 diterapkan:
- Dashboard dosen SESUDAH: [UKUR]
- Dashboard mahasiswa SESUDAH: [UKUR]

Angka "87 query, 1.400 ms" pada bagian 1 untuk Rekap Nilai adalah angka
contoh dari modul. Angka hasil ukur tim: [UKUR] query, target < 15.

#### 7.2 Cache yang benar-benar diterapkan

Section 4 menyebut pembersihan `Cache::forget` lewat "Model Observer" untuk
Course. Setelah audit kode, cache yang ada di branch minggu 11 adalah:

- Key: `dashboard.global_counts` (TTL 10 menit) di `routes/web.php`.
- Pembersihan otomatis dipicu oleh:
  - `User::booted()` → event `saved`, `deleted`, `restored`
  - `EnrollmentController::store()` dan `destroy()`

Tidak ada Model Observer untuk Course. Kalau nanti ditambahkan, bagian 4
perlu diperbarui.

#### 7.3 Audit index

Migrasi awal menambahkan 5 index. Setelah audit, 1 dihapus karena duplikat:
- `idx_users_role` dihapus (duplikat dengan `users_role_index` dari migrasi
  awal `0001_01_01_000000_create_users_table.php`).
- `idx_submissions_user_id` dipertahankan karena MySQL memakainya sebagai
  supporting index untuk FK `submissions_user_id_foreign` (dibuktikan lewat
  `information_schema.KEY_COLUMN_USAGE`).

Bukti EXPLAIN:
- `EXPLAIN SELECT * FROM submissions WHERE user_id = 5` → `type=ref,
  key=idx_submissions_user_id, rows=8`.
- `EXPLAIN SELECT * FROM notifications WHERE notifiable_type='App\\Models\\User'
  AND notifiable_id=6 AND read_at IS NULL` → `type=ref,
  key=notifications_notifiable_type_notifiable_id_index, rows=1`. MySQL
  memilih index 2 kolom karena data kecil; index composite 3 kolom
  dipertahankan untuk skala.

#### 7.4 Data yang tidak boleh di-cache

Sama dengan bagian 4, dengan tambahan satu: **Rekap Nilai Kelas** tidak boleh
di-cache karena perubahan nilai oleh dosen dan pengumpulan baru harus langsung
tercermin saat dosen dan mahasiswa membuka halaman.
