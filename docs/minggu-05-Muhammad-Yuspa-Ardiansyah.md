# Catatan Praktikum Minggu 5 Proweb

---

Muhammad Yuspa Ardiansyah

10241052

# READ: Analisis Pemetaan Route & Daftar Titik Rawan IDOR


### 1. Audit Route KampusLMS

Berdasarkan perintah:
```bash
php artisan route:list --except-vendor
```
Route KampusLMS dikelompokkan berdasarkan role, terutama `admin.*`, `dosen.*`, dan `mahasiswa.*`. Selain itu ada route untuk material, assignment, submission, grade, notification, dan route umum lainnya.

---

### 2. Tabel Titik Rawan IDOR (*Insecure Direct Object Reference*)

IDOR terjadi ketika pengguna dapat membuka data hanya dengan mengganti ID pada URL, tetapi sistem tidak mengecek apakah pengguna tersebut memang berhak mengakses data itu.

| No | Endpoint Rawan | Parameter Objek | Siapa yang Berhak Akses? | Apa yang Mencegah Pengguna Lain? (Status Mitigasi) |
|:--:|---|:---:|---|---|
| **1** | `GET /submissions/{submission}` | `{submission}` | • Mahasiswa pemilik submission<br>• Dosen pengampu course<br>• Admin | **`SubmissionController@show`** melakukan pengecekan kepemilikan submission atau hak akses dosen/admin. Jika tidak berhak, request ditolak dengan **403 Forbidden**. |
| **2** | `GET/PUT/DELETE /courses/{course}` (area edit/hapus) | `{course}` | • Dosen pengampu course<br>• Admin | **`CourseController@authorizeCourseManager`** memastikan course milik dosen yang login atau pengguna adalah admin. |
| **3** | `GET /courses/{course}/assignments/{assignment}` | `{course}`<br>`{assignment}` | • Dosen pengampu course<br>• Mahasiswa yang berhak<br>• Admin | **`Route::scopeBindings()`** membantu memastikan assignment memang berhubungan dengan course pada URL. |
| **4** | `GET /courses/{course}/materials/{material}` | `{course}`<br>`{material}` | • Dosen pengampu course<br>• Mahasiswa yang berhak<br>• Admin | **`Route::scopeBindings()`** memastikan material berada pada course yang sesuai. |
| **5** | `POST /courses` | Payload data | • Dosen<br>• Admin | **`CourseController@store`** membatasi `lecturer_id` untuk dosen agar tidak bisa membuat course atas nama dosen lain. |
| **6** | `GET/PUT/DELETE /users/{user}` | `{user}` | • Admin saja | **Middleware `role:admin`** membatasi route users sehingga dosen dan mahasiswa tidak dapat masuk. |

---

# BREAK: Enam Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Skenario berikut digunakan untuk memahami apa yang terjadi jika kontrol akses tidak diterapkan dengan benar:

| # | Yang Dicoba | Prediksi Sebelum Menguji | Hasil & Observasi |
|:--:|---|---|---|
| **1** | Login sebagai Mahasiswa A lalu mencoba membuka submission milik Mahasiswa B. | Jika tidak ada pengecekan kepemilikan, data Mahasiswa B bisa terbaca. | Dengan pengecekan kepemilikan, akses ditolak dengan **403 Forbidden**. |
| **2** | Membuka `/courses/1/assignments/99` ketika assignment 99 sebenarnya milik course lain tanpa `scopeBindings()`. | Laravel dapat mengambil course dan assignment secara terpisah. | Assignment dari course lain berpotensi tampil pada course yang salah. |
| **3** | Menambahkan `Route::scopeBindings()` lalu mengulangi pengujian nomor 2. | Laravel akan memeriksa hubungan course dan assignment. | Jika assignment bukan milik course tersebut, request menghasilkan **404 Not Found**. |
| **4** | Mendaftarkan middleware seperti tutorial lama di `app/Http/Kernel.php`. | Struktur Laravel 12 tidak menggunakan file tersebut untuk pendaftaran middleware seperti tutorial lama. | Alias middleware didaftarkan di **`bootstrap/app.php`** menggunakan `$middleware->alias()`. |
| **5** | Memasang middleware `role:admin`, lalu mencoba membuka route admin sebagai dosen. | Dosen harus ditolak sebelum masuk ke controller. | Request ditolak dengan **403 Forbidden**. |
| **6** | Sebagai Dosen A, mencoba mengedit course milik Dosen B. | Keduanya sama-sama memiliki role `dosen`, jadi middleware role saja belum cukup. | Pemeriksaan kepemilikan di `CourseController` harus menolak Dosen A dengan **403 Forbidden**. |

---

# BUILD: Implementasi Struktur Route, Scoped Binding, & Middleware KampusLMS

### 1. Struktur Route Group Berdasarkan Peran di `routes/web.php`
Rute dikelompokkan supaya akses setiap role lebih jelas:
```php
Route::middleware('auth')->group(function () {
    // Area Admin
    Route::prefix('admin')->name('admin.')
        ->middleware('role:admin')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('courses', CourseController::class);
            Route::scopeBindings()->group(function () {
                Route::resource('courses.materials', MaterialController::class)->shallow();
                Route::resource('courses.assignments', AssignmentController::class)->shallow();
            });
        });

    // Area Dosen
    Route::prefix('dosen')->name('dosen.')
        ->middleware('role:dosen')->group(function () {
            Route::resource('courses', CourseController::class);
            Route::scopeBindings()->group(function () {
                Route::resource('courses.materials', MaterialController::class)->shallow();
                Route::resource('courses.assignments', AssignmentController::class)->shallow();
            });
        });

    // Area Mahasiswa
    Route::prefix('mahasiswa')->name('mahasiswa.')
        ->middleware('role:mahasiswa')->group(function () {
            Route::resource('courses', CourseController::class)->only(['index', 'show']);
            Route::scopeBindings()->group(function () {
                Route::resource('courses.materials', MaterialController::class)->only(['index', 'show'])->shallow();
                Route::resource('courses.assignments', AssignmentController::class)->only(['index', 'show'])->shallow();
            });
        });
});
```

---

### 2. Middleware `EnsureUserHasRole` & Registrasi di `bootstrap/app.php`
Middleware dibuat di `app/Http/Middleware/EnsureUserHasRole.php`:
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 401 jika belum login
        if (!$user) {
            abort(401);
        }

        // 403 jika role tidak sesuai
        if (!in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
```

Alias middleware didaftarkan di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => EnsureUserHasRole::class,
    ]);
})
```

---

### 3. Penerapan Route Model Binding
Daripada mencari data secara manual dengan `findOrFail($id)`, controller menggunakan model langsung dari parameter route:
```php
public function show(Request $request, Course $course)
public function edit(Request $request, Course $course)
public function update(UpdateCourseRequest $request, Course $course)
public function destroy(Request $request, Course $course)
```

Dengan cara ini Laravel membantu mengambil model berdasarkan parameter route. Jika data tidak ditemukan, Laravel memberikan **404 Not Found**.

---

### 4. Rute Bersarang dengan `shallow()` dan `scopeBindings()`
* `->shallow()` membuat URL untuk detail resource menjadi lebih pendek, misalnya `/assignments/{assignment}`.
* `Route::scopeBindings()` memastikan model anak tetap sesuai dengan model induknya.

---

### 5. Pemeriksaan Kepemilikan Sementara (`abort_unless`) untuk Mencegah IDOR
Pada `app/Http/Controllers/SubmissionController.php`:
```php
abort_unless(
    (int) $submission->user_id === (int) $user->id
        || $user->role === 'admin'
        || (
            $user->role === 'dosen'
            && (int) $submission->assignment->course->lecturer_id === (int) $user->id
        ),
    403
);
```

Artinya, submission hanya boleh diakses oleh pemiliknya, admin, atau dosen yang mengampu course tersebut.

Pada `app/Http/Controllers/CourseController.php`:
```php
private function authorizeCourseManager(Request $request, Course $course): void
{
    $user = $request->user();
    abort_unless($user, 401);
    abort_unless(
        $user->role === 'admin'
            || ($user->role === 'dosen' && (int) $course->lecturer_id === (int) $user->id),
        403
    );
}
```

Artinya, admin boleh mengelola course, sedangkan dosen hanya boleh mengelola course miliknya sendiri.

---

### 6. Halaman Error 403 Kustom
Ditempatkan pada `resources/views/errors/403.blade.php`:
* Menampilkan kode HTTP 403 dan judul "Akses Ditolak".
* Pesannya sederhana: *"Maaf, kamu tidak memiliki izin untuk mengakses halaman ini."*
* Tidak menampilkan informasi pribadi atau alasan internal sistem.
* Menyediakan tautan kembali ke Dashboard.

---

# CHECKPOINT MINGGU 5

---

### Apa itu IDOR? Peragakan satu contoh di aplikasi Anda, lalu tunjukkan perbaikannya.

* **Pengertian Sederhana:**  
  **IDOR (*Insecure Direct Object Reference*)** adalah masalah keamanan ketika pengguna bisa membuka data orang lain hanya dengan mengganti ID pada URL karena sistem tidak mengecek hak aksesnya.
* **Contoh di KampusLMS:**  
  Misalnya Mahasiswa A membuka `/submissions/41`, lalu mengganti menjadi `/submissions/42`. Jika submission 42 milik Mahasiswa B dan tidak ada pengecekan, data B bisa ikut terbuka.
* **Perbaikannya:**  
  Controller harus memastikan submission memang milik pengguna yang sedang login, atau pengguna adalah admin/dosen yang berhak.

---

### Kenapa mengganti ID berurutan dengan UUID **bukan** perbaikan IDOR?

* **Alasan Utama:**  
  Mengganti ID seperti `1, 2, 3` menjadi UUID seperti `d3b07384-d113-4676-9c98-1e4a3e742cc4` hanya membuat ID lebih sulit ditebak. Ini bukan pemeriksaan hak akses.
* **Contohnya:**  
  Jika seseorang sudah mengetahui UUID milik data lain, data tersebut tetap bisa diakses jika server tidak melakukan pengecekan izin.
* **Kesimpulan:**  
  Yang benar-benar mencegah IDOR adalah **authorization check**, bukan hanya mengganti bentuk ID.

---

### Route model binding menjamin apa, dan **tidak** menjamin apa?

* **Yang DIJAMIN:**  
  Laravel membantu mencari model berdasarkan parameter route. Jika data tidak ditemukan, Laravel dapat mengembalikan **404 Not Found**.
* **Yang TIDAK DIJAMIN:**  
  Route model binding **tidak otomatis mengecek apakah pengguna boleh mengakses data tersebut**. Hak akses tetap harus diperiksa oleh middleware, controller, atau Policy.

---

### Apa yang dilakukan `Route::scopeBindings()`? Beri contoh URL yang lolos tanpa itu.

* **Fungsi `Route::scopeBindings()`:**  
  Memastikan model anak pada nested route memang berhubungan dengan model induknya.
* **Contoh:**  
  URL: `/courses/1/assignments/99`.  
  Misalnya Course 1 adalah *Pemrograman Web*, tetapi Assignment 99 sebenarnya milik Course 7. Tanpa `scopeBindings()`, kedua model dapat dicari secara terpisah.
* **Setelah memakai `scopeBindings()`:**  
  Laravel membatasi pencarian assignment berdasarkan course tersebut. Jika Assignment 99 bukan milik Course 1, request menghasilkan **404 Not Found**.

---

### Di berkas mana middleware didaftarkan pada Laravel 12? Kenapa berbeda dari kebanyakan tutorial?

* **Lokasi Pendaftaran di Laravel 12:**  
  Alias middleware didaftarkan di **`bootstrap/app.php`** menggunakan `$middleware->alias([...])`.
* **Kenapa berbeda:**  
  Pada struktur Laravel lama, middleware biasanya ditemukan di `app/Http/Kernel.php`. Pada Laravel 11 dan 12, konfigurasi middleware dipusatkan di `bootstrap/app.php`.

---

### Kenapa middleware `role:dosen` tidak cukup untuk mencegah Dosen A mengedit mata kuliah Dosen B?

* **Batasan Middleware:**  
  Middleware `role:dosen` hanya mengecek apakah pengguna mempunyai role `dosen`. Jadi Dosen A dan Dosen B sama-sama bisa melewati middleware tersebut.
* **Yang masih harus diperiksa:**  
  Controller harus mengecek apakah course tersebut memang milik dosen yang sedang login.
* **Contoh pengecekan:**
  ```php
  abort_unless(
      (int) $course->lecturer_id === (int) auth()->id(),
      403
  );
  ```
* **Kesimpulan:**  
  Middleware menentukan **siapa yang boleh masuk**, sedangkan pemeriksaan kepemilikan menentukan **data mana yang boleh dia kelola**.

