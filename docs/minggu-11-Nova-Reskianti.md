# Catatan Praktikum Minggu 11 Pemrograman Web: Optimisasi Performa

---

**Nama Mahasiswa:** Nova Reskianti  
**NIM:** 10241058  
**Kelompok:** 05  
**Fokus Peran:** Frontend Developer (Optimisasi Halaman Dashboard, Rekap Nilai Kelas / Ledger Dosen, Transkrip Nilai Mahasiswa, Katalog & Detail Mata Kuliah, Efisiensi Render DOM, dan Asset Bundling)

---

## Catatan Kolaborasi & Pembagian Peran Minggu 11

Pada Minggu ke-11 ini, tim Pengembang KampusLMS Kelompok 05 berfokus penuh pada **Optimisasi Performa Sistem**:
1. **Frontend Developer:**
   - **Muhammad Rifa Al Rizqul Aulia (@rifarizqul-itk):** Mengoptimasi halaman penugasan, materi, notifikasi, asset bundling Vite, dan menyusun dokumen benchmark bersama (`docs/performa.md`).
   - **Nova Reskianti (@NovaRes06 - Saya):** Mengoptimasi halaman Dashboard, Rekap Nilai Kelas (`/courses/{course}/grades`), Transkrip Nilai Mahasiswa (`/mahasiswa/grades`), Katalog Mata Kuliah (`/courses`), Detail Mata Kuliah (`/courses/{course}`), memastikan isolasi container scroll matriks nilai, serta eliminasi celah query tersembunyi pada template Blade.
2. **Backend & Database Developer:**
   - **Muhammad Zaldy Syah Firaz (@muhammadzaldysyahfiraz)** & **Muhammad Yuspa Ardiansyah (@ardiansyahyus24):** Menangani optimasi query controller, eager loading `with()`, agregasi SQL `withCount()`, dan strategi caching.
   - **Muhammad Farin Murtadho Syafiq (@BerserkerGats):** Merancang migrasi penambahan index database (`idx_submissions_user_id`, `idx_courses_status`, dll.) dan analisis `EXPLAIN`.

Sesuai batasan peran frontend, saya **tidak menyentuh migrasi database SQL maupun controller backend**, melainkan memastikan lapisan antarmuka pengguna (*user interface layer*) bebas dari *layout thrashing*, hemat memori peramban, dan tidak memicu query database baru secara diam-diam.

---

# READ: Optimisasi Performa dari Sudut Pandang Frontend

Performa aplikasi web tidak hanya ditentukan oleh seberapa cepat database merespons query SQL, tetapi juga bagaimana peramban (*browser*) mengolah, menyusun, dan menampilkan data tersebut ke layar pengguna (*browser runtime performance*). 

### 1. Beban Render DOM pada Tabel Matriks Nilai ($M \times N$)
Halaman paling menantang di domain saya adalah **Matriks Rekap Nilai Kelas Dosen** (`grades/course.blade.php`).
* **Karakteristik Beban:** Jika sebuah mata kuliah memiliki 30 mahasiswa ($M$) dan 18 penugasan ($N$), tabel matriks harus merender setidaknya:
  $$\text{Total Sel Nilai} = 30 \times 18 = 540 \text{ sel interaktif}$$
  Setiap sel memuat angka skor, batas maksimal, badge status keterlambatan, serta tautan aksi cepat. Jika tidak dibatasi dan diisolasi dengan benar, ukuran pohon DOM (*DOM Tree Depth*) melonjak drastis dan menyebabkan peramban mengalami *frame drop* (stutter) saat di-scroll.
* **Solusi Frontend:**
  1. Membungkus tabel dalam container mandiri dengan kelas `overflow-x-auto overscroll-x-contain` agar scroll horizontal tidak membajak (*hijack*) scroll halaman utama.
  2. Mengunci kolom identitas mahasiswa menggunakan `sticky left-0` dengan latar belakang solid (`bg-white` / `bg-slate-100`) agar peramban tidak melakukan *paint recalculation* berulang pada sel di belakangnya.
  3. Memastikan rendering tabel bersifat murni membaca data matriks array PHP yang sudah disiapkan oleh controller, tanpa pemanggilan relasi database sama sekali di dalam loop `@foreach`.

### 2. Kompilasi Aset Produksi: `npm run build` vs `npm run dev`
Modul 11 secara tegas menekankan aturan:
> *"`npm run build`, bukan `npm run dev`, di produksi."*

* **Mode Development (`npm run dev`):** Menjalankan server dev Vite berbasis ESM (*ECMAScript Modules*) lokal. CSS dan JS dimuat secara dinamis tanpa minifikasi agar *Hot Module Replacement* (HMR) berjalan cepat saat coding. Ini sangat boros bandwidth jika digunakan di server publik.
* **Mode Produksi (`npm run build`):**
  1. **Rollup Bundling & Tree-shaking:** Memangkas seluruh CSS Tailwind v4 dan JavaScript yang tidak digunakan dalam file Blade.
  2. **Minifikasi:** Menghapus spasi dan komentar sehingga ukuran file transfer turun drastis.
  3. **Content Hashing (Cache-Busting):** Menghasilkan nama unik berkas seperti `app-ZoRSiarf.css`. Ini memungkinkan web server menyetel header `Cache-Control: max-age=31536000, immutable`, sehingga peramban pengguna menyimpan aset di cache lokal tanpa perlu mengunduh ulang setiap berganti halaman.

### 3. Pencegahan Layout Shift dengan Lazy Loading & Async Decoding
Pada halaman katalog mata kuliah dan dashboard, elemen visual ilustrasi (*OpenDoodles SVG/PNG*) dioptimasi dengan:
* `loading="lazy"`: Mencegah peramban mengunduh gambar sebelum pengguna melakukan scroll mendekati posisi gambar tersebut.
* `decoding="async"`: Memindahkan proses decoding grafis keluar dari *main browser thread*, sehingga transisi halaman tetap responsif (60 FPS).

### 4. Kebersihan Template Blade (*Blade Template Hygiene*)
Template Blade adalah lapisan representasi visual murni. Pelanggaran terbesar performa frontend di Laravel adalah **query yang bocor di dalam Blade view**. Jika di dalam Blade view tertulis pemanggilan method relasi dinamis seperti `$course->students()->count()` atau `$course->hasStudent($user)`, Blade akan mengirimkan query SQL baru ke database pada setiap iterasi baris tampilan.

---

# BREAK: Pengamatan Eksperimen Modul 11 pada Domain Saya

Berdasarkan 7 percobaan pada modul 11.3, berikut hasil pengamatan langsung pada domain halaman saya (Dashboard, Matriks Nilai, dan Katalog Kursus):

| # | Percobaan Modul | Pengamatan pada Halaman Saya (Dashboard & Rekap Nilai) | Refleksi & Mitigasi Frontend |
|:--:|:---|:---|:---|
| **1** | Pasang `Model::preventLazyLoading()` | Pada halaman detail mata kuliah (`courses/show.blade.php`), pemanggilan relasi peserta memicu error jika relasi `students` tidak di-load dari controller. | Mengubah pengecekan di Blade menjadi in-memory: `$course->relationLoaded('students') ? $course->students->count() : $course->students_count`. |
| **2** | Mematikan `with()` di Controller | Ketika eager loading dimatikan pada katalog mata kuliah (`/courses`), query melonjak dari 6 query menjadi 17+ query karena Blade memanggil `$course->lecturer->name` di setiap kartu. | Memastikan kartu katalog dirancang tangguh dengan fallback text jika relasi kosong, serta berkoordinasi dengan backend agar `with('lecturer')` selalu aktif. |
| **3** | `withCount()` vs `count()` koleksi | Menghitung jumlah peserta kelas menggunakan `count($course->students)` memaksa alokasi 30 objek User ke RAM server per mata kuliah. | Di Blade view, saya selalu memprioritaskan properti `$course->students_count` yang dihitung langsung oleh agregasi SQL database. |
| **4** | Dampak Index Database | Sebelum penambahan index `idx_submissions_user_id`, query riwayat nilai mahasiswa lambat saat data submission bertambah banyak. | Berkoordinasi dengan Farin (Database) agar index foreign key dan kolom filter query utama aktif di database. |
| **5** | Uji Seeder Skala Penuh (30 Mahasiswa, 125 Submissions) | Tabel matriks rekap nilai kelas merender 540 sel. Tampilan tetap stabil tanpa freeze karena rendering dilakukan secara linier berbasis array $M \times N$. | Memberikan scroll horizontal terisolasi `overscroll-x-contain` pada kontainer tabel agar peramban desktop dan mobile tidak lag. |
| **6** | `php artisan config:cache` & pemanggilan `env()` | Ketika `config:cache` aktif, seluruh pemanggilan `env()` di luar `config/` mengembalikan `null`. | Melakukan audit seluruh Blade view di domain saya. Terbukti **0 pemanggilan `env()`**; semua link dan judul memakai `config('app.name')`. |
| **7** | Eksperimen Cache pada Nilai Mahasiswa | Menyimulasikan cache 10 menit pada nilai tugas. Dosen menginput nilai baru, namun saat mahasiswa me-refresh halaman transkrip, nilainya masih kosong/lama. | **Menegaskan keputusan tim:** Nilai, feedback dosen, dan status keterlambatan pengumpulan (`is_late`) **HARAM di-cache** demi keadilan akademik. |

---

# FIX: Pembenahan Template Tampilan (Domain Nova)

Berikut adalah perbaikan konkret yang saya terapkan pada template Blade:

### 1. Optimasi Kontainer Matriks Rekap Nilai (`resources/views/grades/course.blade.php`)
* **Masalah:** Matriks tabel nilai kelas dapat menyebabkan pergeseran layout (*layout shift*) dan sulit dinavigasi oleh pengguna keyboard pada layar sempit.
* **Solusi Perbaikan:**  
  Menambahkan atribut aksesibilitas, isolasi scroll, dan solid background pada sel sticky:
  ```blade
  @if ($students->isNotEmpty() && $assignments->isNotEmpty())
      <div class="overflow-x-auto overscroll-x-contain focus:outline-none focus:ring-1 focus:ring-slate-300 rounded-b-xl" 
           tabindex="0" 
           role="region" 
           aria-label="Tabel Matriks Rekap Nilai Kelas">
          <table class="w-full text-left text-xs border-collapse">
              <thead>
                  <tr class="border-b border-slate-200 bg-slate-100/90 text-slate-700 font-semibold uppercase tracking-wider text-[11px]">
                      {{-- Kolom Mahasiswa Sticky Kiri dengan Background Solid --}}
                      <th scope="col" class="py-3.5 px-4 sticky left-0 bg-slate-100 z-20 min-w-[220px] border-r border-slate-200/80">
                          Mahasiswa
                      </th>
                      ...
  ```
  Dengan `overscroll-x-contain`, saat pengguna melakukan scroll horizontal di dalam tabel hingga mentok, peramban tidak akan memicu gesture back/forward bawaan browser.

### 2. Eliminasi Potensi Query Bocor di `resources/views/courses/show.blade.php`
* **Masalah:** Pengecekan jumlah mahasiswa sebelumnya menggunakan `$course->students_count ?? ($course->students ? $course->students->count() : 0);`. Jika relasi `students` tidak di-load, Blade akan memicu query *lazy loading* ke database.
* **Solusi Perbaikan:**  
  Menggunakan pengawal `relationLoaded()`:
  ```blade
  @php
      $stdCount = $course->students_count ?? ($course->relationLoaded('students') ? $course->students->count() : 0);
  @endphp
  ...
  <h4 class="text-xs font-bold text-slate-900 mb-2">
      Daftar Peserta ({{ $course->relationLoaded('students') ? $course->students->count() : $stdCount }})
  </h4>

  @if ($course->relationLoaded('students') && $course->students->isNotEmpty())
      <div class="max-h-52 overflow-y-auto space-y-2 pr-1 divide-y divide-slate-100">
          @foreach ($course->students as $student)
  ```

### 3. Pemuatan Aset Gambar Cerdas (Lazy Load & Async)
Seluruh grafis ilustrasi (*OpenDoodles SVG*) pada `dashboard.blade.php`, `courses/index.blade.php`, dan `courses/show.blade.php` dipastikan memiliki:
```html
<img src="{{ asset('images/illustrations/opendoodles-coffee.svg') }}"
     alt=""
     aria-hidden="true"
     class="w-20 sm:w-24 md:w-28 h-auto diorama-doodle object-contain filter drop-shadow-2xs"
     loading="lazy"
     decoding="async">
```

---

# BUILD: Hasil Kompilasi Aset & Penyelarasan Tim

### 1. Kompilasi Aset Produksi Vite
Saya menjalankan kompilasi aset frontend menggunakan perintah `npm run build`:
```text
> build
> vite build

vite v6.4.3 building for production...
transforming...
✓ 60 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json             0.27 kB │ gzip:  0.15 kB
public/build/assets/app-ZoRSiarf.css  97.28 kB │ gzip: 18.46 kB
public/build/assets/app-D85z6fHB.js   73.97 kB │ gzip: 27.74 kB
✓ built in 1.14s
```
* **Hasil:**
  - CSS utama terkompresi menjadi **97.28 kB** (hanya **18.46 kB** saat ditransfer lewat kompresi gzip di jaringan).
  - JavaScript terkompresi menjadi **73.97 kB** (hanya **27.74 kB** gzip).
  - Waktu build ultra cepat: **1.14 detik**.

### 2. Hasil Pengujian Otomatis (`php artisan test`)
Seluruh 50 pengujian fitur (225 assertions) diuji dan **berhasil 100% lolos (PASS)**:
```text
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\AuditPagesTest
PASS  Tests\Feature\AuthTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\WeekFiveAccessTest
PASS  Tests\Feature\WeekNineFileTest
PASS  Tests\Feature\WeekSixApiTest
PASS  Tests\Feature\WeekTenGradebookTest

Tests:    50 passed (225 assertions)
Duration: 4.82s
```

### 3. Penyelarasan dengan Tim Backend & Database
* **Bersama Rifa (Frontend):** Menyelaraskan desain antarmuka, konsistensi token warna Swiss Design, dan verifikasi dokumen benchmark `docs/performa.md`.
* **Bersama Zaldy & Yuspa (Backend):** Memastikan controller `GradeController@indexForCourse` dan `GradeController@indexMine` tetap menjaga struktur respons matriks array in-memory sehingga query tetap di angka **10 query** (jauh di bawah batas modul < 15 query).
* **Bersama Farin (Database):** Mengonfirmasi bahwa index database pada `submissions.user_id` telah aktif dan mempercepat pemuatan riwayat transkrip nilai mahasiswa.

---

# Checkpoint 11.4: Evaluasi Pembelajaran Minggu 11

### 1. Buka `docs/performa.md`. Halaman mana yang terburuk sebelum optimisasi? Kenapa halaman itu?
> **Jawaban:**  
> Halaman terburuk sebelum optimisasi adalah **Rekap Nilai Kelas Dosen (`/courses/{course}/grades`)** dan **Dashboard (`/dashboard`)**.  
> Pada rekap nilai kelas, sebelum arsitektur matriks in-memory diterapkan, sistem mencoba menjalankan query terpisah untuk setiap persilangan baris mahasiswa dan kolom tugas ($M \times N$). Dengan 30 mahasiswa dan 18 tugas, sistem memicu hingga 87+ query dan memakan waktu lebih dari 1.400 ms. Pada dashboard, terjadi pemuatan berlebih (*over-fetching*) seluruh record relasi tanpa pemilihan kolom spesifik.

### 2. Peragakan N+1 di aplikasi Anda: matikan `with()` pada satu halaman dan tunjukkan lonjakan query.
> **Jawaban:**  
> Pada katalog kursus (`CourseController::index`), query awal menggunakan:
> ```php
> $courses = Course::with('lecturer')->paginate(15);
> ```
> * **Dengan `with('lecturer')`:** Hanya mengeksekusi **3 query** (1 `COUNT(*)` untuk pagination, 1 `SELECT courses`, dan 1 `SELECT users WHERE id IN (...)`).
> * **Jika `with('lecturer')` dimatikan:** Jumlah query melonjak menjadi **17 query** (1 count + 1 courses + 15 query terpisah `SELECT * FROM users WHERE id = ?` saat kartu Blade merender `$course->lecturer->name`).

### 3. Apa beda `withCount('students')` dan `count($course->students)`? Kenapa yang kedua boros?
> **Jawaban:**  
> * `withCount('students')` meminta database menghitung jumlah baris menggunakan subquery SQL agregat `(SELECT COUNT(*) FROM course_user ...)`. Data yang dikirim ke aplikasi hanyalah satu bilangan bulat (*integer*), sangat ringan dan cepat.
> * `count($course->students)` memaksa Laravel mengeksekusi `SELECT * FROM users INNER JOIN course_user ...`, memuat puluhan objek model `User` lengkap beserta atributnya ke dalam memori RAM PHP, lalu baru menghitung jumlah item di koleksi tersebut. Ini sangat boros memori dan memperlambat waktu respon server.

### 4. Tunjukkan index yang Anda tambahkan. Jalankan `EXPLAIN` dan buktikan ia terpakai.
> **Jawaban:**  
> Tim database kami (Farin) menambahkan index `idx_submissions_user_id` pada kolom `user_id` di tabel `submissions`.  
> Bukti perintah `EXPLAIN`:
> ```sql
> EXPLAIN SELECT * FROM submissions WHERE user_id = 5;
> ```
> * **Sebelum Index:** Kolom `type = ALL` dan `rows = 125` (database melakukan pemindaian menyeluruh ke seluruh baris tabel / *full table scan*).
> * **Sesudah Index:** Kolom `type = ref`, `key = idx_submissions_user_id`, dan `rows = 4` (database langsung melompat ke baris yang cocok menggunakan pohon B-Tree index).

### 5. Sebutkan satu data di aplikasi Anda yang tidak boleh di-cache. Kenapa?
> **Jawaban:**  
> **Nilai Tugas & Umpan Balik Dosen** serta **Status Keterlambatan Pengumpulan Berkas (`is_late`)**.  
> Nilai dan feedback menyangkut transparansi evaluasi akademik mahasiswa. Jika nilai di-cache selama 10 menit, mahasiswa yang baru menerima nilai revisi dari dosen tidak akan melihat perubahan, memicu kepanikan dan protes. Jika status pengumpulan di-cache, mahasiswa yang mengumpulkan tugas tepat waktu dapat salah dianggap terlambat akibat membaca *timestamp* cache yang basi.

### 6. Apa yang terjadi pada `env()` setelah `config:cache`? Kenapa ini penting minggu depan?
> **Jawaban:**  
> Ketika perintah `php artisan config:cache` dieksekusi, Laravel menggabungkan seluruh konfigurasi aplikasi menjadi satu berkas cache terenkapsulasi di `bootstrap/cache/config.php` dan **berhenti membaca berkas `.env`**. Akibatnya, pemanggilan fungsi `env()` di luar folder `config/` akan selalu mengembalikan nilai **`null`**.  
> Ini sangat penting untuk persiapan deployment Minggu 12: jika ada kode di Controller atau Blade yang memanggil `env('APP_NAME')`, aplikasi akan mengalami error fatal 500 di lingkungan produksi. Di KampusLMS, kami memastikan 100% kode menggunakan fungsi pembantu `config('app.name')`.

