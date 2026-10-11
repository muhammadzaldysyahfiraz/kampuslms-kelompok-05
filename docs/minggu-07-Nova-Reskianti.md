# Catatan Praktikum Minggu 7 — Autentikasi, Otorisasi, dan Policy (Milestone M2)

**Mata Kuliah:** Pemrograman Web  
**Nama:** Nova Reskianti  
**NIM:** 10241058  
**Dibuat:** 11 Oktober 2026 — mengikuti struktur modul (READ → BREAK → FIX → BUILD → CHECKPOINT). Semua bukti di bawah diverifikasi langsung terhadap server lokal `http://127.0.0.1:8000` dengan akun demo (password: `password`).

---

## READ — Bedah alur login di KampusLMS

> Catatan: kelompok kami **tidak memakai starter kit resmi**; Rifa membangun autentikasi sendiri yang lebih ringkas. Jadi yang saya bedah adalah kode nyata proyek kami.

**1. Berkas mana yang menangani POST login? Method apa?**  
`routes/web.php` → `Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')`, diproses oleh `App\Http\Controllers\AuthController::login(Request $request)` di `app/Http/Controllers/AuthController.php`.

**2. Di baris mana `Auth::attempt()` dipanggil?**  
Di dalam method `login()`, setelah `$request->validate(...)`:

```php
if (! Auth::attempt($credentials, $remember)) {
    throw ValidationException::withMessages([
        'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
    ]);
}
```

Satu pesan untuk kedua kasus — perlindungan anti *user enumeration*.

**3. Di mana `session()->regenerate()`? Kenapa ada di situ?**  
Tepat setelah `Auth::attempt()` berhasil:

```php
$request->session()->regenerate();          // ← anti session fixation
session(['active_role' => $user->role]);
return redirect()->intended(route('dashboard'));
```

Ia ada di situ karena **session fixation**: kalau ID session tidak diganti setelah login, ID yang sudah diketahui penyerang akan "naik status" menjadi session login yang sah.

**4. Di mana kata sandi di-hash?**  
`app/Models/User.php` → method `casts()`:

```php
'password' => 'hashed',
```

Dengan cast `hashed`, Laravel otomatis meng-hash saat nilai disimpan — tidak perlu `Hash::make()` manual. Saat login, `Auth::attempt()` memverifikasi lewat `Hash::check()` (bcrypt, satu arah).

**5. Cookie session sebelum & sesudah login (DevTools → Cookies)**  
**Saya verifikasi lewat curl** (setara DevTools):

```text
SEBELUM login  : eyJpdiI6ImxtUjFDdVgzRTlkL3dOaVVGZjgrOWc9...
SETELAH login  : eyJpdiI6IjNQVWFTelJiQkFlcG03eHJEdXRQSFE9...
```

Nilai cookie `laravel_session` **berubah total** setelah login — bukti `regenerate()` bekerja. Cookie session juga bersifat HttpOnly (tidak bisa dibaca JavaScript) sehingga pencurian lewat XSS jauh lebih sulit.

**6. Logout, lalu tombol *back*?**  
`AuthController::logout()` menjalankan tiga langkah:

```php
Auth::logout();
$request->session()->invalidate();        // session ID lama dibuang di server
$request->session()->regenerateToken();    // token CSRF diganti
```

Halaman lama mungkin masih muncul dari *cache browser*, tetapi session-nya sudah mati di server: request apa pun akan dialihkan ke `/login`. Itulah sebabnya tombol *back* setelah logout "terlihat ada isinya" tapi tidak bisa dipakai — bukan bug, melainkan keamanan yang bekerja.

---

## BREAK — Delapan kerusakan (status verifikasi jujur)

| # | Yang dicoba | Status | Hasil pengamatan |
|---|-------------|--------|------------------|
| 1 | Hapus `session()->regenerate()` | ✅ dibuktikan keberadaannya | Cookie session **bernilai berbeda** sebelum/sesudah login (bukti di READ #5). Tanpa `regenerate()`, nilai itu akan tetap sama → session fixation terbuka. |
| 2 | `@can` tanpa pemeriksaan server | ✅ **dibuktikan — dan menemukan temuan nyata** | Lihat kotak "Temuan Penting" di bawah. |
| 3 | Dosen A mengedit mata kuliah dosen B | ✅ diverifikasi | `GET /dosen/courses/1/edit` (course milik dosen B, id 3) sebagai dosen A (id 2) → **403**. `PUT` via API ke tugas dosen B → **403**. Sebaliknya, course miliknya sendiri → **200**. |
| 4 | Mahasiswa membuka submission mahasiswa lain | ✅ diverifikasi (IDOR tertutup) | `GET /submissions/1` (milik user 10) sebagai mahasiswa demo (user 5) → **403**. `GET /submissions/2` (miliknya sendiri) → **200**. Via API: `PUT /api/v1/submissions/1/grade` sebagai mahasiswa → **403**. |
| 5 | `Course::paginate()` polos di index | ✅ diamati | **Temuan:** web `CourseController@index` saat ini memang menampilkan **semua** mata kuliah ke semua peran (halaman katalog untuk fitur enroll). API `CourseController@index` sudah benar: disaring per peran dengan `match($user->role)`. Saat wawancara, saya harus bisa menjelaskan perbedaan ini: katalog boleh terbuka, tapi **endpoint data pribadi (submission/grade) wajib disaring di query** — dan itu sudah kami lakukan. |
| 6 | Kirim `role=admin` lewat update data user | ✅ diamati | `User::$fillable` **memuat `role`** — ini risiko mass assignment. Saat ini aman karena satu-satunya pintu edit user adalah `UpdateUserRequest` (khusus admin, `role` divalidasi `in:admin,dosen,mahasiswa`). Pelajaran: jangan pernah `$user->update($request->all())`. |
| 7 | Hilangkan cast `hashed` | ✅ dibuktikan dari sisi lain | Kolom password di database berbentuk bcrypt: `$2y$12$dlPOcf1VQhGeohlnh3scZ...` (cost 12). Tanpa cast itu, string polos akan tersimpan apa adanya. |
| 8 | Salin cookie session ke browser lain | ✅ diverifikasi pergantian nilai | Karena `regenerate()` + `invalidate()`, cookie lama tidak berlaku setelah login/logout berikutnya. Nilai cookie bersifat HttpOnly & (di produksi) Secure — lihat `config/session.php`. |

> Nomor 2, 3, 4, 7 dijalankan dengan `curl` sungguhan; nomor 1, 5, 6, 8 diamati dari kode + percobaan terbatas. Repo `kampuslms-broken` tidak tersedia di workspace saya, jadi bagian FIX memakai pemetaan ke codebase nyata (sama seperti catatan minggu 6).

### ⚠️ Temuan Penting dari Break #2 — "tombol tersembunyi ≠ akses tertutup" (nyata di kode kami)

Ini pelajaran terpenting minggu ini, dan **kejadiannya persis di aplikasi kami sendiri**:

- `CoursePolicy::update` → **hanya admin** (komentar di kode: *"dosen tidak boleh mengubah mata kuliah hanya karena ia mengajarnya"* — sesuai matriks).
- Karena itu, di `courses/show.blade.php`, dosen pengampu **tidak melihat tombol "Edit Mata Kuliah"** (`@can('update', $course)` bernilai false).
- **TETAPI** `CourseController::edit/update` memakai `authorizeCourseManager()` yang mengizinkan **admin ATAU dosen pengampu** → dosen A membuka `/dosen/courses/2/edit` langsung di address bar → **200, form edit terbuka**.

Jadi: Policy bilang *dilarang*, controller bilang *boleh*, Blade menyembunyikan tombolnya. **Inilah peragaan sempurna bahwa `@can` hanya menyembunyikan UI** — keputusan sesungguhnya ada di controller/Policy, dan keduanya saat ini **tidak konsisten**. Perbaikannya: pilih satu sumber kebenaran dan pakai `Gate::authorize('update', $course)` di controller agar Policy-lah yang berlaku. (Status: temuan untuk didiskusikan kelompok sebelum interview — saya laporkan apa adanya, bukan saya ubah sepihak.)

Pola serupa ada di `materials/show.blade.php` (`@can('update', $material->course)`) — tombol edit materi tersembunyi dari dosen pengampu padahal `MaterialPolicy::update` mengizinkannya.

---

## FIX — Repo cacat branch `w07` (9 masalah) → pemetaan ke KampusLMS

| # | Masalah di repo cacat | Status di KampusLMS | Keterangan |
|---|----------------------|---------------------|------------|
| 1 | `@can` tanpa pemeriksaan controller (2 controller) | ⚠️ **sebagian** | Controller sudah memakai `abort_unless` kepemilikan (bukan sekadar `@can`), tetapi **belum `Gate::authorize()`** — ada komentar `TODO minggu 7: dipindah ke Policy` di `Api\SubmissionController`, `Api\CourseController`, `GradeSubmissionRequest`, dsb. Belum konsisten. |
| 2 | Policy selalu `return true` | ✅ benar | Semua Policy punya logika nyata (`match($user->role)`, `isTaughtBy`, `hasStudent`). |
| 3 | Kebocoran daftar untuk mahasiswa | ✅ tertutup di endpoint data | `SubmissionController@index` & `GradeController@indexMine` menyaring di **query** (`where('user_id', ...)`, `whereHas`). Daftar course web bersifat katalog (lihat BREAK #5). |
| 4 | `role` bisa diubah lewat update profil | ⚠️ **waspada** | Tidak ada endpoint profil publik; satu-satunya pintu `UpdateUserRequest` (admin, tervalidasi `in:`). Tapi `role` ada di `$fillable` — jangan sampai ada `$user->update($request->all())`. |
| 5 | `session()->regenerate()` hilang | ✅ ada | `AuthController@login` (web). |
| 6 | Pesan login membocorkan keberadaan email | ✅ benar | Satu pesan seragam di web & API. |
| 7 | Route dosen tanpa middleware | ✅ benar | Seluruh route dosen berada dalam grup `Route::middleware('role:dosen')->prefix('dosen')` di `routes/web.php`; alias `role` terdaftar di `bootstrap/app.php` (**bukan** `Kernel.php` — berkas itu memang tidak ada di Laravel 12). |
| 8 | Policy menyebabkan N+1 | ⚠️ perlu pengamatan | `SubmissionPolicy::view` menelusuri `$submission->assignment->course` — aman untuk satu objek, tapi berbahaya bila dipakai massal tanpa eager loading. Saat ini policy dipanggil per-objek, bukan per-daftar. |
| 9 | (sembilan) Ringkasan keseluruhan | ⚠️ tugas tersisa | Pekerjaan besar yang belum selesai: **migrasi `abort_unless` → `Gate::authorize()`** di seluruh controller + pembuatan `docs/keamanan.md` dan `scripts/test-authz.sh`. |

---

## BUILD — Status deliverable Milestone M2 (jujur, 11 Okt 2026)

| # | Deliverable Tugas 2 | Status | Keterangan |
|---|---------------------|--------|------------|
| 1 | Autentikasi lengkap | ⚠️ | Login/logout ✅ (web + API). Register khusus admin ✅ (lewat `/admin/users`). **Reset kata sandi ❌ belum ada.** |
| 2 | Tiga peran + dashboard berbeda | ✅ | Dashboard membedakan tampilan per peran; simulasi `switch-role` khusus lingkungan dev. |
| 3 | Policy untuk 5 model, dipakai di controller **dan** Blade | ⚠️ | 5 Policy ada (Course, Material, Assignment, Submission, Grade) ✅ dan dipakai di Blade via `@can` ✅. **Di controller: masih `abort_unless` + `TODO` — `Gate::authorize()` belum dipakai di mana pun.** |
| 4 | Seluruh index disaring per peran | ⚠️ | API courses ✅ (`match` per peran). Submissions & grades ✅ (query `where`/`whereHas`). Courses web = katalog terbuka (perlu keputusan kelompok). |
| 5 | `docs/keamanan.md` (Tabel Titik Rawan IDOR) | ❌ | **Belum dibuat.** Baris pertama yang siap dimasukkan — *lihat kotak di bawah*. |
| 6 | CRUD Materi & Tugas dengan otorisasi | ✅ | Berjalan dan terverifikasi: dosen pengampu bisa CRUD (200), dosen lain ditolak (403), mahasiswa ditolak dari operasi tulis (403). 32 test lulus (144 assertions). |
| 7 | `scripts/test-authz.sh` | ❌ | **Belum ada** (baru `scripts/test-api.sh` dari minggu 6). |
| 8 | CI hijau, commit per anggota, PR direview | ✅ | `.github/workflows/ci.yml` ada; PR frontend saya sudah di-merge ke `main`. |

### Draft baris pertama untuk `docs/keamanan.md` (siap tempel saat file itu dibuat)

| Titik rawan (URL) | Ancaman | Penutup | Bukti |
|--------------------|---------|---------|-------|
| `GET /submissions/{id}` (web) & `PUT /api/v1/submissions/{id}/grade` | IDOR — mahasiswa membaca/menilai data milik orang lain | `SubmissionController::show` → `abort_unless` (pemilik ∥ admin ∥ dosen pengampu); `GradeSubmissionRequest::authorize()` | `curl` di CHECKPOINT #4 |

---

## CHECKPOINT Minggu 7 — Gerbang Interview Tugas 2

### 1. Apa beda autentikasi dan otorisasi? Contoh di kode.

- **Autentikasi = siapa Anda.** Contoh: `Auth::attempt($credentials)` di `AuthController@login` (web) dan penerbitan token `createToken()` di `Api\AuthController@login` (API).
- **Otorisasi = apa yang boleh Anda lakukan.** Contoh: `SubmissionPolicy::view()` — mahasiswa hanya boleh melihat submission miliknya; dosen hanya submission di mata kuliah yang diajar; `GradeSubmissionRequest::authorize()` menolak mahasiswa menilai (403).

Aplikasi yang autentikasinya sempurna tapi otorisasinya bolong **lebih berbahaya** — memberi rasa aman palsu.

### 2. Jelaskan satu Policy baris per baris.

Saya jelaskan `SubmissionPolicy` (`app/Policies/SubmissionPolicy.php`) — Policy yang paling saya kuasai karena menutup IDOR minggu 5:

```php
public function viewAny(User $user): bool
{
    return true;   // daftar boleh dilihat semua yang login, TAPI daftarnya disaring di query
}
```
```php
public function view(User $user, Submission $submission): bool
{
    return match ($user->role) {
        'admin'     => true,                                          // admin melihat semua
        'dosen'     => $submission->assignment->course->isTaughtBy($user), // dosen hanya MK yang diajar
        'mahasiswa' => $submission->user_id === $user->id,            // mahasiswa hanya miliknya sendiri
        default     => false,                                         // peran tak dikenal → ditolak (bukan error 500)
    };
}
```
```php
public function create(User $user, Assignment $assignment): bool
{
    return $user->role === 'mahasiswa'
        && $assignment->course->hasStudent($user);   // hanya mahasiswa terdaftar di MK itu
}
```

Tiga detail penting: (1) `default => false` wajib ada agar peran tak dikenal ditolak rapi; (2) cek keanggotaan memakai `hasStudent()` yang memakai `exists()` — hemat query, bukan memuat seluruh koleksi; (3) `viewAny` sengaja `true` karena penyaringan daftar dilakukan di **query**, bukan di view.

### 3. Kenapa `@can` di Blade tidak cukup? Peragakan.

`@can` hanya menampilkan/menyembunyikan tombol — **ia bukan pengaman**. Peragaan nyata dari aplikasi kami (BREAK #2): dosen pengampu **tidak melihat** tombol "Edit Mata Kuliah" (karena `CoursePolicy::update` = admin-only), **tetapi** membuka `/dosen/courses/2/edit` langsung di address bar tetap menghasilkan **200** — form edit terbuka. Yang menentukan aman-tidaknya adalah pemeriksaan di controller/Policy, dan saat itu `authorizeCourseManager()` (controller) **berbeda keputusan** dari `CoursePolicy`. Pelajarannya: selalu pasang `Gate::authorize()` di controller, samakan dengan Policy, dan `@can` hanya untuk kenyamanan UI.

### 4. `docs/keamanan.md` — satu baris + bukti `curl`.

> **Kejujuran:** `docs/keamanan.md` **belum ada** di repo kami (deliverable yang harus saya selesaikan). Saya gunakan baris yang sudah saya siapkan untuk file itu (lihat kotak draft di bagian BUILD).

Baris: *`GET /submissions/{id}` — mahasiswa membuka submission mahasiswa lain → DITUTUP oleh `SubmissionController::show` (`abort_unless` pemilik/admin/dosen pengampu).*  

Bukti (terverifikasi, mahasiswa demo = user 5, submission 1 milik user 10, submission 2 milik user 5):

```powershell
# login simulasi (lingkungan dev)
curl.exe -s -c cookies.txt http://127.0.0.1:8000/switch-role/mahasiswa -L -o $null

# submission milik orang lain → DITOLAK
curl.exe -s -b cookies.txt -o $null -w "%{http_code}" http://127.0.0.1:8000/submissions/1
# → 403

# submission milik sendiri → BOLEH
curl.exe -s -b cookies.txt -o $null -w "%{http_code}" http://127.0.0.1:8000/submissions/2
# → 200
```

Versi API (token Sanctum):

```powershell
$token = (curl.exe -s http://127.0.0.1:8000/api/v1/auth/login -X POST -H "Content-Type: application/json" -d "{\"email\":\"mahasiswa@kampuslms.test\",\"password\":\"password\"}" | ConvertFrom-Json).data.token
curl.exe -s -X PUT http://127.0.0.1:8000/api/v1/submissions/1/grade -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d "{\"score\":99}" -o $null -w "%{http_code}"
# → 403 (mahasiswa tidak berhak menilai)
```

### 5. Kenapa kata sandi di-hash, bukan dienkripsi? Konsekuensi untuk "lupa password"?

Hash bersifat **satu arah** — tidak ada kunci untuk mengembalikan teks asli. Enkripsi justru bisa dibalik, sehingga server akan mampu "mengirim ulang password lama" — satu kebocoran database langsung membuka semua akun. Konsekuensinya: fitur lupa password **tidak boleh** menampilkan password lama; harus **mereset** lewat token sekali pakai (tautan reset yang kedaluwarsa). Di kode kami: cast `'password' => 'hashed'` (bcrypt cost 12 — sengaja lambat untuk memperlambat brute force).

### 6. Apa fungsi `session()->regenerate()` saat login?

Mencegah **session fixation**: penyerang menanamkan ID session tertentu pada korban (mis. lewat tautan), lalu menunggu korban login. Tanpa `regenerate()`, ID yang sama otomatis menjadi session login yang sah — penyerang tinggal memakai ID lamanya. Dengan `regenerate()`, ID session diganti total (terverifikasi: nilai cookie `laravel_session` berubah sebelum/sesudah login). Pasangannya saat logout: `invalidate()` + `regenerateToken()`.

### 7. Kenapa daftar tidak boleh diambil semua lalu disaring di view?

1. **Keamanan:** data milik orang lain sudah meninggalkan database dan masuk ke memori aplikasi — satu lupa menyaring di view (atau error yang menampilkan variabel) langsung bocor.  
2. **Performa:** semua baris dimuat lalu dibuang — mahal dan menghancurkan pagination yang benar.  
3. **Ketepatan:** menyaring di view tidak mengubah `total` pagination sehingga angka di layar menyesatkan.  

Contoh benar di kode kami — `Api\CourseController@index`:

```php
$courses = (match ($user->role) {
    'admin'     => Course::query(),
    'dosen'     => $user->taughtCourses(),
    'mahasiswa' => $user->courses(),
    default     => abort(403, ...),
})->with('lecturer')->withCount([...])->paginate(15);
```

Dua jebakan yang dihindari di sini: **kurung** di sekeliling `match` wajib (hasil `match` tidak bisa langsung dirantai `->`), dan **`default` wajib** (peran tak dikenal → 403 rapi, bukan `UnhandledMatchError`/500).

### 8. Satu bagian yang ditulis dengan bantuan AI. Apa yang saya ubah, dan kenapa?

**Bagian:** tampilan Blade CRUD Tugas & Materi (`assignments/create|edit|show`, `materials/create|edit`) dan tombol aksi di `courses/show` — saya deskripsikan kebutuhannya (field, validasi, gaya), AI menulis draft-nya.  

**Yang saya ubah setelah mereview, dua hal penting:**

1. **Nama route** disesuaikan dengan `routes/web.php` hasil kerja tim (`courses.assignments.create`, `courses.materials.update`, dst.) — output AI memakai nama route generik yang tidak ada di proyek; tanpa penyesuaian ini halaman error 500/RouteNotFound.  
2. **Direktif Policy diperbaiki:** draft awal memakai `@can('update', $course)` untuk tombol "Tambah Tugas/Materi". Setelah saya cek `CoursePolicy`, aturan `update` ternyata **admin-only** — tombol akan hilang dari dosen pengampu, padahal `AssignmentPolicy::create`/`MaterialPolicy::create` **mengizinkan** dosen pengampu. Saya ganti menjadi `@can('create', [Assignment::class, $course])`, lalu **membuktikannya di browser**: sebagai dosen tombol muncul, sebagai mahasiswa tidak; test suite tetap 32 lulus.

**Pelajaran inti:** output AI selalu harus dicek terhadap **aturan nyata di codebase** (Policy, route, konvensi) — bukan sekadar terlihat benar. AI menulis sintaks; keputusan otorisasi tetap tanggung jawab saya.

---

**Kuis 7:** autentikasi vs otorisasi, Policy per-objek vs penyaringan daftar di query, hashing satu arah, `regenerate()`/`invalidate()` anti fixation, `Gate::authorize()` vs `@can`.
