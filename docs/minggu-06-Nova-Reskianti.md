# Catatan Praktikum Minggu 6 — RESTful API & Keamanan Sanctum

**Mata Kuliah:** Pemrograman Web  
**Nama:** Nova Reskianti  
**NIM:** 10241058  
**Diperbarui:** 11 Oktober 2026 — disusun ulang mengikuti struktur modul (READ → BREAK → FIX → BUILD → CHECKPOINT). Seluruh bukti di bawah diverifikasi langsung terhadap server lokal `http://127.0.0.1:8000` pada tanggal tersebut.

---

## READ — Bandingkan dua jalur (Web vs API)

### 1. Apa yang berubah setelah `php artisan install:api`?

Tiga hal sekaligus:

1. **Routing API diaktifkan** di `bootstrap/app.php`:
   ```php
   ->withRouting(
       web: __DIR__.'/../routes/web.php',
       api: __DIR__.'/../routes/api.php',   // ← prefix /api otomatis
       ...
   )
   ```
2. **Sanctum terpasang** (`composer.json`: `"laravel/sanctum": "^4.0"`) dan model `User` memakai trait `HasApiTokens`.
3. **Migrasi token dibuat & dijalankan**: `2026_10_06_035630_create_personal_access_tokens_table` (terverifikasi `migrate:status` = *Ran*).

### 2. `GET /api/v1/courses` vs `CourseController` versi web

| Aspek | Web (`CourseController@index`) | API (`Api\CourseController@index`) |
|-------|-------------------------------|-----------------------------------|
| Autentikasi | Session + cookie | Bearer token Sanctum |
| Keluaran | view Blade (`courses.index`) | `CourseResource::collection(...)` (JSON) |
| Penyaringan per peran | Tidak — daftar adalah katalog (dipakai untuk jelajah & enroll) | **Ya** — `match($user->role)` di level query |
| Pencarian & filter status | Ada (`q`, `status` di query string) | Tidak ada (fokus data) |
| Eager loading & pagination | `with('lecturer')`, `paginate(15)->withQueryString()` | `with('lecturer')->withCount([...])`, `paginate(15)` (meta custom dari `ApiResource`) |
| Rate limiting | Tidak khusus | `throttle:60,1` |

Yang **sama**: keduanya menolak akses ilegal (`abort_unless`), memakai eager loading, dan pagination.

### 3. Memanggil API **tanpa** header `Accept: application/json`

```powershell
curl.exe -i http://127.0.0.1:8000/api/v1/courses
```

**Hasil (terverifikasi):** tetap `HTTP/1.1 401 Unauthorized` berformat JSON. Sebabnya ada di `bootstrap/app.php`:

```php
$exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
    return $request->is('api/*') || $request->expectsJson();
});
```

Semua route `api/*` dipaksa menghasilkan JSON error tanpa peduli header `Accept`. Header `Accept: application/json` tetap penting untuk route **web** yang merespons JSON (metode `expectsJson()` di controller assignment/material) dan untuk memastikan klien menerima JSON di mana pun.

### 4. `route:list --path=api` cocok dengan kontrak?

Ya — terverifikasi **15 route** di bawah prefix `api/v1`:

```text
POST        api/v1/auth/login                     → Api\AuthController@login         (throttle:5,1)
POST        api/v1/auth/logout                    → Api\AuthController@logout
GET         api/v1/me                              → Api\AuthController@me
GET         api/v1/courses                         → Api\CourseController@index
GET         api/v1/courses/{id}                    → Api\CourseController@show
GET         api/v1/courses/{course}/materials      → Api\MaterialController@index
GET         api/v1/courses/{course}/assignments    → Api\AssignmentController@index
POST        api/v1/assignments                     → Api\AssignmentController@store    (201)
PUT|PATCH   api/v1/assignments/{assignment}        → Api\AssignmentController@update
DELETE      api/v1/assignments/{assignment}        → Api\AssignmentController@destroy  (204)
GET         api/v1/assignments/{assignment}/submissions → Api\SubmissionController@index
POST        api/v1/assignments/{assignment}/submissions → Api\SubmissionController@store
PUT         api/v1/submissions/{submission}/grade  → Api\SubmissionController@grade
GET         api/v1/notifications                   → Api\NotificationController@index
POST        api/v1/notifications/{id}/read         → Api\NotificationController@read
```

Seluruh route (kecuali login) dibungkus `auth:sanctum` + `throttle:60,1`. Daftar ini juga dipakai `scripts/test-api.sh` dan didokumentasikan di `docs/api.md`.

---

## BREAK — Tujuh kerusakan

> Catatan jujur: nomor 1, 3, 4, 6, 7 **benar-benar saya jalankan** dan hasilnya tercatat di bawah. Nomor 2 dan 5 tidak saya jalankan di server produksi lokal (tidak ada Telescope/Debugbar terpasang) — untuk keduanya saya catat prediksi dan mitigasi yang sudah ada di kode.

| # | Yang dirusak | Hasil pengamatan |
|---|--------------|------------------|
| 1 | Kembalikan model mentah + `$hidden` dihapus | **TERVERIFIKASI.** Dengan `$hidden` masih aktif, `password` tersembunyi — tetapi email, peran, NIM/NIP, dan timestamp tetap bocor. Setelah `$hidden` disingkirkan sesaat (`makeVisible`), hash bcrypt ikut tampil (lihat bukti di CHECKPOINT #1). |
| 2 | Hapus `auth:sanctum` dari grup route | *Prediksi (tidak dijalankan):* semua endpoint daftar/detail menjadi publik tanpa token. Kondisi saat ini aman: seluruh route satwa `auth:sanctum`. |
| 3 | Panggil endpoint terlindungi tanpa token | **TERVERIFIKASI:** `401 Unauthorized` — bahkan tanpa header `Accept` (karena `shouldRenderJsonWhen`). |
| 4 | Login sebagai mahasiswa, panggil `POST /api/v1/assignments` | **TERVERIFIKASI: `403 Forbidden`** (bukan 401 — token valid, tapi hak tidak cukup; aturan ada di `StoreAssignmentRequest::authorize()`). |
| 5 | Hapus eager loading → N+1 | *Prediksi (tidak dijalankan — tidak ada Telescope):* daftar 15 course menjadi 17 query. Mitigasi sudah ada: `with('lecturer')` + `withCount(['materials','assignments'])`. |
| 6 | Hapus `throttle` dari login | *Kebalikannya yang saya uji:* dengan `throttle:5,1` aktif, 5 percobaan login gagal berturut-turut menghasilkan `422` lalu percobaan berikutnya langsung **`429 Too Many Requests`** dengan `Retry-After: 60`. |
| 7 | Pesan login berbeda untuk email vs password salah | **TERVERIFIKASI — tidak ada enumerasi.** Email tidak terdaftar & password salah menghasilkan **422 dengan pesan identik**: `"Email atau kata sandi salah."` (seragam di `Api\AuthController@login` dan web `AuthController@login`). |

---

## FIX — Repo cacat (branch `w06`, 8 masalah)

> **Kejujuran:** repo `kampuslms-broken` tidak tersedia di workspace saya, jadi saya tidak bisa mengirim PR ke sana. Sebagai gantinya saya memetakan kedelapan masalah yang disebut modul ke kondisi **KampusLMS yang sebenarnya**:

| # | Masalah di repo cacat | Status di KampusLMS | Bukti |
|---|----------------------|---------------------|-------|
| 1 | Model mentah dikembalikan (2 endpoint) | ✅ Sudah benar — semua endpoint memakai API Resource | `app/Http/Resources/*` (ApiResource, UserResource, CourseResource, dst.) |
| 2 | Satu endpoint tanpa `auth:sanctum` | ✅ Sudah benar — seluruh route satwa grup `auth:sanctum` + `throttle:60,1` | `routes/api.php` |
| 3 | Status code salah pada `store`/`destroy` | ✅ Sudah benar — `201` untuk store, `204` (`noContent()`) untuk destroy | `Api\AssignmentController@store/@destroy`, `Api\AuthController@logout` |
| 4 | `403` dikembalikan sebagai `401` | ✅ Sudah benar — `401` hanya untuk belum login/token mati; `403` untuk hak salah | Uji: mahasiswa → `POST /assignments` = **403**; tanpa token = **401** |
| 5 | Login tanpa throttle | ✅ Sudah benar — `throttle:5,1` | `routes/api.php`; uji menghasilkan `429` |
| 6 | Pesan login membocorkan keberadaan email | ✅ Sudah benar — satu pesan seragam | `Api\AuthController@login` + `AuthController@login` (web) |
| 7 | N+1 pada endpoint daftar | ✅ Sudah benar — eager loading `with('lecturer')->withCount([...])` | `Api\CourseController@index` |
| 8 | (tambahan modul) Meta pagination tidak sesuai kontrak | ✅ Sudah benar — meta custom `current_page/last_page/total` | `app/Http/Resources/ApiResource.php::paginationInformation` |

**Kesimpulan FIX:** kedelapan masalah repo cacat sudah terwakili dan tertutup di codebase KampusLMS.

---

## BUILD — Status deliverable API KampusLMS

| # | Deliverable | Status | Keterangan |
|---|-------------|--------|------------|
| 1 | `install:api`, Sanctum terpasang | ✅ | Sanctum `^4.0`, migrasi token terjalankan |
| 2 | Endpoint sesuai kontrak Bagian 5 | ✅ | 15 route `api/v1`, format response persis kontrak |
| 3 | API Resource untuk 6 model | ✅ | User, Course, Material, Assignment, Submission, Grade (+ Notification, Login) |
| 4 | Eager loading semua endpoint koleksi | ✅ | `with(...)` + `withCount(...)` |
| 5 | Rate limiting 60/menit umum, 5/menit login | ✅ | Terverifikasi `429` |
| 6 | `docs/api.md` | ✅ | Dokumentasi endpoint + contoh `curl` |
| 7 | `scripts/test-api.sh` | ✅ | Skrip pengujian otorisasi API |
| 8 | Jalur frontend terdaftar | ✅ | `docs/jalur-frontend.md` → **Blade + Livewire** |

---

## CHECKPOINT Minggu 6

### 1. Kenapa mengembalikan model mentah berbahaya? Peragakan kebocorannya.

Mengembalikan model Eloquent langsung (`return $user` / `response()->json($user)`) berbahaya karena: (1) kolom internal ikut terkirim — email, peran, timestamp, dan (jika `$hidden` lupa didaftarkan) **hash kata sandi**; (2) penyerang belajar skema database Anda; (3) `$hidden` adalah **daftar hitam** yang bergantung ingatan developer — kolom sensitif baru langsung bocor. **API Resource adalah daftar putih**: hanya kolom yang Anda sebut yang keluar.

**Peragaan (terverifikasi via Tinker):**

```powershell
# Dengan $hidden aktif — password tersembunyi, tapi email/timestamp tetap keluar:
php artisan tinker --execute="echo json_encode(App\Models\User::first(), JSON_PRETTY_PRINT);"
```
```json
{"id":1,"name":"Admin KampusLMS","email":"admin@kampuslms.test","role":"admin","nim_nip":"ADM001","email_verified_at":"2026-10-06T23:52:56.000000Z","created_at":"2026-10-06T23:52:56.000000Z","updated_at":"2026-10-06T23:52:56.000000Z","deleted_at":null}
```

```powershell
# $hidden disingkirkan sesaat — hash bcrypt langsung bocor:
php artisan tinker --execute='$u = App\Models\User::first(); $u->makeVisible(["password","remember_token"]); echo json_encode($u, JSON_PRETTY_PRINT);'
```
```json
"password":"$2y$12$dlPOcf1VQhGeohlnh3scZ.Q2JsO7LJBA7BRXXsxIlEI3wiEScIPi6",
"remember_token":null,
```

Dengan `UserResource` yang benar, output hanya: `id`, `name`, `email`, `nim_nip`, `role`.

**File:** `app/Models/User.php`, `app/Http/Resources/UserResource.php`

### 2. Apa beda 401 dan 403? Contoh di API kita.

- **401 Unauthenticated** — identitas tidak dikenali (belum login / token tidak valid).
- **403 Forbidden** — identitas dikenali, tapi tidak berhak.

**Terverifikasi:**

```powershell
# 401 — tanpa token:
curl.exe -i http://127.0.0.1:8000/api/v1/courses -H "Accept: application/json"
# → HTTP/1.1 401 Unauthorized
```

```powershell
# 403 — token mahasiswa sah, tapi mencoba hak dosen (menilai tugas):
$token = (curl.exe -s http://127.0.0.1:8000/api/v1/auth/login -X POST -H "Content-Type: application/json" -d "{\"email\":\"mahasiswa@kampuslms.test\",\"password\":\"password\"}" | ConvertFrom-Json).data.token
curl.exe -i -X PUT http://127.0.0.1:8000/api/v1/submissions/1/grade -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d "{\"score\":90}"
# → HTTP/1.1 403 Forbidden
```

**File:** `routes/api.php` (401 via `auth:sanctum`), `app/Http/Requests/GradeSubmissionRequest.php::authorize()` (403)

### 3. Kenapa `routes/api.php` tidak ada di Laravel 12? Cara mengaktifkannya.

Laravel 12 memakai *lean skeleton*: aplikasi Blade murni tidak butuh API, jadi rute API, Sanctum, dan migrasi token ditiadakan agar bootstrap lebih ringan. Cara mengaktifkan: `php artisan install:api` — membuat `routes/api.php`, memasang Sanctum, migrasi `personal_access_tokens`, dan mendaftarkan `api: ...` di `withRouting()` `bootstrap/app.php`.

**File:** `bootstrap/app.php`

### 4. Apa fungsi `whenLoaded()`? Apa yang terjadi tanpanya?

`whenLoaded('lecturer')` hanya menyertakan relasi jika relasi itu sudah di-*eager-load* pada query. Tanpanya: N+1 (query berulang per baris) atau relasi `null`. Di `CourseResource`, kunci `lecturer` otomatis hilang dari JSON bila tidak di-load — bukan error, tapi hemat query.

```powershell
# Tanpa eager load → kunci "lecturer" tidak ada:
php artisan tinker --execute="echo json_encode(new App\Http\Resources\CourseResource(App\Models\Course::first()), JSON_PRETTY_PRINT);"
# Dengan eager load → "lecturer" muncul {id, name}:
php artisan tinker --execute="echo json_encode(new App\Http\Resources\CourseResource(App\Models\Course::with('lecturer')->first()), JSON_PRETTY_PRINT);"
```

**File:** `app/Http/Resources/CourseResource.php` (`whenLoaded('lecturer', ...)`, `whenCounted(...)`)

### 5. Kenapa pesan gagal login tidak boleh membedakan email vs password salah?

Mencegah **user enumeration**: pesan berbeda memberi penyerang daftar email valid untuk di-*brute force*. Solusi: satu pesan seragam `"Email atau kata sandi salah."` — **terverifikasi**: kedua kasus (email fiktif maupun password salah) menghasilkan **422 dengan pesan identik**.

**File:** `app/Http/Controllers/Api/AuthController.php::login()`, `app/Http/Controllers/AuthController.php::login()` (web)

### 6. Kenapa endpoint login wajib di-throttle? Nilai yang dipakai?

Melawan brute force & DoS. Nilai: **`throttle:5,1`** (5 percobaan/menit) — cukup longgar untuk manusia yang salah ketik, mematikan untuk bot. **Terverifikasi:** 5 percobaan gagal → `422` beruntun, percobaan berikutnya **`429 Too Many Requests`** + `Retry-After: 60`. Catatan: dihitung per-IP per-menit, jadi blokir bisa mulai lebih awal bila ada percobaan lain di menit yang sama.

**File:** `routes/api.php` (`Route::post('/auth/login', ...)->middleware('throttle:5,1')`)

---

**Kuis 6:** REST (resource + status code 200/201/204/401/403/422/429), API Resource sebagai daftar putih, Sanctum token Bearer, rate limiting login.
