# Catatan Praktikum Minggu 5 Pemrograman Web

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

# I. READ: Analisis Pemetaan Route & Daftar Titik Rawan IDOR

Pada Minggu ke-5 ini, fokus pembelajaran adalah arsitektur navigasi dan keamanan akses di Laravel 12: pengorganisasian rute berbasis **Route Group** dan **Prefix Peran**, pemanfaatan **Route Model Binding**, penerapan **Nested Resources dengan Scoped Binding**, pengenalan lapisan gerbang via **Middleware**, serta pemahaman mendalam mengenai kerentanan **IDOR (*Insecure Direct Object Reference*)**.

---

### 1. Audit Route KampusLMS

Berdasarkan eksekusi perintah:
```bash
php artisan route:list --except-vendor
```
Aplikasi KampusLMS menghasilkan 108 rute terstruktur yang telah dikelompokkan ke dalam tiga area peran utama (`admin.*`, `dosen.*`, `mahasiswa.*`), rute bersarang materi/tugas, dan rute publik/kompatibilitas.

---

### 2. Tabel Titik Rawan IDOR (*Insecure Direct Object Reference*)

Kerentanan IDOR terjadi ketika sistem menerima parameter identitas objek langsung dari pengguna (seperti `{course}`, `{assignment}`, `{submission}`) tanpa memastikan apakah pengguna yang sedang login memiliki hak atas objek tersebut.

| No | Endpoint Rawan | Parameter Objek | Siapa yang Berhak Akses? | Apa yang Mencegah Pengguna Lain? (Status Mitigasi) |
|:--:|---|:---:|---|---|
| **1** | `GET /submissions/{submission}` | `{submission}` | • Mahasiswa pemilik tugas<br>• Dosen pengampu course terkait<br>• Admin | **`SubmissionController@show`**: Memeriksa `abort_unless((int)$submission->user_id === auth()->id() \|\| auth()->user()->role === 'admin' \|\| $submission->assignment->course->lecturer_id === auth()->id(), 403)`. |
| **2** | `GET/PUT/DELETE /courses/{course}` (area edit/hapus) | `{course}` | • Dosen pengampu course<br>• Admin | **`CourseController@authorizeCourseManager`**: Memeriksa `$course->lecturer_id === auth()->id()` atau role `admin`. Dosen lain otomatis terblokir dengan **403 Forbidden**. |
| **3** | `GET /courses/{course}/assignments/{assignment}` | `{course}`<br>`{assignment}` | • Dosen pengampu course<br>• Mahasiswa terdaftar<br>• Admin | **`Route::scopeBindings()`** & controller check: Memastikan `{assignment}` benar-benar anak relasi dari `{course}` (`assignment->course_id === course->id`). Jika tugas milik matkul lain $\rightarrow$ **404 Not Found**. |
| **4** | `GET /courses/{course}/materials/{material}` | `{course}`<br>`{material}` | • Dosen pengampu course<br>• Mahasiswa terdaftar<br>• Admin | **`Route::scopeBindings()`**: Memvalidasi relasi parent-child antara course dan material. |
| **5** | `POST /courses` | Payload data | • Dosen pengampu (hanya untuk dirinya sendiri)<br>• Admin | **`CourseController@store`**: Memaksa `$data['lecturer_id'] = $request->user()->id` jika pembuat adalah dosen, mencegah dosen menetapkan mata kuliah atas nama dosen lain. |
| **6** | `GET/PUT/DELETE /users/{user}` | `{user}` | • Admin saja | **Middleware `role:admin`**: Memblokir dosen dan mahasiswa di gerbang depan rute dengan status **403 Forbidden**. |

---

# II. BREAK: Enam Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Eksperimen berikut dilakukan untuk mengamati langsung bagaimana kelemahan kontrol akses bekerja dan membuktikan perlindungan yang dibangun:

| # | Yang Dicoba | Prediksi Sebelum Menguji | Hasil & Observasi Nyata |
|:--:|---|---|---|
| **1** | Login sebagai Mahasiswa A, membuka submission milik Mahasiswa B di `/submissions/42`. | Jika tidak ada pengecekan kepemilikan, data jawaban dan nilai Mahasiswa B akan terbaca oleh Mahasiswa A. | **Celah IDOR Nyata:** Mahasiswa A dapat membaca nama, file, dan nilai Mahasiswa B. Setelah dipasang mitigasi `abort_unless`, request langsung ditolak dengan status **`403 Forbidden`**. |
| **2** | Mengakses `/courses/1/assignments/99` di mana tugas 99 sebenarnya milik Course 7 (tanpa `scopeBindings`). | Tanpa scoping, Laravel mengambil Course 1 dan Assignment 99 secara terpisah tanpa mencocokkan relasinya. | Tugas mata kuliah Aljabar Linear (ID 99) muncul di halaman Pemrograman Web (ID 1). Tampilan menjadi tidak konsisten. |
| **3** | Mengaktifkan `Route::scopeBindings()`, lalu mengulangi pengujian nomor 2. | Laravel akan memeriksa hubungan relasi database antar model. | Request langsung dihentikan dengan respon **`404 Not Found`** karena tugas ID 99 bukan anak dari Course ID 1. |
| **4** | Mendaftarkan middleware `EnsureUserHasRole` di `app/Http/Kernel.php` seperti tutorial lama. | Berkas tidak ditemukan pada arsitektur proyek Laravel 12. | File `Kernel.php` tidak ada di Laravel 12. Pendaftaran harus dilakukan di `bootstrap/app.php` melalui `$middleware->alias()`. |
| **5** | Memasang middleware `role:admin` pada rute pengguna, lalu login dan mengakses sebagai Dosen. | Dosen akan dicegat di gerbang middleware sebelum mencapai controller. | Server merespons dengan **`403 Forbidden`** melalui tampilan halaman error kustom `resources/views/errors/403.blade.php`. |
| **6** | Sebagai Dosen A, mencoba mengedit mata kuliah milik Dosen B (keduanya lolos middleware `role:dosen`). | Middleware `role:dosen` meloloskan request karena Dosen A adalah dosen, namun controller harus memblokirnya. | **Membuktikan Middleware Saja Tidak Cukup:** Middleware `role:dosen` berhasil ditembus, tetapi method `authorizeCourseManager` di `CourseController` menangkap pelanggaran kepemilikan dan melempar **`403 Forbidden`**. |

---

# III. FIX: Analisis dan Penanganan Kerusakan

Berdasarkan skenario kerusakan pada tahap *BREAK*:

1. **Pemisahan Autentikasi vs Otorisasi Objek:** Middleware hanya memverifikasi *apakah pengguna sudah login* (`401`) dan *apa perannya* (`403`). Otorisasi data spesifik wajib diverifikasi di controller atau policy (`abort_unless($object->owner_id === auth()->id(), 403)`).
2. **Pencegahan Cross-Course Data Leak:** Seluruh rute bersarang multi-parameter (`/courses/{course}/assignments/{assignment}`) wajib dibungkus dengan `Route::scopeBindings()`.
3. **Penyederhanaan Arsitektur Laravel 12:** Konfigurasi middleware dipusatkan pada `bootstrap/app.php`.
4. **Halaman 403 yang Netral:** Halaman error tidak boleh membocorkan informasi privat pemilik data (cukup menyampaikan bahwa akses ditolak secara umum).

---

# IV. BUILD: Implementasi Struktur Route, Scoped Binding, & Middleware KampusLMS

### 1. Struktur Route Group Berdasarkan Peran di `routes/web.php`
Rute dikelompokkan ke dalam area hak akses yang jelas:
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

### 2. Middleware `EnsureUserHasRole` & Registrasi di `bootstrap/app.php`
Dibuat di `app/Http/Middleware/EnsureUserHasRole.php`:
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

        // 401 jika belum login sama sekali
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

Didaftarkan di `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => EnsureUserHasRole::class,
    ]);
})
```

### 3. Penerapan Route Model Binding
Menghilangkan seluruh pencarian manual `Course::findOrFail($id)` pada controller:
```php
public function show(Request $request, Course $course)
public function edit(Request $request, Course $course)
public function update(UpdateCourseRequest $request, Course $course)
public function destroy(Request $request, Course $course)
```

### 4. Rute Bersarang dengan `shallow()` dan `scopeBindings()`
* `->shallow()` menghasilkan URL yang ringkas untuk aksi detail (`/assignments/{assignment}` alih-alih `/courses/{course}/assignments/{assignment}`).
* `Route::scopeBindings()` memastikan relasi hierarkis antar model divalidasi oleh Eloquent secara otomatis.

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

### 6. Halaman Error 403 Kustom
Ditempatkan pada `resources/views/errors/403.blade.php`:
* Menampilkan kode HTTP 403 dan judul "Akses Ditolak".
* Pesan bersih: *"Maaf, kamu tidak memiliki izin untuk mengakses halaman ini."*
* Tidak membocorkan nama pemilik dokumen, ID asli, maupun alasan internal hak akses.
* Menyediakan tautan kembali ke Dashboard.
