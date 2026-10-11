# Dokumen Analisis Keamanan & Titik Rawan IDOR KampusLMS

> **Mata Kuliah:** Pemrograman Web (SI2514024) — Semester Ganjil 2026/2027  
> **Kelompok:** 05 — Aplikasi LMS Kampus (*KampusLMS*)  
> **Anggota Tim Pengembang:**  
> 1. Muhammad Zaldy Syahfiraz (10241052)  
> 2. Muhammad Yuspa Ardiansyah (10241054)  
> 3. Muhammad Rifa Al-Rizqul (10241050)  
> 4. Nova Reskianti (10241058)  
>
> **Tujuan Dokumen:**  
> Panduan resmi arsitektur keamanan, mitigasi *Insecure Direct Object References* (IDOR), matriks otorisasi per peran, dan skrip pembuktian langsung (*live penetration testing*) menggunakan `curl` untuk evaluasi Ujian Tengah Semester (UTS - Bagian 3: Uji Keamanan Langsung).

---

## 1. Arsitektur Keamanan & Prinsip *Defense in Depth*

Aplikasi **KampusLMS** menerapkan strategi keamanan berlapis (*defense-in-depth*) berbasis fitur inti **Laravel 12**, memadukan proteksi di level jaringan/rute, kontroler, model, hingga basis data:

```
[ HTTP Request ]
       │
       ▼
1. Web/API Route Middleware
   ├── CSRF Protection (Web Session) / Sanctum Token Guard (API)
   ├── Rate Limiting (Throttle 5/min Login, 60/min API)
   └── Role-Based Access Control (role:admin,dosen,mahasiswa)
       │
       ▼
2. Scoped Route Model Binding (Route::scopeBindings())
   └── Memverifikasi bahwa child model (assignment/material) adalah milik parent course (Cross-Course IDOR -> 404)
       │
       ▼
3. Controller-Level Authorization & FormRequest
   ├── Gate::authorize() / abort_unless()
   ├── FormRequest::authorize() (misal: GradeSubmissionRequest)
   └── Validasi ketat allowlist ($request->validated())
       │
       ▼
4. Laravel 12 Authorization Policies
   └── CoursePolicy, AssignmentPolicy, MaterialPolicy, SubmissionPolicy, GradePolicy
       │
       ▼
5. Database Query-Level Scoping
   └── Filter data berdasarkan role pada level SQL Query SEBELUM paginate() (bukan dimuat lalu disaring di Blade)
```

### 7 Pilar Pertahanan Utama:
1. **Pencegahan Session Fixation (`session()->regenerate()`)**:  
   Saat login berhasil, ID session di-regenerasi untuk menggugurkan token session lama. Saat logout, dipanggil `session()->invalidate()` dan `session()->regenerateToken()`.
2. **Kriptografi Kata Sandi Satu Arah (`bcrypt cost 12`)**:  
   Kata sandi di-hash menggunakan algoritma Bcrypt (bukan enkripsi dua arah). Fitur lupa sandi menggunakan token acak temporer kedaluwarsa, server tidak pernah menyimpan atau mengirim ulang kata sandi asli.
3. **Pemisahan Autentikasi vs Otorisasi**:  
   - *Autentikasi* membuktikan identitas pengguna (`Auth::attempt`, Sanctum Bearer Token).  
   - *Otorisasi* menentukan izin akses terhadap objek spesifik (`Gate::authorize`, Laravel Policy).
4. **Keamanan Sisi Server vs Tampilan Blade (`Gate::authorize()` vs `@can`)**:  
   Direktif `@can` di Blade hanyalah kenyamanan UI (menyembunyikan tombol). Seluruh rute dan aksi di kontroler tetap diwajibkan melakukan otorisasi sisi server (`abort_unless` / `Gate::authorize`), sehingga manipulasi URL via address bar atau `curl` tetap tertahan di HTTP 403.
5. **Implicit Scoped Route Binding (`Route::scopeBindings()`)**:  
   Mencegah *Cross-Course IDOR*. Mengakses tugas ID 1 milik Mata Kuliah 1 melalui URL Mata Kuliah 2 (`/courses/2/assignments/1`) otomatis dihentikan oleh Laravel dengan kode **HTTP 404 Not Found**, karena sistem memverifikasi relasi integritas antar-model.
6. **Proteksi *Mass Assignment***:  
   Atribut kritis seperti `role` tidak dimasukkan ke dalam `$fillable` pada pembaruan publik/profil. Pengubahan peran hanya dapat dieksekusi secara eksplisit oleh Administrator melalui formulir tersanitasi.
7. **Penyaringan Kueri di Sumber (*Database-Level Scoping*)**:  
   Daftar data (*index*) tidak pernah dimuat seluruhnya ke memori lalu disaring di Blade (yang boros memori dan rentan bocor). Kueri dibatasi di level Eloquent SQL (`where`, `whereHas`, `isTaughtBy`) sebelum pagination.

---

## 2. Tabel Resmi Titik Rawan IDOR & Matriks Mitigasi

Berikut adalah daftar titik rawan yang didokumentasikan dan diuji secara menyeluruh pada KampusLMS:

| No | Titik Rawan (Endpoint) | Metode | Skenario Penyerangan (Ancaman IDOR) | Aktor Penguji | Lapis Pertahanan / Mitigasi Backend | Ekspektasi Respons | Bukti Uji `curl` |
|:--:|:-----------------------|:------:|:------------------------------------|:--------------|:------------------------------------|:------------------:|:-----------------|
| **1** | `/submissions/{submission}` | `GET` | Mahasiswa mencoba membaca tugas/submission milik mahasiswa lain dengan mengganti parameter ID. | Mahasiswa (User 5) membuka Subm. 79 (User 6) | `SubmissionController@show` & `SubmissionPolicy@view` (`user_id === auth()->id()`) | **403 Forbidden** | Skenario 1 |
| **2** | `/api/v1/submissions/{id}/grade` | `PUT` | Mahasiswa mencoba memberi nilai pada submission miliknya sendiri atau milik temannya. | Mahasiswa (User 5) mencoba menilai Subm. 1 | `GradeSubmissionRequest::authorize()` & `GradePolicy@create/update` (hanya dosen pengampu) | **403 Forbidden** | Skenario 2 |
| **3** | `/assignments/{assignment}` | `PUT` / `DELETE` | Dosen mencoba mengedit atau menghapus tugas dari mata kuliah milik dosen lain (*Cross-Lecturer IDOR*). | Dosen A (User 2) mengedit Tugas 1 (diampu Dosen B / User 4) | `AssignmentController::ensureAssignmentManager` & `AssignmentPolicy@update/delete` | **403 Forbidden** | Skenario 3 |
| **4** | `/materials/{material}` | `PUT` / `DELETE` | Dosen mencoba memodifikasi atau menghapus materi kuliah milik dosen lain. | Dosen A (User 2) menghapus Materi 1 (diampu Dosen B / User 4) | `MaterialController::ensureMaterialManager` & `MaterialPolicy@update/delete` | **403 Forbidden** | Skenario 4 |
| **5** | `/courses/{course}/assignments/{assignment}` | `GET` | Penyerang mencoba memanggil penugasan milik MK-A melalui konteks URL MK-B (*Cross-Course IDOR*). | Mahasiswa/Dosen memanggil `/courses/2/assignments/1` | `Route::scopeBindings()` & `AssignmentController@showNested` verifikasi `assignment->course_id === course->id` | **404 Not Found** | Skenario 5 |
| **6** | `/courses/{course}/materials/{material}` | `GET` | Penyerang mencoba memanggil materi milik MK-A melalui konteks URL MK-B (*Cross-Course IDOR*). | Mahasiswa/Dosen memanggil `/courses/2/materials/1` | `Route::scopeBindings()` & `MaterialController@showNested` verifikasi `material->course_id === course->id` | **404 Not Found** | Skenario 6 |
| **7** | `/courses/{course}` | `PUT` / `DELETE` | Dosen atau Mahasiswa mencoba mengubah metadata atau menghapus data mata kuliah master. | Dosen (User 2) atau Mahasiswa (User 5) mengubah MK 1 | `CoursePolicy@update` & `CoursePolicy@delete` (dibatasi `role === 'admin'`) | **403 Forbidden** | Skenario 7 |
| **8** | `/users` atau `/admin/users` | `GET` / `POST` | Pengguna selain admin mencoba mengakses daftar seluruh pengguna atau mendaftarkan akun baru. | Dosen (User 2) atau Mahasiswa (User 5) | Rute dilindungi Middleware `role:admin` pada grup rute web. | **403 Forbidden** | Skenario 8 |

---

## 3. Panduan Pengujian Langsung (*Live Testing Cheatsheet*)

Pengujian dapat dijalankan menggunakan **PowerShell** (Windows) maupun **Bash** (Linux/macOS). Pastikan server pengembangan lokal aktif:
```powershell
php artisan serve
# Berjalan di http://127.0.0.1:8000
```

### Akun Referensi Database Demo (Hasil `migrate:fresh --seed`):
- **Admin**: `admin@kampuslms.test` / `password` (User ID: 1)
- **Dosen Demo (Pengampu MK 2 & 3)**: `dosen@kampuslms.test` / `password` (User ID: 2)
- **Dosen Lain (Pengampu MK 1)**: `sidney.koss@example.org` / `password` (User ID: 4)
- **Mahasiswa Demo**: `mahasiswa@kampuslms.test` / `password` (User ID: 5, Pemilik Submission 1)
- **Mahasiswa Lain**: `concepcion.dubuque@example.net` / `password` (User ID: 6, Pemilik Submission 79)

---

### Skenario 1: Uji IDOR Akses Submission Mahasiswa Lain
**Deskripsi:** Mahasiswa demo (User 5) mencoba mengakses halaman detail submission milik mahasiswa lain (Submission 79).
- **Perintah PowerShell:**
```powershell
# 1. Login sebagai mahasiswa demo via switch-role (simpan cookie sesi)
curl.exe -s -c cookie-mhs.txt http://127.0.0.1:8000/switch-role/mahasiswa -L -o $null

# 2. Coba akses Submission 79 milik orang lain -> HARUS DITOLAK (403)
curl.exe -s -b cookie-mhs.txt -o $null -w "Status IDOR (Orang Lain): %{http_code}`n" http://127.0.0.1:8000/submissions/79

# 3. Akses Submission 1 milik sendiri -> HARUS BOLEH (200)
curl.exe -s -b cookie-mhs.txt -o $null -w "Status Hak Akses (Milik Sendiri): %{http_code}`n" http://127.0.0.1:8000/submissions/1
```
- **Hasil:**
  - Submission orang lain: **HTTP 403 Forbidden**
  - Submission milik sendiri: **HTTP 200 OK**

---

### Skenario 2: Uji IDOR Penilaian Submission via REST API
**Deskripsi:** Mahasiswa memperoleh token API Sanctum dan mencoba mengubah/memberi nilai submission tugas.
- **Perintah PowerShell:**
```powershell
# 1. Ambil token Sanctum mahasiswa demo
$tokenMhs = (curl.exe -s http://127.0.0.1:8000/api/v1/auth/login -X POST -H "Content-Type: application/json" -d "{\`"email\`":\`"mahasiswa@kampuslms.test\`",\`"password\`":\`"password\`"}" | ConvertFrom-Json).data.token

# 2. Mahasiswa mencoba menilai submission 1 -> HARUS DITOLAK (403)
curl.exe -s -X PUT http://127.0.0.1:8000/api/v1/submissions/1/grade `
  -H "Authorization: Bearer $tokenMhs" `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d "{\`"score\`":95,\`"feedback\`":\`"Bagus\`"}" `
  -o $null -w "Status Nilai oleh Mahasiswa: %{http_code}`n"
```
- **Hasil:** **HTTP 403 Forbidden** (Tertahan oleh `GradeSubmissionRequest::authorize()`).

---

### Skenario 3: Uji Cross-Lecturer IDOR pada Tugas (Assignments)
**Deskripsi:** Dosen demo (User 2) hanya mengampu MK 2 dan MK 3. Tugas 1 adalah milik MK 1 (diampu Dosen User 4). Dosen demo mencoba mengedit Tugas 1.
- **Perintah PowerShell:**
```powershell
# 1. Login sebagai Dosen Demo
curl.exe -s -c cookie-dosen.txt http://127.0.0.1:8000/switch-role/dosen -L -o $null

# 2. Dosen mencoba update Tugas 1 (Mata Kuliah milik Dosen Lain) -> HARUS DITOLAK (403)
curl.exe -s -b cookie-dosen.txt -X PUT http://127.0.0.1:8000/assignments/1 `
  -H "Accept: application/json" `
  -d "title=Pembajakan+Tugas" `
  -o $null -w "Status Update Tugas Dosen Lain: %{http_code}`n"

# 3. Dosen mencoba update Tugas 4 (Mata Kuliah sendiri / MK 2) -> HARUS DIIZINKAN (302/200)
curl.exe -s -b cookie-dosen.txt -X PUT http://127.0.0.1:8000/assignments/4 `
  -H "Accept: application/json" `
  -d "title=Tugas+Pertemuan+1+Resmi" `
  -o $null -w "Status Update Tugas Sendiri: %{http_code}`n"
```
- **Hasil:**
  - Tugas milik dosen lain: **HTTP 403 Forbidden**
  - Tugas mata kuliah sendiri: **HTTP 200 OK / 302 Redirect**

---

### Skenario 4: Uji Cross-Course Scoped Binding IDOR (`Route::scopeBindings()`)
**Deskripsi:** Penyerang mencoba memanipulasi URL bersarang dengan memasangkan Mata Kuliah 2 dengan Tugas 1 (padahal Tugas 1 sebenarnya milik Mata Kuliah 1).
- **Perintah PowerShell:**
```powershell
# Akses pasangan tidak sah: Course 2 + Assignment 1 -> HARUS 404
curl.exe -s -b cookie-dosen.txt -o $null -w "Status Cross-Course Assignment: %{http_code}`n" http://127.0.0.1:8000/courses/2/assignments/1

# Akses pasangan sah: Course 1 + Assignment 1 -> HARUS 200
curl.exe -s -b cookie-dosen.txt -o $null -w "Status Valid Course Assignment: %{http_code}`n" http://127.0.0.1:8000/courses/1/assignments/1
```
- **Hasil:**
  - Pasangan silang (*Cross-Course*): **HTTP 404 Not Found** (Model binding gagal mencocokkan kepemilikan relasi).
  - Pasangan valid: **HTTP 200 OK**.

---

### Skenario 5: Uji Hak Akses Pengelolaan Pengguna (`/users`)
**Deskripsi:** Mahasiswa atau Dosen mencoba mengakses modul administrasi pengguna master (`/users`).
- **Perintah PowerShell:**
```powershell
# Akses rute pengguna dengan sesi Mahasiswa -> HARUS DITOLAK (403)
curl.exe -s -b cookie-mhs.txt -o $null -w "Status Akses /users oleh Mahasiswa: %{http_code}`n" http://127.0.0.1:8000/users

# Akses rute pengguna dengan sesi Dosen -> HARUS DITOLAK (403)
curl.exe -s -b cookie-dosen.txt -o $null -w "Status Akses /users oleh Dosen: %{http_code}`n" http://127.0.0.1:8000/users
```
- **Hasil:** **HTTP 403 Forbidden** (Tertahan oleh Middleware `role:admin`).

---

## 4. Evaluasi Mandiri Kesiapan Uji Keamanan (UTS Bagian 3)

| Pertanyaan Penguji yang Diantisipasi | Jawaban Teknis & Lokasi Kode |
|:---|:---|
| **"Apa yang terjadi jika mahasiswa menebak ID submission di URL?"** | Request tertahan di `SubmissionController@show` baris 37-45 dan `SubmissionPolicy@view`. Aplikasi memeriksa apakah `submission->user_id === user->id`. Jika tidak sama dan bukan admin/dosen pengampu, Laravel langsung mengeksekusi `abort(403)`. |
| **"Mengapa scoped binding penting pada route nested seperti `courses/{course}/assignments/{assignment}`?"** | Tanpa `scopeBindings()`, Laravel hanya memeriksa apakah `{course}` ada dan `{assignment}` ada secara terpisah. Penyerang dapat menyusupkan penugasan dari kelas rahasia melalui URL kelas publik. Dengan `scopeBindings()`, Laravel otomatis menyisipkan kueri relasi SQL `WHERE course_id = ?`, mengembalikan **404 Not Found** jika tidak berelasi. |
| **"Apakah tombol di Blade yang disembunyikan dengan `@can` sudah cukup aman?"** | **Tidak cukup.** `@can` hanya mengontrol elemen visual di DOM browser. Penyerang dapat mengirimkan request HTTP mentah langsung ke server via `curl` atau Postman. Keamanan sejati ditegakkan di kontroler melalui `Gate::authorize()` atau pemanggilan Policy backend. |
| **"Bagaimana mencegah privilege escalation jika penyerang mengirimkan payload `role=admin` pada form registrasi/profil?"** | Kolom `role` dilindungi dari *Mass Assignment* dengan tidak membiarkannya diisi sembarangan via `$request->all()`. Kontroler hanya mengambil data dari `$request->validated()` atau `$request->safe()`, dan manipulasi peran hanya dapat dilakukan oleh Admin di rute `/admin/users`. |

---

*Dokumen ini dibuat dan divalidasi pada seluruh lingkungan pengujian lokal KampusLMS Kelompok 05.*
