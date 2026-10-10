# Catatan Pemrograman Web Minggu 7


# READ
## 1. Konsep inti

### 1.1 Autentikasi vs otorisasi

| Konsep | Pertanyaan | Contoh Laravel |
|---|---|---|
| **Autentikasi** | Siapa yang sedang mengakses? | `Auth::attempt(...)`, middleware `auth`, session atau token Sanctum |
| **Otorisasi** | Apakah pengguna ini boleh melakukan tindakan pada data ini? | `Gate::authorize('update', $course)`, `CoursePolicy::update()` |

Contoh: dosen berhasil login berarti ia terautentikasi. Ia tetap tidak boleh mengedit mata kuliah milik dosen lain; Policy harus menolak tindakan tersebut.

### 1.2 Login, session, dan logout

Pada starter kit Laravel 12, lokasi file bergantung pada stack yang dipilih. Telusuri `routes/auth.php`, controller/action login, Form Request login, dan model `User`. Pada pola yang memakai `LoginRequest`, controller dapat memanggil `$request->authenticate()`, sementara proses setara `Auth::attempt()` berada di method `authenticate()`.

```php
// Contoh pola login session; sesuaikan dengan kode starter kit.
if (! Auth::attempt($request->only('email', 'password'))) {
    return back()->withErrors([
        'email' => 'Email atau kata sandi salah.',
    ])->onlyInput('email');
}

$request->session()->regenerate();

return redirect()->intended(route('dashboard'));
```

`session()->regenerate()` membuat ID session baru setelah login untuk membantu mencegah **session fixation**. Pertahankan langkah regenerasi yang disediakan starter kit. Catatan akurasi: pada Laravel, guard session bawaan juga memiliki mekanisme rotasi session saat login, jadi efek menghapus satu baris dapat bergantung pada alur autentikasi yang dipakai; tetap jangan menghapus regenerasi eksplisit dari alur starter kit.

Logout session yang benar umumnya mengerjakan tiga hal:

```php
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```

- `Auth::logout()` menghapus status autentikasi dari guard.
- `invalidate()` mengosongkan data session dan mengganti ID session.
- `regenerateToken()` membuat token CSRF baru.

Setelah logout, tombol **Back** mungkin masih menampilkan halaman lama dari cache browser. Itu tidak berarti session login masih berlaku. Request baru ke halaman terlindungi harus ditolak atau diarahkan ke login. Untuk halaman sensitif, kontrol cache juga perlu dipertimbangkan.

### 1.3 Hashing kata sandi

Di `app/Models/User.php`, cari cast berikut:

```php
protected function casts(): array
{
    return [
        'password' => 'hashed',
    ];
}
```

Cast `hashed` membuat kata sandi yang diberikan saat disimpan menjadi hash. Hash **bukan enkripsi**: hash tidak dirancang untuk dibalik menjadi kata sandi asli. Saat login, kata sandi yang dimasukkan diverifikasi terhadap hash. Saat lupa kata sandi, aplikasi mengirim **tautan/kode reset** dan menyimpan kata sandi baru setelah pengguna melalui proses reset yang sah; aplikasi tidak dapat mengambil kata sandi lama dari hash.

Jika cast tersebut dihapus dan kode menyimpan input kata sandi mentah tanpa `Hash::make()` atau mekanisme hashing lain, kata sandi baru berisiko tersimpan sebagai teks biasa. Karena itu, jangan pernah menghapus hashing.

### 1.4 Cookie session

Cookie session menghubungkan browser ke session di server. Setelah login, ID session biasanya berubah; bandingkan cookie di DevTools sebelum dan sesudah login. Jangan menyalin atau membagikan nilai cookie session.

- **HttpOnly**: mencegah JavaScript halaman membaca cookie melalui `document.cookie`; bukan perlindungan terhadap semua jenis pencurian.
- **Secure**: browser hanya mengirim cookie melalui HTTPS.
- **SameSite**: membantu membatasi pengiriman cookie lintas situs dan mengurangi risiko CSRF.

Menyalin cookie session ke browser lain dapat menjadi **session hijacking** selama session itu masih berlaku. Aktifkan pengaturan cookie yang sesuai untuk produksi HTTPS; konfigurasi pengembangan lokal bisa berbeda.

### 1.5 Policy dan `Gate::authorize()`

Policy memusatkan aturan akses terhadap model. Laravel biasanya dapat menemukan Policy secara otomatis jika nama dan lokasinya mengikuti konvensi, misalnya `Course` → `CoursePolicy`.

```bash
php artisan make:policy CoursePolicy --model=Course
```

Contoh sederhana untuk memperlihatkan idenya; sesuaikan relasi dan matriks izin proyek:

```php
namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function view(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || $course->lecturer_id === $user->id
            || $course->students()->whereKey($user->id)->exists();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || $course->lecturer_id === $user->id;
    }
}
```

Penjelasan:

- `view()` memberi akses bila pengguna admin, dosen pemilik mata kuliah, atau mahasiswa terdaftar. Relasi `students()` adalah contoh; namanya harus cocok dengan model proyek.
- `update()` hanya memberi akses kepada admin atau dosen pemilik.
- `exists()` mengecek keanggotaan langsung di database tanpa mengambil seluruh koleksi mahasiswa.
- Jangan menyalin aturan ini membabi buta: hak `create`, `delete`, serta akses Material, Assignment, Submission, dan Grade harus mengikuti **matriks hak akses pada spesifikasi proyek**.

Panggil Policy di backend:

```php
use Illuminate\Support\Facades\Gate;

public function update(UpdateCourseRequest $request, Course $course)
{
    Gate::authorize('update', $course);
    $course->update($request->validated());

    return redirect()->route('courses.show', $course)
        ->with('success', 'Mata kuliah berhasil diperbarui.');
}
```

Di Laravel 12, `Gate::authorize()` merupakan pilihan eksplisit yang sesuai dengan modul ini. `$this->authorize()` hanya dapat dipakai jika controller mewarisi/menyertakan trait `AuthorizesRequests` yang menyediakan method tersebut.

### 1.6 Mengapa `@can` saja tidak cukup?

```blade
@can('update', $course)
    <a href="{{ route('courses.edit', $course) }}">Edit</a>
@endcan
```

`@can` hanya mengatur apakah tombol/link ditampilkan di HTML. Pengguna bisa mengetik URL sendiri atau mengirim request langsung. Controller harus tetap memeriksa izin melalui `Gate::authorize()`, Policy, atau otorisasi server-side setara.

- `@can` di Blade: **tampilan/kenyamanan pengguna**.
- Policy yang diperiksa oleh controller atau route: **keamanan akses**.

### 1.7 Penyaringan daftar harus dilakukan di query

Jangan ambil seluruh data, kemudian menyembunyikannya di Blade. Data yang tidak berhak diterima tetap bocor di server/response, bisa dihitung, atau bisa terlihat akibat kesalahan view.

```php
$user = request()->user();

$query = match ($user->role) {
    'admin'     => Course::query(),
    'dosen'     => $user->taughtCourses(),
    'mahasiswa' => $user->courses(),
    default     => abort(403),
};

$courses = $query->with('lecturer')->paginate(15);
```

`match` dipilih untuk query dasar berdasarkan peran. Variabel `$query` lalu dapat dirangkai dengan `with()` dan `paginate()`. Ganti nama relasi sesuai proyek. `default => abort(403)` mencegah peran yang tidak dikenali memperoleh data.

---

## 2. READ 

### Jawaban enam pertanyaan

1. **File dan method POST login:** cek `routes/auth.php`/route login menggunakan `php artisan route:list --path=login`. Starter kit dan stack berbeda bisa menggunakan controller, action, atau komponen berbeda. Ikuti route aktual, jangan menebak nama file.
2. **Lokasi `Auth::attempt()` atau setaranya:** cari `Auth::attempt`, `authenticate()`, atau `$request->authenticate()` di `app/` dan `routes/`. Pada pola `LoginRequest`, `authenticate()` bisa memuat pemanggilan `Auth::attempt()`.
3. **Alasan `session()->regenerate()`:** membuat ID session baru setelah login untuk mencegah session fixation. Jangan menghapus langkah regenerasi starter kit.
4. **Lokasi hashing:** `app/Models/User.php`, method `casts()` dengan `'password' => 'hashed'`, atau mekanisme hashing eksplisit saat membuat/mengubah pengguna.
5. **Cookie sebelum dan sesudah login:** buka DevTools → Application/Storage → Cookies; catat nama cookie session dan bandingkan nilainya sebelum/sesudah login. Nilai sensitif jangan dipublikasikan. Jika cookie tidak tampak berubah, periksa konfigurasi dan alur autentikasi—jangan langsung menyimpulkan aman/tidak aman hanya dari tampilan cookie.
6. **Logout lalu tekan Back:** browser mungkin menunjukkan halaman yang tersimpan di cache. Namun, request baru ke route terlindungi harus gagal tanpa autentikasi. Logout harus menghapus status login, menginvalidasi session, dan membuat token CSRF baru.

Perintah pencarian yang membantu saat menelusuri kode:

```bash
php artisan route:list --path=login
rg -n "Auth::attempt|authenticate\(|session\(\)->regenerate|password.*hashed" app routes
```


---

## 3. BREAK

> Lakukan hanya pada branch/lingkungan latihan, bukan server produksi. Prediksi akibatnya terlebih dahulu, lalu pulihkan kode setelah observasi.

| # | Kerusakan | Pelajaran dan kondisi yang benar |
|---:|---|---|
| 1 | Hapus `session()->regenerate()` saat login | Menghilangkan lapisan pertahanan eksplisit terhadap session fixation. Risiko nyata bergantung pada alur auth/guard; jangan menghapus regenerasi starter kit. Pulihkan baris tersebut. |
| 2 | Hapus `Gate::authorize()` dari `update`, tetapi biarkan `@can` | Link bisa tetap tersembunyi, tetapi request langsung dapat lolos jika tidak ada otorisasi backend lain. Pulihkan pemeriksaan Policy di controller. |
| 3 | Dosen A mengirim PUT ke mata kuliah dosen B | Policy `update` harus menolak pengguna yang bukan admin atau pemilik yang sah. Jangan mengandalkan `role:dosen` saja. |
| 4 | Mahasiswa membuka submission mahasiswa lain | Policy/query harus menolak akses langsung ke submission yang bukan miliknya, kecuali peran yang memang berwenang menurut spesifikasi. Ini contoh IDOR. |
| 5 | `index` memakai `Course::paginate(15)` polos | Semua mata kuliah berpotensi tampil ke mahasiswa. Batasi query sejak awal sesuai role dan keanggotaan. |
| 6 | Kirim `role=admin` pada form edit profil | Role tidak boleh diambil dari input profil umum. Gunakan daftar field yang diizinkan dan `$request->validated()`; ubah role lewat alur admin khusus. |
| 7 | Hapus cast `hashed`, lalu simpan user baru | Tanpa hashing lain, password dapat tersimpan sebagai teks biasa. Pulihkan cast atau hashing eksplisit sebelum menyimpan. Jangan meninggalkan database latihan berisi kata sandi asli. |
| 8 | Salin cookie session ke browser lain | Siapa pun yang memperoleh cookie aktif dapat berpotensi menyamar sebagai pemiliknya. HttpOnly, Secure, SameSite, HTTPS, masa berlaku, dan logout yang benar mengurangi risiko, tetapi cookie tidak boleh dibagikan. |

---

## 5. BUILD 

- [ ] **Autentikasi lengkap:** login, logout, reset kata sandi, dan registrasi yang hanya dapat dibuat/dilakukan admin sesuai rancangan. Jangan membuka registrasi publik jika ketentuannya admin-only.
- [ ] **Tiga role:** admin, dosen, mahasiswa memiliki dashboard dan navigasi sesuai peran. Penyembunyian menu bukan pengganti keamanan backend.
- [ ] **Policy lima model:** `Course`, `Material`, `Assignment`, `Submission`, `Grade`. Aturan `viewAny`, `view`, `create`, `update`, dan `delete` diterapkan sesuai kebutuhan/matriks akses—tidak semua method harus membuka akses yang sama.
- [ ] **Otorisasi dipanggil dari backend:** gunakan `Gate::authorize()`/Policy di controller atau tempat server-side yang tepat. Blade menggunakan `@can` hanya untuk tampilan.
- [ ] **Seluruh `index` difilter di query:** admin sesuai cakupan admin; dosen hanya data yang boleh ia kelola/lihat; mahasiswa hanya data yang menjadi haknya. Terapkan aturan ke query sebelum `paginate()`.
- [ ] **`docs/keamanan.md`:** isi daftar titik rawan IDOR dari minggu 5; setiap baris menyebut Policy atau query yang menutupnya.
- [ ] **CRUD Materi dan Tugas:** operasi berjalan, validasi server-side aktif, otorisasi benar. Untuk minggu ini, upload berkas belum diwajibkan; metadata sudah cukup.
- [ ] **`scripts/test-authz.sh`:** tersedia sebagai skrip pemeriksaan otorisasi dan ekspektasi responsnya terdokumentasi.
- [ ] **Kolaborasi dan CI:** CI hijau, setiap anggota mempunyai commit, dan fitur dikerjakan lewat PR yang direview.

### Contoh tabel `docs/keamanan.md`

Isi URI, parameter, dan nama relasi berdasarkan route/model KampusLMS yang benar-benar digunakan.

| Titik rawan | Risiko | Pengaman backend yang harus dicatat |
|---|---|---|
| `GET /courses/{course}` | Mahasiswa membuka course yang tidak diikuti atau dosen membuka course milik dosen lain | `CoursePolicy::view()` dan/atau query scoped sesuai rancangan |
| `PUT /courses/{course}` | Dosen mengubah course milik dosen lain | `CoursePolicy::update()` dipanggil dengan `Gate::authorize()` |
| `GET /courses/{course}/materials` | Pengguna membaca materi dari course tanpa akses | Filter query berdasarkan course yang boleh diakses dan Policy pada objek bila diperlukan |
| `PUT/DELETE /materials/{material}` | Mengubah/menghapus materi milik pihak lain | `MaterialPolicy::update/delete()` |
| `GET /courses/{course}/assignments` | Daftar tugas course lain bocor | Query assignments melalui course yang dapat diakses; batasi status sesuai role |
| `PUT/DELETE /assignments/{assignment}` | Dosen memodifikasi tugas dosen lain | `AssignmentPolicy::update/delete()` |
| `GET /assignments/{assignment}/submissions` | Dosen melihat pengumpulan di luar kewenangannya | `SubmissionPolicy::viewAny()`/query scoped berdasarkan course yang diajar |
| `GET /submissions/{submission}` | Mahasiswa melihat submission mahasiswa lain (IDOR) | `SubmissionPolicy::view()` memeriksa pemilik serta role pengecualian yang sah |
| `PUT /submissions/{submission}/grade` | Pengguna tidak berwenang memberi/mengubah nilai | `GradePolicy` atau `SubmissionPolicy` menolak selain aktor yang berwenang |
| `POST /profile` atau `PUT /profile` | Pengguna mengirim `role=admin` | Validasi allowlist; jangan mass-assign `role`; proses perubahan role khusus admin |
| `GET /notifications/{id}` atau `POST /notifications/{id}/read` | Membaca/mengubah notifikasi orang lain | Query melalui relasi `user` yang login atau otorisasi kepemilikan |

> Status `403` atau `404` untuk objek yang bukan milik pengguna harus mengikuti kebijakan proyek. Hal yang wajib adalah data tidak dapat diakses tanpa izin.

---

## 6. Checkpoint Minggu 7

### 1. Apa beda autentikasi dan otorisasi?

Autentikasi memverifikasi identitas pengguna, misalnya login dengan email dan kata sandi. Otorisasi memutuskan tindakan yang boleh dilakukan, misalnya `CoursePolicy::update()` memeriksa apakah dosen adalah pemilik course.

### 2. Tunjukkan Policy yang ditulis dan jelaskan tiap baris

Contoh penjelasan `CoursePolicy::update()`:

```php
public function update(User $user, Course $course): bool
{
    return $user->role === 'admin'
        || $course->lecturer_id === $user->id;
}
```

- Method menerima pengguna yang login dan course yang dituju.
- `role === 'admin'` memberi izin kepada admin sesuai aturan contoh.
- `lecturer_id === user.id` memberi izin kepada dosen pemilik course.
- `||` berarti salah satu kondisi benar sudah cukup.
- Jika dua kondisi salah, method mengembalikan `false` dan tindakan harus ditolak.

### 3. Kenapa `@can` tidak cukup?

Karena Blade hanya menyembunyikan tombol. Pengguna dapat mengetik URL atau mengirim request langsung. Controller harus memanggil `Gate::authorize('update', $course)` atau pengaman server-side setara pada setiap tindakan.

### 4. Jelaskan satu baris `docs/keamanan.md` dan bukti `curl`

Contoh: dosen A mencoba mengubah course dosen B. `CoursePolicy::update()` hanya mengizinkan admin atau dosen pemilik. Backend memeriksa Policy, sehingga request dosen A ditolak—umumnya `403`, atau `404` bila desain menyamarkan keberadaan objek. Bukti curl harus menunjukkan endpoint, identitas/token atau session yang dipakai, dan status respons aktual.

### 5. Kenapa password di-hash, bukan dienkripsi? Apa dampaknya bagi lupa password?

Hashing dirancang satu arah, sehingga aplikasi tidak dapat mengembalikan kata sandi asli. Enkripsi dapat dibalik oleh pihak yang memiliki kunci. Fitur lupa password karena itu menggunakan token/tautan reset dan membuat hash baru setelah reset, bukan menampilkan kata sandi lama.

### 6. Apa fungsi `session()->regenerate()`?

Mengganti ID session setelah login agar ID sebelum autentikasi tidak terus dipakai untuk session terautentikasi, sehingga membantu mencegah session fixation.

### 7. Kenapa daftar tidak boleh diambil semua lalu disaring di view?

Karena data yang tidak berhak sudah diambil dan berpotensi ikut terkirim, dihitung, atau bocor melalui view/API lain. Query harus difilter berdasarkan role, relasi, dan kepemilikan sebelum pagination.

### 8. Tunjukkan kode yang dibantu AI. Apa yang diubah dan kenapa?

Jawab sesuai pengalaman nyata, jangan mengarang. Contoh pola jawaban:

File: app/Http/Controllers/Api/CourseController.php

```php

$courses = (match ($user->role) {
    'admin'     => Course::query(),
    'dosen'     => $user->taughtCourses(),
    'mahasiswa' => $user->courses(),
    default     => abort(403, 'Anda tidak memiliki akses ke sumber daya ini.'),
})
    ->with('lecturer')
    ->withCount(['materials', 'assignments'])
    ->latest('courses.id')
    ->paginate(15);

```

Saya menggunakan bantuan AI untuk memahami dan menyusun penyaringan data mata kuliah berdasarkan peran pengguna di backend.

Saya memastikan query-nya berbeda untuk setiap role: admin dapat mengambil semua mata kuliah, dosen hanya mengambil mata kuliah yang diajarnya, dan mahasiswa hanya mengambil mata kuliah yang diikutinya. Saya juga memastikan role yang tidak dikenal ditolak dengan status 403.

Selain itu, saya menggunakan `with('lecturer')` dan `withCount()` untuk mengambil relasi dosen serta jumlah materi dan tugas, kemudian `paginate(15)` untuk membatasi data per halaman.

Alasannya adalah agar data yang tidak berhak dilihat pengguna tidak ikut dikirim dari backend, bukan sekadar disembunyikan di halaman. Dengan begitu, risiko kebocoran data pada daftar mata kuliah bisa dikurangi.

---