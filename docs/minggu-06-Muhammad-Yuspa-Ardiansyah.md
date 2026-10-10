# Catatan Praktikum Minggu 6 Proweb

---

Muhammad Yuspa Ardiansyah

10241052

# READ:  Memahami REST API dan Sanctum pada Laravel 12

Praktikum Minggu ke-6 membahas pemisahan logika aplikasi (*backend*) dari bagian tampilan (*presentation*) dengan menerapkan **REST API (*Representational State Transfer Application Programming Interface*)**. Pada KampusLMS, pendekatan ini memungkinkan satu backend menyediakan data untuk beberapa jenis klien, seperti web berbasis Blade, aplikasi mobile, SPA, dan layanan akademik eksternal.

---

### 1. Perbedaan Web Monolitik vs REST API

| Aspek | Jalur Web (`routes/web.php`) | Jalur REST API (`routes/api.php`) |
|---|---|---|
| **Mekanisme Identitas** | Stateful dengan Session & Cookie | Stateless menggunakan Bearer Token ([Sanctum](https://laravel.com/docs/12.x/sanctum)) |
| **Proteksi CSRF** | Memerlukan token CSRF (`@csrf`) untuk formulir web | Umumnya tidak memakai token CSRF pada API yang menggunakan Bearer Token |
| **Respon Gagal Autentikasi** | Dapat mengarahkan pengguna ke halaman login melalui HTTP 302 | Mengirim JSON dengan HTTP 401 Unauthorized (`{"message": "Unauthenticated."}`) |
| **Format Muatan Balik** | Halaman HTML yang dirender server | Data terstruktur dalam JSON (*JavaScript Object Notation*) |
| **Sifat Komunikasi** | Stateful (server menyimpan informasi session) | Stateless (request membawa kredensial autentikasinya sendiri) |

---

### 2. Menyiapkan `routes/api.php` pada Laravel 12

Dalam struktur Laravel 12 yang minimalis, file `routes/api.php` tidak selalu tersedia ketika proyek pertama kali dibuat. Agar fitur API dapat digunakan, jalankan perintah resmi berikut:

```bash
php artisan install:api
```

Perintah ini menyiapkan kebutuhan API, termasuk:
1. Memasang dependensi `laravel/sanctum`.
2. Membuat berkas route API apabila belum tersedia.
3. Menyiapkan migrasi tabel `personal_access_tokens`.
4. Menyiapkan konfigurasi routing API pada `bootstrap/app.php`.

---

### 3. API Resource: Mengendalikan Data yang Dikirim

Mengirim model Eloquent secara langsung, misalnya `return response()->json(User::all())`, berisiko membuat atribut database yang tidak dibutuhkan ikut muncul dalam response. Informasi seperti hash `password`, `remember_token`, atau data pribadi pengguna dapat terekspos jika tidak dilindungi dengan benar.

**API Resource** (`JsonResource`) digunakan untuk menentukan atribut yang boleh dikirim kepada klien melalui konsep *whitelist*. Dengan cara ini, response tidak bergantung pada seluruh atribut model. Selain itu, `whenLoaded()` membantu menampilkan relasi hanya jika relasi tersebut sudah dimuat sebelumnya. Hal ini dapat mengurangi query tambahan dan mencegah masalah N+1 yang memengaruhi performa.

---

### 4. Perbandingan `CourseController`: Web dan API

* **`CourseController` Versi Web:** Mengambil data kursus dan menampilkan halaman Blade melalui `return view('courses.index', compact('courses'))`. Proses ini dapat menggunakan redirect, pesan flash, dan autentikasi berbasis session browser.
* **`CourseController` Versi API:** Mengirimkan data melalui `CourseResource::collection($courses)` agar atribut response dapat dikendalikan. API juga menggunakan status HTTP untuk menunjukkan hasil autentikasi dan otorisasi (`401` atau `403`) serta menyediakan data dalam format JSON.

---

### 5. Kegunaan Header `Accept: application/json`

Saat klien mengakses endpoint API tanpa header `Accept: application/json`, respons kesalahan tertentu dapat diperlakukan seperti request web biasa. Akibatnya, Laravel mungkin mengembalikan redirect HTML, bukan response JSON yang diharapkan. Untuk meminta respons JSON, sertakan header berikut:

```http
Accept: application/json
```

Header tersebut menunjukkan bahwa klien mengharapkan response JSON, termasuk ketika terjadi kesalahan validasi (`422 Unprocessable Content`) atau autentikasi (`401 Unauthorized`).

---

# BREAK: Tujuh Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Skenario berikut digunakan untuk memahami risiko keamanan dan masalah performa yang dapat muncul apabila perlindungan API dihilangkan. Hasil pada kolom observasi harus disesuaikan dengan pengujian yang benar-benar dilakukan.

| # | Skenario Kerusakan Terencana | Prediksi Sebelum Pengujian | Observasi & Bukti Nyata |
|:--:|---|---|---|
| **1** | Mengirim `response()->json(User::all())` langsung dari controller tanpa menggunakan Resource. | Atribut model pengguna berpotensi ikut dikonversi menjadi JSON. | **Risiko Kebocoran Data:** Field sensitif yang tidak disembunyikan, misalnya hash kata sandi atau informasi pengguna lain, dapat muncul pada response. |
| **2** | Melepas middleware `auth:sanctum` dari grup route `/api/v1/courses`. | Endpoint tidak lagi dilindungi oleh middleware autentikasi tersebut. | **Akses Tanpa Autentikasi:** Pengguna anonim berpotensi membaca data kursus apabila tidak ada lapisan perlindungan lain. |
| **3** | Mengakses endpoint terlindungi dengan token yang sudah dicabut (*revoked*). | Server tidak lagi menerima token tersebut sebagai kredensial yang valid. | Respons yang diharapkan adalah **`401 Unauthorized`** (`{"message": "Unauthenticated."}`). |
| **4** | Login sebagai mahasiswa, kemudian mengirim `POST /api/v1/assignments`. | Pemeriksaan peran harus menolak mahasiswa yang mencoba membuat tugas. | **Pembedaan 401 vs 403:** Pengguna sudah terautentikasi, tetapi tidak mempunyai izin sehingga respons yang diharapkan adalah **`403 Forbidden`**. |
| **5** | Menghapus eager loading `with('lecturer')` pada pengambilan koleksi kursus. | Pengaksesan relasi dosen satu per satu dapat menimbulkan query N+1. | Jumlah query dapat meningkat karena aplikasi perlu mengambil data dosen secara terpisah. Verifikasi melalui log atau profiler query. |
| **6** | Menghapus middleware `throttle:5,1` dari endpoint login. | Tidak ada lagi pembatasan percobaan login dari middleware tersebut. | **Risiko Brute-Force:** Penyerang dapat mencoba kombinasi kredensial berulang tanpa batas yang ditetapkan middleware itu. |
| **7** | Membuat pesan login berbeda untuk kasus "Email tidak terdaftar" dan "Password salah". | Respons dapat mengungkap apakah suatu email terdaftar. | **User Enumeration:** Penyerang dapat mengumpulkan informasi tentang akun yang valid berdasarkan perbedaan pesan kesalahan. |

---

# BUILD: Implementasi REST API KampusLMS Sesuai Kontrak Bagian 5

### 1. Struktur Rute pada `routes/api.php`

Endpoint API dikelompokkan dengan prefix `/api/v1`. Contoh struktur route berdasarkan kontrak adalah:

```php
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

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

Operasi *upsert* digunakan untuk menangani dua kondisi, membuat nilai baru ketika belum tersedia atau memperbarui nilai yang sudah ada. Contoh implementasi berikut menunjukkan pola dasarnya ini:

```php
public function grade(GradeSubmissionRequest $request, Submission $submission)
{
    $grade = $submission->grade ?? new Grade();
    $grade->fill($request->safe()->only(['score', 'feedback']));
    $grade->submission_id = $submission->id;
    $grade->graded_by = $request->user()->id;
    $grade->graded_at = now();
    $grade->save();

    // 201 ketika nilai baru dibuat, 200 ketika nilai diperbarui
    return (new GradeResource($grade->load('grader')))
        ->response()
        ->setStatusCode($grade->wasRecentlyCreated ? 201 : 200);
}
```

---

### 3. Menjalankan Skrip Uji cURL (`scripts/test-api.sh`)

Skrip pengujian API dapat dijalankan pada server lokal KampusLMS dengan perintah:

```bash
bash scripts/test-api.sh
```

**Contoh Keluaran Terminal:**

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

*Catatan: blok di atas adalah contoh hasil keluaran dari dokumen sumber, bukan bukti bahwa pengujian pada lingkungan lokal Anda sudah dijalankan. Ganti dengan hasil terminal aktual setelah skrip dijalankan.*

---

### 4. Bukti Pengujian Otomatis Laravel (`tests/Feature/WeekSixApiTest.php`)

Jalankan pengujian fitur Laravel menggunakan:

```bash
php artisan test
```

**Contoh Keluaran Pengujian:**

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
# CHECKPOINT MINGGU 6

### 1. Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.

Model mentah berbahaya karena atribut yang tidak seharusnya dilihat pengguna dapat ikut terkirim dalam JSON. Gunakan **API Resource** untuk memilih data yang boleh ditampilkan.

```php
// Berisiko: model langsung dikirim
return response()->json(User::all());

// Lebih aman: gunakan API Resource
return UserResource::collection(User::all());
```

Jika atribut sensitif tidak disembunyikan, response berpotensi membocorkan data seperti `password` hash atau `remember_token`.

### 2. Apa beda 401 dan 403? Tunjukkan di API Anda satu contoh masing-masing.

- **401 Unauthorized:** Pengguna belum terautentikasi atau token tidak valid.
- **403 Forbidden:** Pengguna sudah terautentikasi, tetapi tidak memiliki izin untuk melakukan tindakan.

Contoh di KampusLMS:

```http
GET /api/v1/courses
```

Tanpa token yang valid → `401 Unauthorized`.

```http
POST /api/v1/assignments
Authorization: Bearer <TOKEN_MAHASISWA>
```

Jika mahasiswa tidak memiliki hak membuat tugas → `403 Forbidden`.

### 3. Kenapa `routes/api.php` tidak ada secara default di Laravel 12? Bagaimana mengaktifkannya?

Laravel 12 dapat dibuat dengan struktur minimal sehingga file API tidak langsung tersedia. Untuk memasang kebutuhan API dan membuat konfigurasi yang diperlukan, jalankan:

```bash
php artisan install:api
```

Perintah ini menyiapkan routing API dan Laravel Sanctum untuk autentikasi berbasis token.

### 4. Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?

`whenLoaded()` menampilkan data relasi hanya jika relasi tersebut sudah dimuat. Ini membantu mencegah query tambahan yang tidak diperlukan saat Resource mengakses relasi.

```php
'lecturer' => new UserResource($this->whenLoaded('lecturer')),
```

Tanpanya, akses relasi yang belum dimuat dapat memicu query tambahan dan menyebabkan masalah **N+1 query** ketika menampilkan banyak data.

### 5. Kenapa pesan gagal login tidak boleh membedakan email salah dan password salah?

Karena perbedaan pesan dapat membocorkan apakah suatu email terdaftar. Penyerang bisa memanfaatkan informasi tersebut untuk melakukan *user enumeration*.

Gunakan pesan umum untuk kedua kondisi:

```json
{
  "message": "Email atau kata sandi salah."
}
```

### 6. Kenapa endpoint login wajib di-*throttle*? Berapa nilai yang Anda pakai dan mengapa?

*Rate limiting* membatasi jumlah request dalam periode tertentu sehingga mengurangi risiko **brute-force**, yaitu percobaan banyak kombinasi kata sandi secara berulang.

Pada KampusLMS, konfigurasi yang digunakan adalah:

```php
->middleware('throttle:5,1')
```

Artinya, maksimal **5 percobaan dalam 1 menit** untuk batasan yang diterapkan middleware tersebut. Nilai ini membatasi percobaan login berulang, sedangkan endpoint API lain dapat menggunakan batas berbeda, misalnya `throttle:60,1`.

