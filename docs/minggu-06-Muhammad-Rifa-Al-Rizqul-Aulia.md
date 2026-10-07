# Catatan Praktikum Minggu 6 Pemrograman Web

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

# I. READ: Analisis Arsitektur REST API & Sanctum di Laravel 12

Pada Minggu ke-6 ini, fokus pembelajaran adalah peralihan dari arsitektur monolitik Blade murni menuju pemisahan lapisan logika (*backend*) dan penyajian (*presentation*) melalui **REST API (*Representational State Transfer Application Programming Interface*)**. Hal ini menjadi fondasi krusial bagi KampusLMS karena satu backend yang sama nantinya dapat melayani berbagai jenis klien (aplikasi web Blade, mobile, SPA, maupun sistem akademik eksternal kampus).

---

### 1. Perbedaan Mendasar Web Monolitik vs REST API

| Aspek | Jalur Web (`routes/web.php`) | Jalur REST API (`routes/api.php`) |
|---|---|---|
| **Mekanisme Identitas** | Stateful berbasis Session & Cookie | Stateless berbasis Bearer Token ([Sanctum](https://laravel.com/docs/12.x/sanctum)) |
| **Proteksi CSRF** | Wajib via token CSRF (`@csrf`) | Tidak diperlukan (karena tidak menggunakan Cookie browser) |
| **Respon Gagal Autentikasi** | Redirect HTTP 302 ke formulir login | JSON HTTP 401 Unauthorized (`{"message": "Unauthenticated."}`) |
| **Format Muatan Balik** | Dokumen HTML ter-render | Payload terstruktur JSON (*JavaScript Object Notation*) |
| **Sifat Komunikasi** | Stateful (server mengingat session) | Stateless (setiap request membawa tokennya sendiri) |

---

### 2. Aktivasi `routes/api.php` di Laravel 12

Berbeda dengan Laravel 10 ke bawah di mana `routes/api.php` otomatis tersedia sejak instalasi proyek, pada Laravel 12 berkas ini **tidak dibuat secara default**. Desain minimalis Laravel 12 memangkas berkas yang tidak digunakan secara default. Untuk mengaktifkannya, dijalankan perintah resmi:

```bash
php artisan install:api
```

Perintah ini secara otomatis:
1. Memasang paket dependensi `laravel/sanctum`.
2. Menerbitkan berkas `routes/api.php`.
3. Menambahkan migrasi tabel `personal_access_tokens`.
4. Mengonfigurasi `bootstrap/app.php` dengan routing `api: __DIR__.'/../routes/api.php'`.

---

### 3. API Resource: Kebijakan Zero-Model Leakage

Mengembalikan model Eloquent mentah secara langsung (`return response()->json(User::all())`) adalah salah satu sumber kebocoran data (*data leakage*) paling fatal dalam pengembangan web. Kolom privat seperti `password` hash, `remember_token`, atau email mahasiswa lain akan langsung terpapar ke publik.

Melalui **API Resource** (`JsonResource`), sistem menerapkan konsep *whitelist*: hanya field yang secara eksplisit didefinisikan yang akan dikirimkan kepada klien. Selain itu, pemanggilan kondisional `whenLoaded()` memastikan bahwa data relasi hanya disertakan bila telah di-*eager load*, mencegah timbulnya query N+1 yang merusak performa server.

---

### 4. Perbandingan Kontroler: Web vs API (`CourseController`)

* **`CourseController` Versi Web:** Mengambil data dan mengembalikan tampilan Blade (`return view('courses.index', compact('courses'))`), menangani redirect session dengan pesan flash, dan mengandalkan autentikasi session browser.
* **`CourseController` Versi API:** Mengembalikan transformasi `CourseResource::collection($courses)` lengkap dengan struktur `meta` paginasi JSON standar, memeriksa otorisasi kepemilikan objek secara ketat dengan status code HTTP (`401` vs `403`), dan sepenuhnya stateless.

---

### 5. Pengaruh Header `Accept: application/json`

Ketika klien mengirimkan request ke rute API tanpa menyertakan header `Accept: application/json`, Laravel menganggap klien adalah peramban web biasa sehingga bila terjadi kegagalan validasi atau autentikasi, server akan mengirimkan respon redirect HTML (302) ke halaman login web. Dengan menyertakan:
```http
Accept: application/json
```
Laravel dipaksa selalu merespons dalam format payload JSON standar dengan kode status yang tepat (`401 Unauthorized` atau `422 Unprocessable Content`).

---

# II. BREAK: Tujuh Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Eksperimen berikut dilakukan untuk mengamati secara langsung bahaya keamanan dan kegagalan fungsional pada implementasi API sebelum diproteksi:

| # | Skenario Kerusakan Terencana | Prediksi Sebelum Pengujian | Observasi & Bukti Nyata |
|:--:|---|---|---|
| **1** | Mengembalikan `response()->json(User::all())` langsung dari controller tanpa Resource. | Seluruh kolom tabel `users` akan terkonversi ke JSON dan terkirim ke klien. | **Kebocoran Data Fatal:** Kolom hash Bcrypt kata sandi (`$2y$12$...`), `remember_token`, dan data privat pengguna lain terpapar rapi di layar klien. |
| **2** | Menghapus middleware `auth:sanctum` dari grup rute `/api/v1/courses`. | Endpoint dapat dipanggil oleh siapa saja tanpa autentikasi. | **Akses Terbuka Publik:** Pengguna anonim tanpa token dapat membaca seluruh daftar mata kuliah dan data akademik kampus. |
| **3** | Mengirim permintaan ke endpoint terlindungi menggunakan token yang telah dihapus / dicabut (*revoked*). | Server mendeteksi token tidak ada di tabel `personal_access_tokens` dan menolak permintaan. | Server merespons dengan **`401 Unauthorized`** (`{"message": "Unauthenticated."}`). |
| **4** | Login sebagai Mahasiswa, lalu mengirim permintaan `POST /api/v1/assignments`. | Middleware peran atau controller memblokir akses mahasiswa dan mengembalikan 403. | **Pembedaan 401 vs 403:** Mahasiswa sudah terautentikasi (bukan 401), tetapi tidak berhak membuat tugas sehingga ditolak dengan **`403 Forbidden`** (`"Anda tidak memiliki akses ke sumber daya ini."`). |
| **5** | Menghapus eager loading `with('lecturer')` pada pemanggilan koleksi mata kuliah. | Terjadi query N+1 pada database untuk memuat dosen setiap baris data mata kuliah. | Database mengeksekusi 1 query daftar course ditambah 15 query tambahan individual untuk masing-masing lecturer (terbukti via profil kueri). |
| **6** | Menghapus middleware `throttle:5,1` dari endpoint login. | Penyerang dapat melakukan tebakan kata sandi secara berulang tanpa pembatasan. | Percobaan login dapat dijalankan ratusan kali per detik tanpa hambatan, membuka celah *brute-force attack*. |
| **7** | Mengembalikan pesan login yang membedakan "Email tidak terdaftar" dan "Password salah". | Penyerang dapat memanfaatkan pesan tersebut untuk mengidentifikasi keberadaan akun. | **User Enumeration:** Penyerang dapat memetakan email dosen dan mahasiswa yang terdaftar di kampus hanya dengan mengamati respons error login. |

---

# III. FIX: Analisis dan Penanganan Kerusakan

### 1. Eliminasi Kerentanan & Penyempurnaan REST API (8 Masalah Repo Cacat)

Berdasarkan temuan audit dan skenario *BREAK*:
1. **Zero Model Leakage:** Seluruh keluaran model dibungkus menggunakan 7 kelas `JsonResource` (`UserResource`, `CourseResource`, `MaterialResource`, `AssignmentResource`, `SubmissionResource`, `GradeResource`, `NotificationResource`).
2. **Perlindungan Token Penuh:** Seluruh rute internal dibungkus dalam grup middleware `auth:sanctum`.
3. **Standarisasi Kode Status HTTP:**
   - Pembuatan data baru mengembalikan kode **`201 Created`** (tugas, pengumpulan tugas, dan pembuatan nilai pertama).
   - Penghapusan data mengembalikan kode **`204 No Content`** (tanpa muatan bodi).
   - Konflik pengumpulan tugas ganda mengembalikan kode **`409 Conflict`**.
4. **Pembedaan Tegas 401 vs 403:** Tamu tanpa token ditolak dengan `401 Unauthorized`, sedangkan pengguna dengan peran yang salah ditolak dengan `403 Forbidden`.
5. **Anti-Brute Force:** Endpoint login dibatasi ketat `throttle:5,1` (maksimal 5 percobaan per menit per alamat IP).
6. **Anti-User Enumeration:** Pesan kegagalan login diseragamkan: `"Email atau kata sandi salah."` baik untuk kasus email tidak terdaftar maupun password salah.
7. **Pemberantasan N+1 Query:** Eager loading `with(...)` dan `withCount(...)` diimplementasikan pada seluruh pemanggilan koleksi relasi.

---

### 2. Investigasi & Solusi Masalah "Frontend Web Rusak" (Laporan Zaldy)

Saat anggota tim (Zaldy) melakukan merge konfigurasi API ke branch utama (`main`), terjadi kendala di mana halaman web frontend KampusLMS mengalami crash fatal saat dibuka:

* **Gejala:** Membuka dashboard atau menu navigasi web menghasilkan error HTTP 500:
  ```text
  Target class [role] does not exist.
  ```
* **Akar Penyebab (*Root Cause*):**  
  Pada saat Zaldy menambahkan konfigurasi rute API di `bootstrap/app.php`, callback konfigurasi middleware `$middleware->alias(...)` secara tidak sengaja terhapus/dikosongkan. Akibatnya, seluruh rute web yang dilindungi middleware `middleware('role:admin')`, `role:dosen`, maupun `role:mahasiswa` gagal mengenali kelas middleware `EnsureUserHasRole`.
* **Solusi Perbaikan:**  
  Mendaftarkan kembali alias middleware peran di `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'role' => \App\Http\Middleware\EnsureUserHasRole::class,
      ]);
  })
  ```
  Setelah alias didaftarkan kembali, seluruh halaman web frontend, navigasi peran, serta pengujian hak akses web kembali berfungsi 100% normal tanpa merusak UI yang sudah dibangun.

---

# IV. BUILD: Implementasi REST API KampusLMS Sesuai Kontrak Bagian 5

### 1. Struktur Rute di `routes/api.php`
Seluruh 14 endpoint REST API KampusLMS telah diimplementasikan di bawah prefix `/api/v1`:

```php
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::get('/courses/{course}/materials', [MaterialController::class, 'index']);
        Route::get('/courses/{course}/assignments', [AssignmentController::class, 'index']);

        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::match(['put', 'patch'], '/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);

        Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'index']);
        Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'store']);
        Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'grade']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
    });
});
```

---

### 2. Logika Upsert Penilaian Tugas (`PUT /submissions/{id}/grade`)
Sesuai kontrak Bagian 5, endpoint penilaian mendukung operasi idempotent / *upsert* aman:
```php
public function grade(GradeSubmissionRequest $request, Submission $submission)
{
    $grade = $submission->grade ?? new Grade();
    $grade->fill($request->safe()->only(['score', 'feedback']));
    $grade->submission_id = $submission->id;
    $grade->graded_by     = $request->user()->id;
    $grade->graded_at     = now();
    $grade->save();

    // 201 saat nilai baru pertama kali dibuat, 200 saat diperbarui
    return (new GradeResource($grade->load('grader')))
        ->response()
        ->setStatusCode($grade->wasRecentlyCreated ? 201 : 200);
}
```

---

### 3. Bukti Eksekusi Skrip Uji cURL (`scripts/test-api.sh`)

Skrip pengujian otomatis dijalankan pada server lokal KampusLMS:
```bash
bash scripts/test-api.sh
```

**Hasil Keluaran Terminal:**
```text
Memulai Pengujian REST API KampusLMS pada: http://127.0.0.1:8000

==============================================================================
  TAHAP 0: Autentikasi Pengguna & Pembuatan Sanctum Token
==============================================================================
  [PASS] Login Dosen Demo (dosen@kampuslms.test) (HTTP 200)
  [PASS] Login Mahasiswa Demo (mahasiswa@kampuslms.test) (HTTP 200)

==============================================================================
  TAHAP 1: Uji Akses Tanpa Token (Harus 401 Unauthorized)
==============================================================================
  [PASS] GET /me tanpa token (HTTP 401)
  [PASS] GET /courses tanpa token (HTTP 401)
  [PASS] POST /assignments tanpa token (HTTP 401)
  [PASS] GET /notifications tanpa token (HTTP 401)

==============================================================================
  TAHAP 2: Token Mahasiswa Mengakses Hak Akses Dosen (Harus 403 Forbidden)
==============================================================================
  [PASS] Mahasiswa mencoba POST /assignments (Course ID 22) (HTTP 403)
  [PASS] Mahasiswa mencoba PUT /assignments/20 (HTTP 403)
  [PASS] Mahasiswa mencoba DELETE /assignments/20 (HTTP 403)
  [PASS] Mahasiswa mencoba intip submissions (GET /assignments/20/submissions) (HTTP 403)
  [PASS] Mahasiswa mencoba memberi nilai tugas (PUT /submissions/106/grade) (HTTP 403)

==============================================================================
  TAHAP 3: Uji Kerentanan IDOR (Insecure Direct Object Reference) (Harus 403 Forbidden)
==============================================================================
  [PASS] Dosen memanipulasi course_id asing pada POST /assignments (HTTP 422)
  [PASS] Mahasiswa membuka course yang tidak diikutinya (HTTP 404)

==============================================================================
  TAHAP 4: Uji Jalur Sukses (Happy Path) Pengguna Terotorisasi
==============================================================================
  [PASS] GET /me (Dosen) (HTTP 200)
  [PASS] GET /courses (Dosen) (HTTP 200)
  [PASS] GET /courses/22 (Detail MK Dosen) (HTTP 200)
  [PASS] POST /assignments (Dosen membuat tugas) (HTTP 201)
  [PASS] PUT /assignments/25 (Dosen mengupdate tugas) (HTTP 200)
  [PASS] GET /assignments/25/submissions (HTTP 200)
  [PASS] DELETE /assignments/25 (Dosen menghapus tugas) (HTTP 204)
  [PASS] GET /notifications (Dosen) (HTTP 200)
  [PASS] POST /auth/logout (Revoke token) (HTTP 204)

==============================================================================
  RINGKASAN HASIL PENGUJIAN API
==============================================================================
Total Pengujian Berhasil : 22
Total Pengujian Gagal    : 0

✔ SEMUA PENGUJIAN OTORISASI & ENDPOINT REST API PEKAN 06 LOLOS DENGAN SEMPURNA!
```

---

### 4. Bukti Eksekusi Pengujian Otomatis Fitur Laravel (`tests/Feature/WeekSixApiTest.php`)

```bash
php artisan test
```

**Hasil Keluaran Pengujian:**
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response

   PASS  Tests\Feature\WeekFiveAccessTest
  ✓ guest gets 401 for protected course routes
  ✓ student cannot open course management form
  ✓ lecturer cannot edit another lecturers course
  ✓ lecturer can edit own course
  ✓ lecturer cannot assign new course to another lecturer
  ✓ student can view own submission but not another students submission
  ✓ course lecturer and admin can view submission
  ✓ nested assignment binding rejects assignment from another course
  ✓ only admin can manage users

   PASS  Tests\Feature\WeekSixApiTest
  ✓ unauthenticated request returns 401
  ✓ login successful and returns token without leaking sensitive data
  ✓ login prevents user enumeration
  ✓ me endpoint returns authenticated user resource
  ✓ logout revokes current token
  ✓ courses index scoped by user role
  ✓ courses show accessible and forbidden for non enrolled student
  ✓ student cannot create assignment
  ✓ lecturer cannot create assignment for another lecturers course
  ✓ lecturer can create update and delete own assignment
  ✓ other lecturer cannot update or delete assignment
  ✓ student can submit assignment and cannot submit twice
  ✓ lecturer can grade submission with upsert status codes
  ✓ other lecturer cannot grade submission 403
  ✓ user can view and read own notifications

  Tests:    26 passed (118 assertions)
  Duration: 8.47s
```

---

# V. CHECKPOINT MINGGU 6 — PERSIAPAN INTERVIEW

---

### ❓ 1. Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.

* **Bahaya Kebocoran Model Mentah:**  
  Ketika sebuah controller mengeksekusi `return response()->json($user)` atau `return Course::all()`, Eloquent secara otomatis melakukan serialisasi terhadap seluruh kolom yang ada pada tabel basis data. Kolom rahasia seperti hash kata sandi (`password`), string token otentikasi (`remember_token`), serta data privat mahasiswa/dosen lainnya akan langsung terkirim dalam muatan JSON publik.
* **Peragaan Kebocoran Nyata:**
  ```json
  // Respon berbahaya dari return response()->json(User::all()):
  [
    {
      "id": 1,
      "name": "Admin KampusLMS",
      "email": "admin@kampuslms.test",
      "password": "$2y$12$eX.SampleHashBcryptPasswordHere...",
      "remember_token": "a1b2c3d4...",
      "created_at": "2026-09-13T07:00:00.000000Z"
    }
  ]
  ```
* **Solusi Perlindungan:**  
  Menggunakan **`UserResource`** sebagai daftar putih (*whitelist*), sehingga hanya atribut yang diizinkan (`id`, `name`, `email`, `nim_nip`, `role`) yang keluar ke respon klien.

---

### ❓ 2. Apa beda 401 dan 403? Tunjukkan di API Anda satu contoh masing-masing.

* **`401 Unauthorized` (Autentikasi Gagal):**  
  Menjawab pertanyaan: *"Siapa Anda?"*. Kode ini dikembalikan ketika klien belum menyertakan token atau token yang disertakan tidak valid/sudah dicabut.  
  *Contoh di KampusLMS:* Memanggil `GET /api/v1/courses` tanpa header `Authorization: Bearer <token>`.
* **`403 Forbidden` (Otorisasi Gagal):**  
  Menjawab pertanyaan: *"Apakah Anda berhak menyentuh data ini?"*. Klien telah berhasil login dan identitasnya valid, namun peran atau izin kepemilikannya tidak memenuhi syarat untuk melakukan aksi tersebut.  
  *Contoh di KampusLMS:* Mahasiswa yang telah login mencoba mengirim `POST /api/v1/assignments` untuk membuat tugas baru, atau Dosen A mencoba mengedit penugasan mata kuliah milik Dosen B.

---

### 3. Kenapa `routes/api.php` tidak ada secara default di Laravel 12? Bagaimana mengaktifkannya?

* **Alasan Desain di Laravel 12:**  
  Laravel 12 mengusung filosofi *streamlined framework*. Framework tidak lagi memuat berkas atau dependensi yang belum tentu dibutuhkan oleh aplikasi web monolitik sederhana. Jika pengembang hanya membangun aplikasi berbasis Blade murni, maka konfigurasi API tidak perlu membebani codebase.
* **Cara Mengaktifkannya:**  
  Dijalankan perintah artisan:
  ```bash
  php artisan install:api
  ```
  Perintah ini secara otomatis menginstalasi Sanctum, membuat berkas `routes/api.php`, membuat migrasi tabel token, dan mendaftarkan rute API di `bootstrap/app.php`.

---

### ❓ 4. Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?

* **Fungsi `whenLoaded()`:**  
  Method `whenLoaded('relasi')` pada API Resource berfungsi untuk menyertakan data relasi ke dalam respon JSON **hanya jika relasi tersebut telah dimuat sebelumnya via eager loading (`with(...)`)**.
* **Apa yang Terjadi Tanpa `whenLoaded()`:**  
  Jika kita menulis `'lecturer' => new UserResource($this->lecturer)`, maka setiap kali satu baris course diserialisasi, Eloquent akan memicu satu kueri SQL baru ke tabel `users` untuk mengambil data dosen. Jika ada 15 baris course pada halaman tersebut, sistem akan mengeksekusi 1 + 15 = 16 query SQL (**masalah N+1 Query**).  
  Dengan `whenLoaded()`, jika controller tidak melakukan eager load, field tersebut diabaikan secara elegan tanpa memicu kueri tambahan.

---

### ❓ 5. Kenapa pesan gagal login tidak boleh membedakan email salah dan password salah?

* **Kerentanan *User Enumeration*:**  
  Jika sistem membedakan pesan error (misalnya: *"Email tidak terdaftar"* vs *"Password salah"*), penyerang otomatis mengetahui dengan pasti alamat email mana saja yang terdaftar di basis data sistem kampus. Penyerang kemudian dapat mengumpulkan daftar email valid tersebut dan memfokuskan serangan *brute force* atau *credential stuffing* pada akun-akun yang terbukti ada.
* **Standar Keamanan:**  
  Pesan error wajib diseragamkan menjadi satu respon netral:
  ```json
  {
    "message": "Data yang diberikan tidak valid.",
    "errors": {
      "email": [
        "Email atau kata sandi salah."
      ]
    }
  }
  ```
  sehingga penyerang tidak memperoleh informasi validitas keberadaan akun.

---

### ❓ 6. Kenapa endpoint login wajib di-*throttle*? Berapa nilai yang Anda pakai dan mengapa?

* **Tujuan *Throttling*:**  
  Mencegah serangan *brute-force* dan serangan penolakan layanan (*Denial of Service - DoS*). Algoritma hashing kata sandi seperti Bcrypt sengaja dibuat membutuhkan komputasi intensif (*resource-heavy*). Jika ribuan percobaan login dijalankan secara serentak per detik, utilisasi CPU server akan mencapai 100% dan melumpuhkan sistem.
* **Nilai Ambang Batas yang Digunakan:**  
  Di KampusLMS diterapkan **`throttle:5,1`** (maksimal 5 permintaan dalam kurun waktu 1 menit per alamat IP).
* **Alasan Pemilihan Nilai:**  
  Nilai 5 percobaan per menit sangat ideal: memberikan toleransi yang cukup bagi pengguna manusia sah yang salah mengetik kata sandinya, namun seketika menghentikan alat otomatis penyerang dengan respon **`429 Too Many Requests`**.

---

*Disusun untuk Dokumentasi Praktikum & Persiapan Evaluasi Milestone Pekan 06 — KampusLMS Kelompok 05*
