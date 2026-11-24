# Dokumen Analisis Keamanan & Titik Rawan IDOR KampusLMS

> **Mata Kuliah:** Pemrograman Web (SI2514024) — Semester Ganjil 2026/2027  
> **Kelompok:** 05 — Aplikasi LMS Kampus (*KampusLMS*)  
> **Anggota Tim Pengembang:**  
> 1. Muhammad Zaldy Syahfiraz (10241052)  
> 2. Muhammad Yuspa Ardiansyah (10241054)  
> 3. Muhammad Rifa Al-Rizqul (10241050)  
> 4. Nova Reskianti (10241058)  
> 5. Muhammad Farin Murtadho Syafiq (10241046)  
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

Berikut adalah daftar titik rawan beserta feature test yang membuktikan perilaku otorisasinya. Jalankan satu test dengan `php artisan test --filter=NamaTest`.

| No | Titik Rawan (Endpoint) | Metode | Skenario Penyerangan (Ancaman IDOR) | Aktor Penguji | Lapis Pertahanan / Mitigasi Backend | Ekspektasi Respons | Bukti Uji `curl` | Feature test |
|:--:|:-----------------------|:------:|:------------------------------------|:--------------|:------------------------------------|:------------------:|:-----------------|:--------------|
| **1** | `/submissions/{submission}` | `GET` | Mahasiswa mencoba membaca submission mahasiswa lain dengan mengganti ID. | Mahasiswa membuka submission milik orang lain | `SubmissionPolicy::view` membatasi submission mahasiswa pada pemiliknya. | **403 Forbidden** | Skenario 1 | `WeekFiveAccessTest::test_student_can_view_own_submission_but_not_another_students_submission` |
| **2** | `/api/v1/submissions/{submission}/grade` | `PUT` | Mahasiswa mencoba memberi nilai melalui API. | Mahasiswa pemilik atau bukan pemilik submission | `GradeSubmissionRequest::authorize()` dan `GradePolicy::create` hanya mengizinkan dosen pengampu. | **403 Forbidden** | Skenario 2 | `WeekSixApiTest::test_student_cannot_grade_submission_via_api` |
| **3** | `/assignments/{assignment}` | `PUT` / `DELETE` | Dosen mencoba mengubah atau menghapus tugas dosen lain. | Dosen yang bukan pengampu | `AssignmentPolicy::update/delete` membatasi pengelolaan kepada admin atau dosen pengampu. | **403 Forbidden** | Skenario 3 | `WeekFiveAccessTest::test_other_lecturer_cannot_update_or_delete_assignments_in_web` |
| **4** | `/courses/{course}/materials`; `/materials/{material}` | `POST` / `PUT` / `DELETE` | Dosen mencoba mengunggah ke, mengubah, atau menghapus materi kelas dosen lain. | Dosen yang bukan pengampu | `MaterialPolicy::create/update/delete` memeriksa hak pengelolaan mata kuliah. | **403 Forbidden** | Skenario 4 | `WeekNineFileTest::test_other_lecturer_cannot_create_update_or_delete_course_material` |
| **5** | `/courses/{course}/assignments/{assignment}` | `GET` | Penyerang meminta tugas milik MK-A lewat URL MK-B. | Mahasiswa/Dosen | Scoped route binding memastikan tugas merupakan anak dari mata kuliah pada URL. | **404 Not Found** | Skenario 5 | `WeekFiveAccessTest::test_nested_assignment_binding_rejects_assignment_from_another_course` |
| **6** | `/courses/{course}/materials/{material}` | `GET` | Penyerang meminta materi milik MK-A lewat URL MK-B. | Mahasiswa/Dosen | `MaterialController::showNested` memastikan materi terkait dengan mata kuliah pada URL. | **404 Not Found** | Skenario 6 | `WeekFiveAccessTest::test_nested_material_binding_rejects_material_from_another_course` |
| **7** | `/courses/{course}` | `PUT` / `DELETE` | Dosen atau mahasiswa mencoba mengubah atau menghapus mata kuliah. | Dosen/Mahasiswa; admin untuk jalur sukses | `CoursePolicy::update/delete` hanya mengizinkan admin. | **403 Forbidden** untuk non-admin | Skenario 7 | `WeekFiveAccessTest::test_non_admin_cannot_update_or_delete_courses_and_admin_can` |
| **8** | `/users` atau `/admin/users` | `GET` / `POST` | Pengguna selain admin mencoba melihat daftar atau membuat akun. | Dosen/Mahasiswa | Middleware rute dan `StoreUserRequest` membatasi aksi kepada admin. | **403 Forbidden** | Skenario 8 | `WeekFiveAccessTest::test_only_admin_can_manage_users`; `WeekFiveAccessTest::test_non_admin_cannot_create_users_and_admin_can` |
| **9** | `/courses/{course}/enrollments` | `POST` / `DELETE` | Pengguna tidak berwenang mencoba mengubah daftar mahasiswa kelas. | Dosen non-pengampu/Mahasiswa | `EnrollmentController` membatasi aksi kepada admin atau dosen pengampu. | **403 Forbidden** | Skenario 9 | `WeekFiveAccessTest::test_only_admin_or_assigned_lecturer_can_enroll_a_student` |
| **10** | `/notifications/{notification}` dan `/notifications/{notification}/read` | `GET` / `PATCH` / `DELETE` | Pengguna mencoba melihat, membaca, atau menghapus notifikasi milik orang lain. | Pengguna lain | `NotificationController` hanya mencari notifikasi lewat relasi pengguna yang login. | **404 Not Found** | Skenario 10 | `WeekTenNotificationTest::test_student_cannot_view_mark_or_delete_another_students_notification` |

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

> **PENTING — Cara Menjalankan di Windows PowerShell:**
> - Gunakan `-o NUL` (jangan gunakan `-o $null` karena PowerShell membaca `$null` sebagai string kosong sehingga curl gagal).
> - Tambahkan `-k` jika mengakses lewat domain lokal HTTPS seperti `https://kampuslms.test`.
> - Jika menggunakan `php artisan serve`, pastikan perintah `php artisan serve` aktif di jendela terminal terpisah (port 8000).

### Skenario 1: Uji IDOR Akses Submission Mahasiswa Lain (`/submissions/{submission}`)
**Deskripsi:** Mahasiswa demo (User 5) mencoba mengakses halaman detail submission milik mahasiswa lain (Submission 79 / User 6).
- **Perintah PowerShell:**
```powershell
# 1. Login sebagai mahasiswa demo via switch-role (simpan cookie sesi)
curl.exe -s -k -c cookie-mhs.txt http://127.0.0.1:8000/switch-role/mahasiswa -L -o NUL

# 2. Coba akses Submission 79 milik orang lain -> HARUS DITOLAK (403)
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status IDOR (Orang Lain): %{http_code}`n" http://127.0.0.1:8000/submissions/79

# 3. Akses Submission 1 milik sendiri -> HARUS BOLEH (200)
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Hak Akses (Milik Sendiri): %{http_code}`n" http://127.0.0.1:8000/submissions/1
```
- **Hasil:**
  - Submission orang lain: **HTTP 403 Forbidden**
  - Submission milik sendiri: **HTTP 200 OK**
- **Mitigasi Backend:** `SubmissionController@show` & `SubmissionPolicy@view` (`$user->id === $submission->user_id`).

---

### Skenario 2: Uji IDOR Penilaian Submission via REST API (`/api/v1/submissions/{id}/grade`)
**Deskripsi:** Mahasiswa memperoleh token API Sanctum dan mencoba mengubah/memberi nilai submission tugas miliknya sendiri.
- **Perintah PowerShell:**
```powershell
# 1. Ambil token Sanctum mahasiswa demo
$tokenMhs = (curl.exe -s -k http://127.0.0.1:8000/api/v1/auth/login -X POST -H "Content-Type: application/json" -d "{\`"email\`":\`"mahasiswa@kampuslms.test\`",\`"password\`":\`"password\`"}" | ConvertFrom-Json).data.token

# 2. Mahasiswa mencoba menilai submission 1 -> HARUS DITOLAK (403)
curl.exe -s -k -X PUT http://127.0.0.1:8000/api/v1/submissions/1/grade `
  -H "Authorization: Bearer $tokenMhs" `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d "{\`"score\`":95,\`"feedback\`":\`"Bagus\`"}" `
  -o NUL -w "Status Nilai oleh Mahasiswa: %{http_code}`n"
```
- **Hasil:** **HTTP 403 Forbidden**
- **Mitigasi Backend:** `GradeSubmissionRequest::authorize()` & `GradePolicy@update` (hanya dosen pengampu mata kuliah terkait).

---

### Skenario 3: Uji Cross-Lecturer IDOR pada Tugas (`/assignments/{assignment}`)
**Deskripsi:** Dosen demo (User 2) hanya mengampu MK 2 dan MK 3. Tugas 1 adalah milik MK 1 (diampu Dosen Sidney Koss / User 4). Dosen demo mencoba mengedit Tugas 1.
- **Perintah PowerShell:**
```powershell
# 1. Login sebagai Dosen Demo
curl.exe -s -k -c cookie-dosen.txt http://127.0.0.1:8000/switch-role/dosen -L -o NUL

# 2. Dosen mencoba update Tugas 1 (Mata Kuliah milik Dosen Lain) -> HARUS DITOLAK (403)
curl.exe -s -k -b cookie-dosen.txt -X PUT http://127.0.0.1:8000/assignments/1 `
  -H "Accept: application/json" `
  -d "title=Pembajakan+Tugas" `
  -o NUL -w "Status Update Tugas Dosen Lain: %{http_code}`n"

# 3. Dosen mencoba update Tugas 4 (Mata Kuliah sendiri / MK 2) -> HARUS DIIZINKAN (302/200)
curl.exe -s -k -b cookie-dosen.txt -X PUT http://127.0.0.1:8000/assignments/4 `
  -H "Accept: application/json" `
  -d "title=Tugas+Pertemuan+1+Resmi" `
  -o NUL -w "Status Update Tugas Sendiri: %{http_code}`n"
```
- **Hasil:**
  - Tugas milik dosen lain: **HTTP 403 Forbidden**
  - Tugas mata kuliah sendiri: **HTTP 200 OK / 302 Redirect**
- **Mitigasi Backend:** `AssignmentController::ensureAssignmentManager` & `AssignmentPolicy@update`/`delete`.

---

### Skenario 4: Uji Cross-Lecturer IDOR pada Materi Kuliah (`/materials/{material}`)
**Deskripsi:** Dosen demo (User 2) mencoba mengubah atau menghapus Materi 1 milik MK 1 yang diampu dosen lain.
- **Perintah PowerShell:**
```powershell
# Dosen mencoba update Materi 1 milik MK dosen lain -> HARUS DITOLAK (403)
curl.exe -s -k -b cookie-dosen.txt -X PUT http://127.0.0.1:8000/materials/1 `
  -H "Accept: application/json" `
  -d "title=Pembajakan+Materi" `
  -o NUL -w "Status Update Materi Dosen Lain: %{http_code}`n"
```
- **Hasil:** **HTTP 403 Forbidden**
- **Mitigasi Backend:** `MaterialController::ensureMaterialManager` & `MaterialPolicy@update`/`delete`.

---

### Skenario 5: Uji Cross-Course Scoped Binding IDOR pada Tugas (`/courses/{course}/assignments/{assignment}`)
**Deskripsi:** Penyerang mencoba memanipulasi URL bersarang dengan memasangkan Mata Kuliah 2 dengan Tugas 1 (padahal Tugas 1 sebenarnya milik Mata Kuliah 1).
- **Perintah PowerShell:**
```powershell
# 1. Akses pasangan tidak sah: Course 2 + Assignment 1 -> HARUS 404
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Cross-Course Assignment: %{http_code}`n" http://127.0.0.1:8000/courses/2/assignments/1

# 2. Akses pasangan sah: Course 1 + Assignment 1 -> HARUS 200
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Valid Course Assignment: %{http_code}`n" http://127.0.0.1:8000/courses/1/assignments/1
```
- **Hasil:**
  - Pasangan silang (*Cross-Course*): **HTTP 404 Not Found** (Model binding otomatis memeriksa integritas relasi).
  - Pasangan valid: **HTTP 200 OK**
- **Mitigasi Backend:** `Route::scopeBindings()` di `routes/web.php` dan `AssignmentController@showNested`.

---

### Skenario 6: Uji Cross-Course Scoped Binding IDOR pada Materi (`/courses/{course}/materials/{material}`)
**Deskripsi:** Penyerang memanggil Materi 1 (milik MK 1) melalui rute MK 2 (`/courses/2/materials/1`).
- **Perintah PowerShell:**
```powershell
# 1. Pasangan silang: Course 2 + Material 1 -> HARUS 404
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Cross-Course Material: %{http_code}`n" http://127.0.0.1:8000/courses/2/materials/1

# 2. Pasangan sah: Course 1 + Material 1 -> HARUS 200
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Valid Course Material: %{http_code}`n" http://127.0.0.1:8000/courses/1/materials/1
```
- **Hasil:**
  - Pasangan silang: **HTTP 404 Not Found**
  - Pasangan valid: **HTTP 200 OK**
- **Mitigasi Backend:** `Route::scopeBindings()` di `routes/web.php` dan `MaterialController@showNested`.

---

### Skenario 7: Uji Modifikasi Data Master Mata Kuliah (`/courses/{course}`)
**Deskripsi:** Pengguna dengan peran Dosen atau Mahasiswa mencoba mengubah data master mata kuliah atau menghapusnya.
- **Perintah PowerShell:**
```powershell
# Dosen mencoba merubah nama Mata Kuliah 1 -> HARUS DITOLAK (403)
curl.exe -s -k -b cookie-dosen.txt -X PUT http://127.0.0.1:8000/courses/1 `
  -H "Accept: application/json" `
  -d "name=Nama+Mata+Kuliah+Dirusak" `
  -o NUL -w "Status Edit Master MK oleh Dosen: %{http_code}`n"
```
- **Hasil:** **HTTP 403 Forbidden**
- **Mitigasi Backend:** `CoursePolicy@update` dan `CoursePolicy@delete` membatasi wewenang hanya pada `role === 'admin'`.

---

### Skenario 8: Uji Hak Akses Pengelolaan Pengguna (`/users` atau `/admin/users`)
**Deskripsi:** Mahasiswa atau Dosen mencoba mengakses modul administrasi pengguna master (`/users` atau `/admin/users`).
- **Perintah PowerShell:**
```powershell
# 1. Akses rute pengguna dengan sesi Mahasiswa -> HARUS DITOLAK (403)
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Akses /users oleh Mahasiswa: %{http_code}`n" http://127.0.0.1:8000/users

# 2. Akses rute pengguna dengan sesi Dosen -> HARUS DITOLAK (403)
curl.exe -s -k -b cookie-dosen.txt -o NUL -w "Status Akses /admin/users oleh Dosen: %{http_code}`n" http://127.0.0.1:8000/admin/users
```
- **Hasil:** **HTTP 403 Forbidden**
- **Mitigasi Backend:** Dilindungi oleh Middleware `role:admin` pada rute web.

---

### Skenario 9: Uji Manipulasi Peserta Kelas / Roster Tampering (`/courses/{course}/enrollments`)
**Deskripsi:** Dosen non-pengampu (atau Mahasiswa) mencoba mendaftarkan mahasiswa lain ke kelas yang bukan kewenangannya (misal Dosen Demo mendaftarkan mahasiswa ke MK 1).
- **Perintah PowerShell:**
```powershell
# Dosen Demo (bukan pengampu MK 1) mencoba menambahkan mahasiswa (ID 5) ke MK 1 -> HARUS 403
curl.exe -s -k -b cookie-dosen.txt -X POST http://127.0.0.1:8000/courses/1/enrollments `
  -H "Accept: application/json" `
  -d "student_id=5" `
  -o NUL -w "Status Roster Tamper oleh Dosen Non-Pengampu: %{http_code}`n"
```
- **Hasil:** **HTTP 403 Forbidden**
- **Mitigasi Backend:** `EnrollmentController::authorizeManager()` memanggil `Gate::authorize('manageContent', $course)`.

---

### Skenario 10: Uji Akses Notifikasi Pengguna Lain (`/{role}/notifications/{notification}`)
**Deskripsi:** Mahasiswa demo (User 5) mencoba mengakses atau menandai notifikasi milik Administrator (Notification ID 1).
- **Perintah PowerShell:**
```powershell
# Mahasiswa mencoba membuka notifikasi ID 1 milik Admin -> HARUS 404
curl.exe -s -k -b cookie-mhs.txt -o NUL -w "Status Akses Notifikasi Orang Lain: %{http_code}`n" http://127.0.0.1:8000/mahasiswa/notifications/1
```
- **Hasil:** **HTTP 404 Not Found**
- **Mitigasi Backend:** `NotificationController::userNotification()` mengambil data melalui relasi pengguna aktif `$user->notifications()->findOrFail($id)`. Notifikasi milik akun lain tidak pernah ada dalam kueri tersebut (*User-Scoped Isolation*).

## 4. Evaluasi Mandiri Kesiapan Uji Keamanan (UTS Bagian 3)

| Pertanyaan Penguji yang Diantisipasi | Jawaban Teknis & Lokasi Kode |
|:---|:---|
| **"Apa yang terjadi jika mahasiswa menebak ID submission di URL?"** | Request tertahan di `SubmissionController@show` baris 37-45 dan `SubmissionPolicy@view`. Aplikasi memeriksa apakah `submission->user_id === user->id`. Jika tidak sama dan bukan admin/dosen pengampu, Laravel langsung mengeksekusi `abort(403)`. |
| **"Mengapa scoped binding penting pada route nested seperti `courses/{course}/assignments/{assignment}`?"** | Tanpa `scopeBindings()`, Laravel hanya memeriksa apakah `{course}` ada dan `{assignment}` ada secara terpisah. Penyerang dapat menyusupkan penugasan dari kelas rahasia melalui URL kelas publik. Dengan `scopeBindings()`, Laravel otomatis menyisipkan kueri relasi SQL `WHERE course_id = ?`, mengembalikan **404 Not Found** jika tidak berelasi. |
| **"Apakah tombol di Blade yang disembunyikan dengan `@can` sudah cukup aman?"** | **Tidak cukup.** `@can` hanya mengontrol elemen visual di DOM browser. Penyerang dapat mengirimkan request HTTP mentah langsung ke server via `curl` atau Postman. Keamanan sejati ditegakkan di kontroler melalui `Gate::authorize()` atau pemanggilan Policy backend. |
| **"Bagaimana mencegah privilege escalation jika penyerang mengirimkan payload `role=admin` pada form registrasi/profil?"** | Kolom `role` dilindungi dari *Mass Assignment* dengan tidak membiarkannya diisi sembarangan via `$request->all()`. Kontroler hanya mengambil data dari `$request->validated()` atau `$request->safe()`, dan manipulasi peran hanya dapat dilakukan oleh Admin di rute `/admin/users`. |

---

*Dokumen ini dibuat dan divalidasi pada seluruh lingkungan pengujian lokal KampusLMS Kelompok 05.*
