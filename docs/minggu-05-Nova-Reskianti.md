# Catatan Praktikum Minggu 5

**Mata Kuliah:** Pemrograman Web  
**Nama:** Nova Reskianti  
**NIM:** 10241058  

---

## READ: Peta Route dan Titik Rawan IDOR

### 1. Peta Route Aplikasi (`php artisan route:list --except-vendor`)

Berikut adalah daftar route utama yang terdaftar pada aplikasi KampusLMS:

| Method | URI | Nama Route | Action / Controller | Middleware |
|---|---|---|---|---|
| `GET` | `/` | `home` | Closure (redirect ke dashboard) | `web` |
| `GET` | `dashboard` | `dashboard` | Closure (tampilan ringkasan) | `web` |
| `GET` | `tentang` | `tentang` | Closure (informasi tim) | `web` |
| `GET` | `switch-role/{role}` | `switch-role` | Closure (ganti sesi simulasi) | `web` |
| `GET` | `courses` | `courses.index` | `CourseController@index` | `web` |
| `GET` | `courses/create` | `courses.create` | `CourseController@create` | `web` |
| `POST` | `courses` | `courses.store` | `CourseController@store` | `web` |
| `GET` | `courses/{course}` | `courses.show` | `CourseController@show` | `web` |
| `GET` | `courses/{course}/edit` | `courses.edit` | `CourseController@edit` | `web` |
| `PUT/PATCH` | `courses/{course}` | `courses.update` | `CourseController@update` | `web` |
| `DELETE` | `courses/{course}` | `courses.destroy` | `CourseController@destroy` | `web` |
| `GET` | `users` | `users.index` | `UserController@index` | `web` |
| `GET` | `users/create` | `users.create` | `UserController@create` | `web` |
| `POST` | `users` | `users.store` | `UserController@store` | `web` |
| `GET` | `users/{user}` | `users.show` | `UserController@show` | `web` |
| `GET` | `users/{user}/edit` | `users.edit` | `UserController@edit` | `web` |
| `PUT/PATCH` | `users/{user}` | `users.update` | `UserController@update` | `web` |
| `DELETE` | `users/{user}` | `users.destroy` | `UserController@destroy` | `web` |
| `GET` | `courses/{course}/materials` | `courses.materials.index` | `MaterialController@index` | `web`, `role:dosen,mahasiswa` |
| `GET` | `materials/{material}` | `materials.show` | `MaterialController@show` | `web`, `role:dosen,mahasiswa` |
| `GET` | `courses/{course}/assignments` | `courses.assignments.index` | `AssignmentController@index` | `web`, `role:dosen,mahasiswa` |
| `GET` | `assignments/{assignment}` | `assignments.show` | `AssignmentController@show` | `web`, `role:dosen,mahasiswa` |
| `GET` | `submissions/{submission}` | `submissions.show` | `SubmissionController@show` | `web`, `role:dosen,mahasiswa` |

---

### 2. Identifikasi Route Berparameter Model & Analisis Hak Akses

Semua route yang memiliki kurung kurawal (`{course}`, `{user}`, `{material}`, `{assignment}`, `{submission}`) adalah route yang menerima parameter model. Jika tidak dijaga dengan otorisasi level objek, endpoint ini berpotensi menjadi celah **IDOR (Insecure Direct Object Reference)**.

---

### 3. Daftar Titik Rawan IDOR

| No | Route / Endpoint | Parameter Model | Siapa yang Berhak Mengakses? | Risiko IDOR Jika Tanpa Otorisasi Objek | Status Proteksi Saat Ini |
|---|---|---|---|---|---|
| 1 | `courses/{course}/edit` | `{course}` | Dosen pengampu mata kuliah tersebut & Admin | Dosen A dapat membuka form edit milik Dosen B hanya dengan mengganti angka ID di URL. | Terlindungi (`abort_unless` kepemilikan) |
| 2 | `courses/{course}` (PUT/DELETE) | `{course}` | Dosen pengampu & Admin | Dosen lain dapat mengubah data atau menghapus mata kuliah yang bukan miliknya. | Terlindungi (`abort_unless` kepemilikan) |
| 3 | `users/{user}/edit` | `{user}` | Admin & User pemilik akun itu sendiri | User biasa bisa mengedit profil, email, atau mengubah kata sandi milik pengguna lain. | Terlindungi (Grup `role:admin`) |
| 4 | `submissions/{submission}` | `{submission}` | Mahasiswa pemilik submission & Dosen pengampu tugas | Mahasiswa A dapat melihat tugas, berkas jawaban, atau nilai milik Mahasiswa B. | Rawan IDOR (Wajib dicek via `abort_unless`/Policy) |
| 5 | `courses/{course}/assignments/{assignment}` | `{course}`, `{assignment}` | Mahasiswa terdaftar di course tersebut & Dosen pengampu | Pengguna bisa mengakses tugas milik course lain dengan manipulasi URL silang (*cross-course access*). | Terlindungi dengan `scopeBindings()` |

---

## BREAK: Enam Kerusakan (Rusak dengan Sengaja)

Eksperimen merusak sistem otorisasi dan routing secara terencana:

| # | Yang Dicoba | Prediksi Sebelum Mencoba | Hasil / Error Sebenarnya |
|---|---|---|---|
| **1** | Login sebagai Mahasiswa A, lalu buka tugas milik Mahasiswa B dengan mengganti angka ID di URL (`/submissions/2`) | Data jawaban dan nilai milik Mahasiswa B akan terbuka jika controller tidak mengecek kepemilikan. | **IDOR Terjadi!** Halaman submission milik Mahasiswa B tampil utuh tanpa ada penolakan, membocorkan privasi berkas dan nilai mahasiswa lain. |
| **2** | Akses `/courses/1/assignments/99` di mana tugas ID 99 sebenarnya milik mata kuliah ID 7 | Sistem seharusnya menolak karena tugas 99 bukan bagian dari mata kuliah 1. | **Lolos Tanpa Scoping!** Halaman tugas 99 tetap terbuka normal karena Laravel secara default mencari model secara independen tanpa memeriksa relasi induknya. |
| **3** | Aktifkan `Route::scopeBindings()` pada route nested, lalu ulangi percobaan nomor 2 | Laravel akan memeriksa relasi `course->assignments()` dan menolak request jika tidak cocok. | **Berhasil Dicegah (HTTP 404 Not Found)!** Laravel mendeteksi bahwa assignment 99 bukan anak dari course 1 dan otomatis melempar error 404. |
| **4** | Mendaftarkan middleware baru di berkas `app/Http/Kernel.php` seperti panduan lama | Pendaftaran akan gagal atau tidak berefek karena struktur framework sudah berubah. | **File Tidak Ditemukan!** Pada Laravel 12, berkas `app/Http/Kernel.php` sudah dihapus total. Middleware wajib didaftarkan di `bootstrap/app.php`. |
| **5** | Pasang middleware `role:admin` pada grup rute, lalu buka halaman menggunakan peran `dosen` | Akses akan langsung diblokir oleh middleware dengan kode HTTP penolakan hak akses. | **Muncul HTTP 403 Forbidden!** Middleware `EnsureUserHasRole` membaca peran user dan langsung memotong request sebelum mencapai controller. |
| **6** | Sebagai Dosen A, buka dan simpan perubahan pada mata kuliah milik Dosen B (keduanya lolos `role:dosen`) | Dosen A bisa mengedit mata kuliah Dosen B karena middleware hanya memeriksa peran "dosen", bukan "dosen pemilik". | **Bypass Otorisasi!** Middleware `role:dosen` meloloskan request karena Dosen A memang seorang dosen. Ini membuktikan bahwa **middleware peran saja TIDAK CUKUP** untuk mengamankan data per-objek. |

---

## FIX: Perbaikan Repo Cacat (7 Masalah Branch `w05`)

Analisis perbaikan dari 7 masalah umum pada repositori yang cacat:

### 1. Route Berada di Luar Grup `auth`
* **Masalah:** Route manajemen data diletakkan di luar middleware autentikasi, sehingga dapat dibuka oleh siapa saja di internet tanpa login.
* **Perbaikan:** Memasukkan seluruh rute sensitif ke dalam grup middleware otentikasi dan peran.
* **Dampak Pengguna:** Pengguna wajib login terlebih dahulu sebelum dapat melihat menu perkuliahan.
* **Dampak Penyerang:** Penyerang publik tidak bisa lagi melakukan *scraping* data internal kampus.

### 2. Lubang IDOR pada Submission dan Material
* **Masalah:** Controller hanya memanggil `Submission::findOrFail($id)` tanpa memeriksa apakah pengguna yang login adalah pemilik tugas atau dosen pengampu.
* **Perbaikan:** Menambahkan pemeriksaan kepemilikan sementara di controller:
  ```php
  abort_unless($submission->user_id === auth()->id() || auth()->user()->isLecturer(), 403);
  ```
* **Dampak Pengguna:** Mahasiswa hanya bisa melihat dan mengunduh jawaban milik dirinya sendiri.
* **Dampak Penyerang:** Penyerang yang mencoba mengganti ID submission di URL langsung diblokir dengan pesan 403 Forbidden.

### 3. Nested Route Tanpa `scopeBindings`
* **Masalah:** Route `/courses/{course}/materials/{material}` mengizinkan akses materi milik mata kuliah lain melalui kombinasi ID acak.
* **Perbaikan:** Mengaktifkan chaining method `->scopeBindings()` pada rute bersarang di `routes/web.php`.
* **Dampak Pengguna:** Menjamin navigasi materi selalu konsisten dengan mata kuliah yang sedang dibuka.
* **Dampak Penyerang:** Penyerang tidak bisa lagi melakukan manipulasi parameter URL silang antar mata kuliah.

### 4. Middleware Didaftarkan di Berkas yang Salah
* **Masalah:** Pengembang mencoba membuat file `app/Http/Kernel.php` manual di Laravel 12 sehingga alias middleware tidak pernah terbaca.
* **Perbaikan:** Mendaftarkan alias middleware di dalam method `withMiddleware()` pada berkas `bootstrap/app.php`.
* **Dampak Pengguna:** Aplikasi berjalan normal tanpa error konfigurasi server.
* **Dampak Penyerang:** Aturan proteksi peran aktif berjalan dan tidak terabaikan oleh framework.

### 5. Nama Route Bentrok Antar Peran
* **Masalah:** Route admin dan dosen sama-sama memakai nama `courses.index` tanpa pembeda, sehingga fungsi `route('courses.index')` menghasilkan URL yang salah.
* **Perbaikan:** Menerapkan `name('admin.')` dan `name('dosen.')` pada masing-masing grup peran.
* **Dampak Pengguna:** Tombol navigasi dan redirect mengarah ke dashboard peran yang tepat.
* **Dampak Penyerang:** Menghilangkan celah kebingungan alur navigasi (*route confusion*).

### 6. Tindakan Destruktif Menggunakan Method `GET`
* **Masalah:** Menghapus data mata kuliah menggunakan link biasa `<a href="/courses/1/delete">`.
* **Perbaikan:** Mengubah aksi hapus menjadi form dengan method `POST` dan spoofing `@method('DELETE')` serta proteksi `@csrf`.
* **Dampak Pengguna:** Mencegah data terhapus secara tidak sengaja oleh mesin pencari (*web crawler*) atau klik tautan yang salah.
* **Dampak Penyerang:** Mencegah serangan CSRF via tag `<img>` atau link jebakan sederhana.

### 7. Bukti Pengujian cURL (Sebelum vs Sesudah Perbaikan IDOR)

#### Sebelum Perbaikan (IDOR Terbuka):
```bash
# Mahasiswa A (ID 10) mencoba mengakses submission milik Mahasiswa B (ID 25)
curl -X GET http://kampuslms.test/submissions/25 \
  -H "Cookie: kampuslms_session=sesi_mahasiswa_A"

# Hasil: HTTP 200 OK (Data jawaban Mahasiswa B bocor!)
```

#### Sesudah Perbaikan (IDOR Ditutup dengan `abort_unless`):
```bash
# Mahasiswa A mencoba kembali mengakses submission ID 25
curl -X GET http://kampuslms.test/submissions/25 \
  -H "Cookie: kampuslms_session=sesi_mahasiswa_A"

# Hasil: HTTP 403 Forbidden (Akses ditolak, privasi data aman!)
```

---

## BUILD: Struktur Route dan Keamanan Objek KampusLMS

Implementasi fitur struktur routing dan proteksi objek pada aplikasi KampusLMS:

### 1. Struktur Rute Berbasis Peran di `routes/web.php`
Pengelompokan rute menggunakan prefix URL dan name prefix sesuai hak akses peran:
```php
// Grup Administrator
Route::prefix('admin')->name('admin.')->middleware(['role:admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('courses', CourseController::class);
});

// Grup Dosen
Route::prefix('dosen')->name('dosen.')->middleware(['role:dosen'])->group(function () {
    Route::resource('courses', CourseController::class)->except(['create', 'store']);
});

// Grup Mahasiswa
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['role:mahasiswa'])->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
});
```

### 2. Middleware `EnsureUserHasRole` & Pendaftaran di `bootstrap/app.php`
* Berkas middleware dibuat pada: `app/Http/Middleware/EnsureUserHasRole.php`.
* Memeriksa login (401 jika belum login) dan memeriksa kecocokan peran (403 jika peran tidak sesuai).
* Didaftarkan sebagai alias `'role'` pada `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'role' => \App\Http\Middleware\EnsureUserHasRole::class,
      ]);
  })
  ```

### 3. Penerapan Route Model Binding
Menghilangkan query manual `Course::findOrFail($id)` dan menggantinya dengan injeksi model otomatis pada controller:
```php
public function show(Course $course)
public function edit(Course $course)
public function update(UpdateCourseRequest $request, Course $course)
public function destroy(Course $course)
```
Laravel otomatis mencari data berdasarkan primary key dan melempar HTTP 404 jika record tidak ada.

### 4. Rute Bersarang dengan `shallow()` dan `scopeBindings()`
Mencegah URL yang terlalu panjang dan mencegah akses silang antar model:
```php
Route::resource('courses.materials', MaterialController::class)->shallow()->scopeBindings();
Route::resource('courses.assignments', AssignmentController::class)->shallow()->scopeBindings();
```
* **`shallow()`:** Mengubah URL aksi spesifik seperti edit/show menjadi lebih ringkas (`/materials/{material}` alih-alih `/courses/{course}/materials/{material}`).
* **`scopeBindings()`:** Memastikan bahwa `{material}` benar-benar anak dari `{course}` yang bersangkutan.

### 5. Pemeriksaan Kepemilikan Sementara (`abort_unless`) pada Titik Rawan
Diterapkan pada `app/Http/Controllers/CourseController.php` untuk mencegah Dosen A memodifikasi data milik Dosen B:
```php
protected function authorizeCourseAccess(Course $course): void
{
    $activeRole = session('active_role');
    $user = auth()->user() ?? ($activeRole === 'dosen' ? User::where('role', 'dosen')->first() : null);

    if ($activeRole === 'dosen' && $user) {
        abort_unless(
            $course->lecturer_id === $user->id,
            403,
            'Akses ditolak. Anda tidak memiliki izin untuk mengelola mata kuliah milik dosen lain.'
        );
    }
}
```

### 6. Halaman Error 403 Kustom yang Aman
Dibuat pada `resources/views/errors/403.blade.php`:
* Menggunakan desain modern dan konsisten dengan antarmuka KampusLMS.
* Menyampaikan pesan penolakan secara informatif dan sopan tanpa membocorkan detail kepemilikan data pengguna lain (misalnya: tidak menyebutkan nama pemilik asli dokumen).
* Menyediakan tombol navigasi untuk kembali ke halaman sebelumnya atau ke dashboard.

---

## Checkpoint Minggu 5

### 1. Apa itu IDOR? Peragakan satu contoh di aplikasi Anda, lalu tunjukkan perbaikannya.

* **Pengertian Sederhana:**  
  IDOR (*Insecure Direct Object Reference*) adalah celah keamanan di mana pengguna bisa mengintip atau mengubah data milik orang lain hanya dengan mengganti angka ID di alamat URL browser. Ini terjadi karena server lupa mengecek: *"Apakah orang yang meminta data ini benar-benar pemiliknya?"*.

* **Contoh Nyata di KampusLMS:**  
  Misalnya mahasiswa bernama Budi membuka hasil tugasnya di URL:  
  `http://kampuslms.test/submissions/10`  
  Lalu Budi iseng mengganti angka `10` menjadi `11`:  
  `http://kampuslms.test/submissions/11`  
  Jika controller langsung mengambil data tanpa memeriksa siapa yang login, Budi bisa melihat jawaban tugas dan nilai milik temannya.

* **Cara Memperbaikinya:**  
  Di dalam controller, periksa apakah ID pemilik data sama dengan ID pengguna yang sedang login:
  ```php
  public function show(Submission $submission)
  {
      // Tolak jika bukan pemiliknya dan bukan dosen
      abort_unless($submission->user_id === auth()->id() || auth()->user()->role === 'dosen', 403);

      return view('submissions.show', compact('submission'));
  }
  ```

---

### 2. Kenapa mengganti ID berurutan dengan UUID **bukan** perbaikan IDOR?

* **Penjelasan Sederhana:**  
  Mengganti ID angka (`1, 2, 3`) menjadi UUID acak (`a8098c1a-f86e-11da-bd1a...`) hanya membuat URL jadi sulit ditebak (*Security by Obscurity*), **tetapi pintu keamanannya tetap terbuka**.
* **Kenapa Masih Bocor?**  
  UUID tetap bisa bocor lewat tautan chat, riwayat browser, atau response JSON API. Begitu penyerang mendapatkan kode UUID tersebut dan membukanya di browser, server tetap akan menampilkan data orang lain karena server **tetap tidak mengecek hak kepemilikannya**.
* **Solusi Asli:**  
  Keamanan sejati adalah melakukan pengecekan hak akses (*authorization check*), bukan sekadar menyamarkan angka ID.

---

### 3. Route model binding menjamin apa, dan **tidak** menjamin apa?

* **Yang Dijamin:**  
  Menjamin bahwa **data tersebut memang ada di database**. Jika kita mengetik `/courses/999` dan data ID 999 tidak ada, Laravel otomatis menampilkan error `404 Not Found`. Kita tidak perlu lagi menulis `Course::findOrFail($id)` manual.
* **Yang TIDAK Dijamin:**  
  Sama sekali **tidak menjamin bahwa pengguna yang sedang membuka URL itu berhak melihat data tersebut**. Data yang ada di database akan langsung diambil dan diberikan ke siapa saja jika kita tidak memasang pengecekan izin (*authorization*).

---

### 4. Apa yang dilakukan `Route::scopeBindings()`? Beri contoh URL yang lolos tanpa itu.

* **Fungsi Sederhana:**  
  `scopeBindings()` memastikan bahwa data anak di URL benar-benar anak kandung dari data induk di depannya.
* **Contoh URL yang Lolos Tanpa `scopeBindings()`:**  
  Misalnya Tugas ID 99 sebenarnya milik mata kuliah Matematika (Course 7). Tanpa `scopeBindings()`, penyerang bisa membuka:  
  `/courses/1/assignments/99` (Course 1 = Pemrograman Web).  
  Laravel tetap menampilkan tugas 99 karena ia mencari Course 1 dan Assignment 99 secara terpisah tanpa mengecek hubungannya.
* **Setelah Memakai `scopeBindings()`:**  
  Laravel otomatis mengecek relasi: *"Apakah tugas 99 terdaftar di mata kuliah 1?"*. Karena bukan, Laravel langsung menolaknya dengan error **404 Not Found**.

---

### 5. Di berkas mana middleware didaftarkan pada Laravel 12? Kenapa berbeda dari kebanyakan tutorial?

* **Di Mana Didaftarkannya?**  
  Pada file **`bootstrap/app.php`**, di dalam fungsi `withMiddleware()`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'role' => \App\Http\Middleware\EnsureUserHasRole::class,
      ]);
  })
  ```
* **Kenapa Berbeda dari Tutorial Umum?**  
  Di tutorial lama (Laravel 10 ke bawah), middleware didaftarkan di berkas `app/Http/Kernel.php`. Mulai Laravel 11 dan 12, framework disederhanakan (*streamlined structure*): berkas `Kernel.php` dihapus total dan seluruh konfigurasi routing serta middleware dipusatkan langsung ke berkas `bootstrap/app.php`.

---

### 6. Kenapa middleware `role:dosen` tidak cukup untuk mencegah Dosen A mengedit mata kuliah Dosen B?

* **Penjelasan Sederhana:**  
  Middleware `role:dosen` ibarat satpam di pintu depan gedung yang hanya memeriksa tanda pengenal: *"Apakah Anda seorang dosen?"*.  
  * Ketika Dosen A datang, satpam melihat tanda pengenal Dosen A dan mempersilakannya masuk karena dia memang dosen.
  * Masalahnya, satpam gerbang tidak tahu dosen tersebut mau masuk ke ruangan kerja siapa. Begitu lolos dari satpam depan, Dosen A bisa sembarangan masuk ke ruangan Dosen B dan mengacak-acak dokumen di sana.
* **Kesimpulan:**  
  Middleware hanya memeriksa **Peran Global (Siapa Anda)**, bukan **Kepemilikan Objek (Data siapa yang sedang Anda utak-atik)**. Untuk mencegah Dosen A mengedit mata kuliah Dosen B, kita wajib memasang otorisasi kepemilikan objek di controller atau menggunakan Policy (`$course->lecturer_id === $user->id`).
