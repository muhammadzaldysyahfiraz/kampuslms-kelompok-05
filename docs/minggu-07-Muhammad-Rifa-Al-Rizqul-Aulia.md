# Catatan Praktikum Minggu 7 Pemrograman Web

---

**Mata Kuliah:** SI2514024 — Pemrograman Web (Semester Ganjil 2026/2027)  
**Program Studi:** Sistem Informasi — Institut Teknologi Kalimantan (ITK)  
**Nama Mahasiswa:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05  
**Peran:** Frontend Developer & UI Designer  
**Dosen Pengampu:** Pak Aidil Saputra Kirsan  
**Asisten Dosen:** Kak Achmad Zaki Zaidan  

---

# I. READ: Arsitektur Autentikasi Sesi, Keamanan Cookie, dan Otorisasi Berbasis Policy di Laravel 12

Pada Minggu ke-7 ini, fokus utama pengembangan KampusLMS adalah penguatan keamanan sistem melalui **Autentikasi Sesi Stateful**, **Manajemen Cookie & Proteksi Sesi**, serta **Otorisasi Berbasis Kebijakan (*Policy-Based Authorization*)**. Sebagai Frontend Developer dan UI Designer, pemahaman mendalam mengenai batasan antara proteksi visual antarmuka (`@can`) dan validasi keamanan sisi server (`Gate::authorize()`) menjadi fondasi krusial agar aplikasi tidak hanya ramah pengguna (*user-friendly*), tetapi juga tangguh terhadap berbagai vektor serangan seperti *IDOR*, *Session Fixation*, dan *Privilege Escalation*.

---

### 1. Perbedaan Mendasar Autentikasi vs Otorisasi

| Aspek | Autentikasi (*Authentication*) | Otorisasi (*Authorization*) |
|---|---|---|
| **Definisi Inti** | Proses membuktikan identitas pengguna (*"Siapa Anda?"*). | Proses menentukan hak akses pengguna terhadap entitas/sumber daya tertentu (*"Bolehkah Anda melakukan aksi X pada data Y?"*). |
| **Mekanisme di Laravel** | `Auth::attempt()`, middleware `auth`, cookie session `laravel_session`, token Sanctum. | Laravel Policy (`CoursePolicy`, `AssignmentPolicy`), `Gate::authorize()`, direktif Blade `@can`. |
| **Titik Eksekusi** | Dijalankan di awal daur hidup request (login controller atau middleware `auth`). | Dijalankan pada controller method sebelum operasi data, atau di query level. |
| **Kode Respon HTTP Jika Gagal** | `401 Unauthorized` (pada API) atau redirect `302` ke halaman login (pada Web). | `403 Forbidden`. |
| **Contoh Kasus KampusLMS** | Dosen Pak Aidil berhasil login dengan email dan password yang sah $\rightarrow$ **Terautentikasi**. | Dosen Pak Aidil mencoba mengedit mata kuliah milik Dosen Pak Ari $\rightarrow$ **Ditolak Otorisasi (403 Forbidden)**. |

---

### 2. Alur Login, Proteksi Sesi, dan Penanganan Logout

Pada KampusLMS, alur autentikasi web dibangun menggunakan sistem sesi bawaan Laravel 12 yang diimplementasikan di `app/Http/Controllers/AuthController.php`:

#### A. Alur Login & Pencegahan *User Enumeration*
Formulir login mengirimkan request `POST /login` yang diproteksi token CSRF (`@csrf`) dan rate limiter (`throttle:5,1`). Controller memvalidasi kredensial pengguna:

```php
if (! Auth::attempt($credentials, $remember)) {
    throw ValidationException::withMessages([
        'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
    ]);
}
```

> **Catatan Keamanan (Anti-User Enumeration):** Pesan kesalahan sengaja dibuat seragam (*"Email atau kata sandi yang Anda masukkan salah"*). Sistem tidak membedakan apakah email yang tidak ditemukan atau password yang salah, sehingga penyerang tidak dapat menebak daftar email valid yang terdaftar pada sistem.

#### B. Regenerasi Sesi (`session()->regenerate()`)
Tepat setelah `Auth::attempt()` berhasil, dijalankan:
```php
$request->session()->regenerate();
```
Pemanggilan ini bertujuan mengganti ID sesi lama dengan ID sesi baru yang diacak oleh server. Mekanisme ini merupakan pertahanan utama terhadap serangan **Session Fixation**, di mana penyerang menetapkan ID sesi tertentu pada browser korban sebelum login agar penyerang dapat membajak sesi setelah korban memasukkan kredensialnya.

#### C. Penanganan Logout Sempurna
Proses keluar dari sistem di `AuthController::logout()` menjalankan 3 langkah wajib:
```php
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```
1. `Auth::logout()`: Menghapus status pengguna yang terotentikasi dari guard.
2. `$request->session()->invalidate()`: Mengosongkan seluruh data session di server dan memusnahkan ID sesi saat ini.
3. `$request->session()->regenerateToken()`: Mengganti token CSRF untuk mencegah eksploitasi form lama.

#### D. Analisis Tombol *Back* pada Browser Setelah Logout
Setelah logout, jika pengguna menekan tombol *Back* pada peramban, halaman dasbor mungkin tampak muncul kembali. Ini terjadi karena mekanisme *Browser Cache* (peramban memuat salinan HTML statis dari memori lokal tanpa melakukan request baru ke server). Namun, status otentikasi di server telah sepenuhnya hangus. Begitu pengguna melakukan klik tautan, memuat ulang halaman (*F5*), atau mengirimkan formulir, server langsung menolak dan mengarahkan pengguna kembali ke halaman `/login`.

---

### 3. Kriptografi Hashing Kata Sandi

Di model `app/Models/User.php`, atribut kata sandi dilindungi menggunakan cast bawaan:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

* **Sifat Hashing (Satu Arah):** Hashing menggunakan algoritma **Bcrypt (cost 12)** adalah fungsi matematis satu arah (*one-way cryptographic hash*). Nilai hash seperti `$2y$12$dlPOcf1...` secara matematis tidak dapat didekripsi kembali menjadi teks kata sandi asli.
* **Verifikasi Saat Login:** Saat login, `Auth::attempt()` mencocokkan input kata sandi dengan hash di database menggunakan `Hash::check()`.
* **Alur Lupa Kata Sandi:** Karena password asli tidak dapat dipulihkan, alur pemulihan kata sandi dilakukan dengan mengirimkan **Token Acak Bertenggat Waktu** ke email pengguna (`/forgot-password`), lalu pengguna membuat kata sandi baru melalui formulir `/reset-password/{token}`.

---

### 4. Keamanan Cookie Sesi Browser

Cookie sesi `laravel_session` bertindak sebagai token identitas penghubung antara peramban klien dan rekaman sesi di server. Diatur dalam `config/session.php`:
* **`HttpOnly = true`:** Melarang JavaScript (seperti `document.cookie`) membaca nilai cookie sesi. Hal ini memperkecil dampak pencurian sesi jika website terkena celah XSS (*Cross-Site Scripting*).
* **`Secure = true` (Lingkungan Produksi):** Memastikan cookie hanya dikirimkan melalui saluran komunikasi terenkripsi HTTPS.
* **`SameSite = lax / strict`:** Membatasi pengiriman cookie pada request lintas situs (*cross-site*), memperkuat pertahanan terhadap CSRF.

---

### 5. Otorisasi Berbasis Policy & `Gate::authorize()`

Untuk mengelola aturan akses pada 5 model inti KampusLMS (`Course`, `Assignment`, `Material`, `Submission`, `Grade`), Laravel menyediakan kelas Policy:

```bash
php artisan make:policy CoursePolicy --model=Course
php artisan make:policy AssignmentPolicy --model=Assignment
php artisan make:policy MaterialPolicy --model=Material
php artisan make:policy SubmissionPolicy --model=Submission
php artisan make:policy GradePolicy --model=Grade
```

Di dalam kontroler, otorisasi diperiksa secara eksplisit sebelum aksi dijalankan:
```php
Gate::authorize('update', $course);
```
Jika pengguna tidak memiliki izin sesuai logika Policy, Laravel secara otomatis melempar exception `AuthorizationException` yang menghasilkan respon **HTTP 403 Forbidden**.

---

### 6. Bahaya Mengandalkan Direktif `@can` Saja di Blade (*Front-end Obscurity*)

Dalam arsitektur frontend web, direktif Blade `@can` sering digunakan untuk menampilkan tombol aksi secara kondisional:

```blade
@can('update', $course)
    <a href="{{ route('courses.edit', $course) }}" class="btn btn-warning">Edit Mata Kuliah</a>
@endcan
```

> **PRINSIP KEAMANAN KRUSIAL:**  
> **Direktif `@can` di Blade hanyalah elemen kenyamanan antarmuka (*UI / UX enhancement*), BUKAN mekanisme pengamanan aplikasi.**  
> Jika tombol "Edit" disembunyikan menggunakan `@can`, namun di `CourseController::edit()` atau `update()` tidak dipasang `Gate::authorize('update', $course)` atau policy backend, maka pengguna yang nakal dapat langsung mengetikkan URL `/courses/1/edit` di *address bar* atau mengirim payload HTTP `PUT /courses/1` via Postman/cURL. Tanpa validasi server-side, sistem akan tetap memproses pembaruan data tersebut!

---

### 7. Penyaringan Kueri di Lapisan Basis Data (*Query-Level Scoping*)

Keamanan dan efisiensi data menuntut penyaringan data dilakukan sejak kueri SQL di basis data, bukan dimuat seluruhnya lalu disembunyikan di Blade:

```php
// CONTOH KELIRU: Mengambil semua data lalu menyaring di Blade
$courses = Course::all(); // Mahasiswa A bisa membedah response JSON atau memori

// CONTOH BENAR: Menyaring di level Query Database
$user = request()->user();
$courses = match ($user->role) {
    'admin'     => Course::query(),
    'dosen'     => $user->taughtCourses(),
    'mahasiswa' => $user->courses(),
    default     => abort(403),
}->with('lecturer')->paginate(12);
```

Dengan pendekatan ini, data privat milik pengguna lain tidak pernah terkirim melintasi jaringan dan tidak membebani memori server.

---

# II. BREAK: Delapan Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Pada bagian ini, dilakukan 8 eksperimen pengujian kegagalan terencana untuk membuktikan kerentanan dan memverifikasi ketahanan sistem keamanan KampusLMS:

| # | Yang Diuji / Skenario yang Dirusak | Prediksi Sebelum Menguji | Hasil & Konsekuensi Sebenarnya |
|:--:|:---|:---|:---|
| **1** | Menghapus pemanggilan `session()->regenerate()` pada method `AuthController::login`. | ID sesi sebelum login dan sesudah login akan tetap identik, membuka celah Session Fixation. | Nilai cookie `laravel_session` tidak berubah antara sebelum dan sesudah otentikasi. Penyerang yang telah menanamkan ID sesi pada peramban korban dapat langsung mengambil alih sesi korban tanpa login ulang. |
| **2** | Menyembunyikan tombol Edit menggunakan `@can('update', $course)` di Blade, tetapi menghapus `Gate::authorize()` di `CourseController::update`. | Tampilan tombol di browser hilang, namun penyerang tetap dapat mengirim request langsung via cURL/Postman. | **Celah Otorisasi Terbuka Lebar:** Dosen lain yang membuka form edit via address bar `/dosen/courses/1/edit` berhasil mengubah data mata kuliah milik dosen rekanan. Membuktikan proteksi visual Blade tidak mengamankan endpoint backend. |
| **3** | Dosen A mengirim request HTTP `PUT` ke mata kuliah milik Dosen B yang tidak diampunya. | Policy `CoursePolicy::update` akan mendeteksi ketidaksesuaian `lecturer_id` dan memblokir request. | Request ditolak dengan kode status **`403 Forbidden`**. Logika Policy berhasil memverifikasi kepemilikan objek dan menghentikan manipulasi data lintas pengajar (*Cross-Lecturer tamper*). |
| **4** | Mahasiswa A mencoba mengakses rute `/submissions/1` milik Mahasiswa B secara langsung melalui URL. | Sistem akan melempar error `403 Forbidden` karena Mahasiswa A bukan pemilik tugas dan bukan dosen pengampu. | **Mitigasi IDOR Terbukti Efektif:** Server merespons dengan **`403 Forbidden`** via `resources/views/errors/403.blade.php`. Berkas jawaban dan nilai tugas mahasiswa lain terlindungi. |
| **5** | Menjalankan `Course::paginate(15)` tanpa scoping peran pada halaman daftar tugas/submission. | Seluruh data tugas dan pengumpulan dari seluruh mahasiswa di kampus akan tercampur baur pada satu tabel. | Terjadi kebocoran data akademik masif (*Data Leakage*). Mahasiswa dapat melihat tugas dan nilai mahasiswa lain di kelas yang berbeda. Scoping query wajib dipasang di kontroler. |
| **6** | Mengirimkan parameter `role=admin` melalui formulir pembaruan profil pengguna publik. | Field `role` akan diabaikan atau ditolak karena tidak masuk dalam daftar atribut yang divalidasi. | Sistem hanya memproses atribut yang lolos validasi Form Request (`name`, `email`). Atribut `role` diabaikan, mencegah terjadinya eskalasi hak akses (*Privilege Escalation*). |
| **7** | Menghapus cast `'password' => 'hashed'` pada model `User`, lalu membuat user baru. | Kata sandi yang dimasukkan akan disimpan ke dalam database dalam bentuk teks polos (*plain text*). | Kata sandi tersimpan sebagai teks biasa tanpa algoritma Bcrypt. Jika basis data bocor, seluruh kredensial pengguna langsung terekspos. Cast `hashed` mutlak dipertahankan. |
| **8** | Menyalin nilai cookie sesi aktif dari satu browser ke browser lain (*Incognito* / mesin berbeda). | Browser kedua dapat mengakses dasbor tanpa memasukkan kata sandi selama sesi server belum di-invalidate. | **Perilaku Nyata Session Hijacking:** Browser kedua langsung masuk sebagai pengguna bersangkutan. Begitu pengguna asli melakukan `logout`, `$request->session()->invalidate()` memusnahkan sesi di server, dan cookie curian tersebut seketika menjadi tidak berlaku (**401 / Redirect Login**). |

---

# III. FIX: Analisis dan Penanganan Kerusakan (*Code Hardening*)

Berdasarkan hasil pengujian terencana pada tahap *BREAK*, dilakukan standarisasi dan penguatan kode (*hardening*) pada KampusLMS:

### 1. Menjaga Konsistensi Otorisasi Sisi Server & Sisi Klien
Setiap rute manipulasi data dipastikan memiliki pasangan proteksi ganda:
* **Sisi Klien (Blade):** Menggunakan `@can('update', $model)` untuk menyembunyikan tombol aksi dari pengguna yang tidak berhak, demi kerapian antarmuka.
* **Sisi Server (Controller):** Memanggil `Gate::authorize('update', $model)` sebelum mengeksekusi operasi basis data.

### 2. Perbaikan Bug Argumen pada `SubmissionPolicy::create`
Saat pengujian form pengumpulan tugas dari sisi mahasiswa, sempat terdeteksi bug `ArgumentCountError` di mana method `create(User $user)` di `SubmissionPolicy` memerlukan parameter tugas:
```php
// SEBELUM:
public function create(User $user, Assignment $assignment): bool

// SESUDAH (Mendukung pemanggilan Gate::authorize('create', [Submission::class, $assignment])):
public function create(User $user, ?Assignment $assignment = null): bool
{
    if ($user->role !== 'mahasiswa') {
        return false;
    }
    
    if ($assignment) {
        return $assignment->course->students()->where('users.id', $user->id)->exists();
    }
    
    return true;
}
```

### 3. Perlindungan Scoped Route Model Binding
Mencegah serangan *Cross-Course IDOR* pada relasi bersarang materi dan tugas dengan mengaktifkan `Route::scopeBindings()` pada `routes/web.php` dan `routes/api.php`:
```php
Route::scopeBindings()->group(function () {
    Route::resource('courses.materials', MaterialController::class)->shallow();
    Route::resource('courses.assignments', AssignmentController::class)->shallow();
});
```
Dengan konfigurasi ini, URL `/courses/1/assignments/99` otomatis merespons **`404 Not Found`** jika Assignment 99 bukan anak dari Course 1.

---

# IV. BUILD: Implementasi Frontend, Dashboard Berorientasi Peran, & Fitur Otorisasi

Sebagai Frontend Developer dan UI Designer, berikut adalah rincian komponen antarmuka dan integrasi logika yang dibangun pada Milestone Minggu ke-7:

### 1. Dashboard Berorientasi Peran (*Role-Adaptive Dashboard*)
Antarmuka dasbor utama (`resources/views/dashboard.blade.php`) dirancang adaptif berdasarkan peran aktif pengguna:

* **Tampilan Mahasiswa:**
  * **Ringkasan Akademik:** Menampilkan jumlah mata kuliah terdaftar, tugas aktif, dan tugas selesai.
  * **Tugas Mendatang:** Menampilkan batas waktu pengumpulan tugas dengan indikator visual tenggat waktu (hijau untuk aman, kuning untuk mendekati batas, merah untuk terlambat).
  * **Penghapusan Komponen Redundan:** Menghilangkan widget "Peringkat Mahasiswa" yang tidak relevan secara pedagogis, serta menyatukan widget "Jadwal Mendatang" dan "Log Penugasan" menjadi satu antarmuka terpadu yang efisien.
* **Tampilan Dosen:**
  * **Mata Kuliah Diampu:** Kartu ringkasan kelas dengan jumlah mahasiswa terdaftar dan tombol cepat kelola materi/tugas.
  * **Antrean Penilaian (*Pending Grading*):** Menampilkan tugas mahasiswa yang baru dikumpulkan dan membutuhkan evaluasi serta skor nilai.
* **Tampilan Admin:**
  * **Statistik Sistem:** Ringkasan total pengguna (Admin, Dosen, Mahasiswa) dan mata kuliah aktif.
  * **Aksi Cepat:** Tautan langsung ke manajemen akun pengguna dan katalog mata kuliah.

---

### 2. Formulir Penilaian Dosen & Evaluasi Tugas Mahasiswa
Menyediakan antarmuka penilaian langsung pada halaman detail pengumpulan tugas (`resources/views/submissions/show.blade.php`):
* Menampilkan informasi nama mahasiswa, waktu pengumpulan, dan berkas/teks jawaban tugas.
* Form penilaian interaktif terlindungi `@can('grade', $submission)`:
  * Input nilai numerik skala 0 hingga 100 dengan validasi real-time.
  * Area teks umpan balik (*Feedback*) dosen untuk memberikan catatan konstruktif kepada mahasiswa.
  * Tombol simpan nilai yang memicu pembaruan status submission menjadi `graded`.

---

### 3. Integrasi Formulir Reset Kata Sandi di Antarmuka
Menyediakan halaman pemulihan akun yang intuitif dan aman:
* Formulir permohonan tautan reset: `resources/views/auth/forgot-password.blade.php` dengan pesan konfirmasi pengiriman token.
* Formulir penetapan sandi baru: `resources/views/auth/reset-password.blade.php` dengan validasi kecocokan sandi (*password confirmation*) dan token terverifikasi.

---

### 4. Eliminasi Dummy Data & Integrasi Materi Perkuliahan Nyata
Menghapus seluruh teks *Lorem Ipsum* pada antarmuka aplikasi dan menggantinya dengan konten materi perkuliahan nyata pada kurikulum Pemrograman Web ITK:
* Materi Arsitektur MVC & Konsep Laravel 12.
* Materi Desain Antarmuka Blade & Desain Komponen Responsif.
* Materi Routing, Middleware, dan Keamanan Sesi Web.
* Materi RESTful API & Otorisasi Sanctum.

---

### 5. Verifikasi Pengujian Otomatis & Skrip Otorisasi
Integritas otorisasi diuji secara berkala untuk memastikan tidak ada celah keamanan atau regresi:
1. **PHPUnit / Pest Test Suite:** Menjalankan `php artisan test` dengan **35 tests dan 156 assertions** yang lulus 100%.
2. **Skrip Uji Otorisasi Mandiri (`scripts/test-authz.sh`):** Skrip otomasi berbasis cURL yang memverifikasi 8 skenario titik rawan IDOR dan hak akses lintas peran (Mahasiswa, Dosen, dan Admin) dengan hasil seluruh pengujian berstatus `PASS`.

---

# V. Ringkasan & Refleksi Pengembangan

Pengerjaan Milestone Minggu ke-7 ini memberikan pemahaman mendalam bahwa keindahan antarmuka pengguna (*UI/UX*) harus selalu berjalan selaras dengan keamanan data (*security-first design*). Menyembunyikan tombol menggunakan direktif Blade `@can` adalah praktik baik untuk kenyamanan visual, namun pintu utama keamanan tetap berada pada kebijakan otorisasi sisi server (*Policy & Gate*). Melalui kombinasi dashboard adaptif peran, proteksi IDOR berlapis, dan eliminasi data dummy, KampusLMS siap menghadapi evaluasi Ujian Tengah Semester (UTS) dengan standar kualitas kode yang kokoh.
