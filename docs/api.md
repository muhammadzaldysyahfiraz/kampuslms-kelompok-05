# Dokumentasi REST API KampusLMS (v1)

Dokumentasi resmi antarmuka pemrograman aplikasi (*Application Programming Interface*) untuk sistem **KampusLMS** berbasis **Laravel 12** dan **Laravel Sanctum**.

---

## 1. Ikhtisar Arsitektur & Ketentuan Umum

- **Base URL:** `http://localhost:8000/api/v1` (atau `http://kampuslms.test/api/v1`)
- **Prefix Versi:** `/api/v1`
- **Autentikasi:** [Laravel Sanctum](https://laravel.com/docs/12.x/sanctum) menggunakan skema Bearer Token pada HTTP Header:
  ```http
  Authorization: Bearer <personal_access_token>
  ```
- **Format Pertukaran Data:** JSON (*JavaScript Object Notation*).
- **Header Permintaan Wajib:**
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
  *(Catatan: Header `Content-Type: application/json` tidak digunakan saat mengunggah berkas multipart pada pengumpulan tugas).*

### Kode Status HTTP (HTTP Status Codes)

| Kode | Keterangan | Penggunaan di KampusLMS |
|:---:|---|---|
| `200 OK` | Permintaan berhasil | Pengambilan data tunggal/koleksi, pembaruan data tugas/nilai. |
| `201 Created` | Sumber daya baru berhasil dibuat | Pembuatan tugas (`POST /assignments`), pengumpulan berkas (`POST /submissions`), pembuatan nilai baru (`PUT /submissions/{id}/grade`). |
| `204 No Content` | Berhasil dieksekusi tanpa muatan balik | Penghapusan tugas (`DELETE /assignments/{id}`), logout (`POST /auth/logout`). |
| `401 Unauthorized` | Autentikasi gagal / belum login | Token tidak disertakan, format token salah, atau token telah dicabut. |
| `403 Forbidden` | Otorisasi ditolak | Pengguna terotentikasi mencoba mengakses data di luar perannya atau di luar kepemilikan haknya (pencegahan IDOR). |
| `404 Not Found` | Data tidak ditemukan | Objek basis data tidak ada atau tidak berada dalam relasi kepemilikan pengguna. |
| `409 Conflict` | Konflik integritas data | Mahasiswa mencoba mengumpulkan tugas yang sudah pernah ia kumpulkan sebelumnya (*anti-duplicate submission*). |
| `422 Unprocessable Content` | Validasi input gagal | Masukan data tidak memenuhi aturan validasi form request. |
| `429 Too Many Requests` | Melebihi ambang batas laju (*Rate Limit*) | Dipicu saat melebihi 5 percobaan/menit pada login atau 60 permintaan/menit pada rute umum. |

### Akun Demo untuk Pengujian

| Peran | Alamat Email | Kata Sandi | Deskripsi Akses |
|---|---|---|---|
| **Admin** | `admin@kampuslms.test` | `password` | Mengelola seluruh pengguna, mata kuliah, dan pemantauan sistem. |
| **Dosen** | `dosen@kampuslms.test` | `password` | Mengelola materi & tugas untuk mata kuliah yang diampunya, menilai tugas mahasiswa. |
| **Mahasiswa** | `mahasiswa@kampuslms.test` | `password` | Melihat materi & tugas mata kuliah yang diikutinya, mengunggah pengumpulan tugas. |

---

## 2. Format Respon Standar (Bagian 5 Spesifikasi)

### Respon Sukses (Data Tunggal)
```json
{
  "data": {
    "id": 1,
    "name": "Pemrograman Web",
    "code": "SI2514024"
  }
}
```

### Respon Sukses (Koleksi Berpaginasi)
```json
{
  "data": [
    {
      "id": 1,
      "name": "Pemrograman Web"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 65
  }
}
```

### Respon Gagal Validasi (`422 Unprocessable Content`)
```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "score": [
      "Nilai tidak boleh melebihi nilai maksimum tugas (100)."
    ]
  }
}
```

### Respon Akses Ditolak (`403 Forbidden`)
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

### Respon Belum Terautentikasi (`401 Unauthorized`)
```json
{
  "message": "Unauthenticated."
}
```

---

## 3. Modul Autentikasi (`/auth`)

### 3.1 Login & Dapatkan Token
Mengautentikasi kredensial pengguna dan mengembalikan Sanctum personal access token. Dilindungi pembatas laju 5 permintaan per menit (*throttle: 5,1*) dan perlindungan terhadap *user enumeration*.

- **Metode & Endpoint:** `POST /api/v1/auth/login`
- **Akses:** Publik (tanpa token)
- **Rate Limit:** 5 requests / menit
- **Body Parameters (JSON):**
  - `email` (string, required, valid email)
  - `password` (string, required)
  - `device_name` (string, optional, default: `"api"`)

#### Contoh Perintah cURL:
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "dosen@kampuslms.test",
    "password": "password",
    "device_name": "curl-terminal"
  }'
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": {
    "token": "1|qXy0mK8xW9vZ1eP...plaintext-token...",
    "user": {
      "id": 2,
      "name": "Dosen Demo",
      "email": "dosen@kampuslms.test",
      "nim_nip": "DOS001",
      "role": "dosen"
    }
  }
}
```

#### Respon Gagal Validasi / Kredensial Salah (`422 Unprocessable Content`):
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

---

### 3.2 Profil Pengguna Saat Ini (`/me`)
Mengambil identitas pengguna yang sedang terotentikasi berdasarkan token Bearer yang dikirimkan.

- **Metode & Endpoint:** `GET /api/v1/me`
- **Akses:** Terotentikasi (`auth:sanctum`)

#### Contoh Perintah cURL:
```bash
curl -X GET http://localhost:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": {
    "id": 2,
    "name": "Dosen Demo",
    "email": "dosen@kampuslms.test",
    "nim_nip": "DOS001",
    "role": "dosen"
  }
}
```

---

### 3.3 Logout & Pencabutan Token
Mencabut (*revoke*) token yang sedang aktif digunakan sehingga tidak dapat digunakan kembali.

- **Metode & Endpoint:** `POST /api/v1/auth/logout`
- **Akses:** Terotentikasi (`auth:sanctum`)

#### Contoh Perintah cURL:
```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`204 No Content`):
*(Tanpa bodi konten HTTP).*

---

## 4. Modul Mata Kuliah (`/courses`)

### 4.1 Daftar Mata Kuliah
Mengambil daftar mata kuliah yang relevan dengan pengguna yang login secara terpaginasi (Admin: seluruh MK; Dosen: MK yang diajar; Mahasiswa: MK yang diikuti). Menerapkan *eager loading* relasi dosen dan penghitungan agregat materi & tugas untuk mencegah permasalahan N+1.

- **Metode & Endpoint:** `GET /api/v1/courses`
- **Akses:** Terotentikasi (`auth:sanctum`)
- **Query Parameters:**
  - `page` (integer, optional, default: `1`)

#### Contoh Perintah cURL:
```bash
curl -X GET "http://localhost:8000/api/v1/courses?page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": [
    {
      "id": 1,
      "code": "SI2514024",
      "name": "Pemrograman Web",
      "description": "Pengembangan web modern berbasis Laravel 12 dan REST API.",
      "sks": 3,
      "status": "active",
      "lecturer": {
        "id": 2,
        "name": "Dosen Demo"
      },
      "counts": {
        "materials": 4,
        "assignments": 3
      },
      "created_at": "2026-09-13T07:10:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### 4.2 Detail Mata Kuliah
Mendapatkan detail mata kuliah berdasarkan ID. Menggunakan scoped authorization: Mahasiswa hanya boleh melihat mata kuliah yang diikutinya; Dosen hanya boleh melihat mata kuliah yang diampunya; Admin dapat melihat seluruh mata kuliah.

- **Metode & Endpoint:** `GET /api/v1/courses/{id}`
- **Akses:** Terotentikasi + Terotorisasi (`auth:sanctum`)

#### Contoh Perintah cURL:
```bash
curl -X GET http://localhost:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": {
    "id": 1,
    "code": "SI2514024",
    "name": "Pemrograman Web",
    "description": "Pengembangan web modern berbasis Laravel 12 dan REST API.",
    "sks": 3,
    "status": "active",
    "lecturer": {
      "id": 2,
      "name": "Dosen Demo"
    },
    "counts": {
      "materials": 4,
      "assignments": 3
    },
    "created_at": "2026-09-13T07:10:00+00:00"
  }
}
```

#### Respon Akses Ditolak (`403 Forbidden`):
```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

---

### 4.3 Daftar Materi Pembelajaran Mata Kuliah
Mengambil daftar materi pembelajaran yang diunggah ke dalam suatu mata kuliah secara terpaginasi.

- **Metode & Endpoint:** `GET /api/v1/courses/{course}/materials`
- **Akses:** Terotentikasi + Terdaftar dalam Course (`auth:sanctum`)
- **Query Parameters:**
  - `page` (integer, optional)

#### Contoh Perintah cURL:
```bash
curl -X GET http://localhost:8000/api/v1/courses/1/materials \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Pengantar REST API & Sanctum",
      "description": "Slide kuliah pekan ke-6.",
      "type": "file",
      "original_name": "pekan-06-api.pdf",
      "file_size": 2451000,
      "mime_type": "application/pdf",
      "external_url": null,
      "uploader": {
        "id": 2,
        "name": "Dosen Demo"
      },
      "created_at": "2026-10-06T08:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### 4.4 Daftar Tugas Mata Kuliah
Mengambil daftar tugas pada mata kuliah tertentu. Mahasiswa otomatis hanya melihat tugas berstatus `published`. Dosen pengampu dapat melihat seluruh status dan dapat melakukan pemfilteran berdasarkan parameter `status`.

- **Metode & Endpoint:** `GET /api/v1/courses/{course}/assignments`
- **Akses:** Terotentikasi + Terdaftar dalam Course (`auth:sanctum`)
- **Query Parameters:**
  - `status` (string, optional, pilihan: `draft`, `published`)
  - `page` (integer, optional)

#### Contoh Perintah cURL:
```bash
curl -X GET "http://localhost:8000/api/v1/courses/1/assignments?status=published" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Tugas 1: Pembangunan REST API",
      "instructions": "Bangun 14 endpoint REST API sesuai spesifikasi Bagian 5.",
      "due_at": "2026-10-20T23:59:00+00:00",
      "max_score": 100,
      "allow_late": true,
      "status": "published",
      "created_at": "2026-10-06T09:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

## 5. Modul Tugas (`/assignments`)

### 5.1 Buat Tugas Baru
Membuat tugas baru pada mata kuliah. Hanya boleh dilakukan oleh Dosen pengampu mata kuliah terkait.

- **Metode & Endpoint:** `POST /api/v1/assignments`
- **Akses:** Dosen Pengampu Mata Kuliah
- **Body Parameters (JSON):**
  - `course_id` (integer, required, exists:courses,id)
  - `title` (string, required, max: 255)
  - `instructions` (string, required)
  - `due_at` (datetime string, required)
  - `max_score` (integer, required, between: 1, 100)
  - `allow_late` (boolean, optional, default: true)
  - `status` (string, optional, in: `draft`, `published`, default: `draft`)

#### Contoh Perintah cURL:
```bash
curl -X POST http://localhost:8000/api/v1/assignments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN_DOSEN>" \
  -d '{
    "course_id": 1,
    "title": "Tugas 2: Evaluasi Keamanan Web",
    "instructions": "Lakukan audit kerentanan IDOR dan kebocoran data.",
    "due_at": "2026-10-25 23:59:00",
    "max_score": 100,
    "allow_late": false,
    "status": "published"
  }'
```

#### Respon Sukses (`201 Created`):
```json
{
  "data": {
    "id": 2,
    "course_id": 1,
    "title": "Tugas 2: Evaluasi Keamanan Web",
    "instructions": "Lakukan audit kerentanan IDOR dan kebocoran data.",
    "due_at": "2026-10-25T23:59:00+00:00",
    "max_score": 100,
    "allow_late": false,
    "status": "published",
    "created_at": "2026-10-07T08:00:00+00:00"
  }
}
```

---

### 5.2 Perbarui Tugas
Memperbarui rincian tugas yang sudah ada. Dilindungi otorisasi kepemilikan (`UpdateAssignmentRequest`). Dosen lain yang mencoba mengubah tugas ini akan ditolak 403 Forbidden.

- **Metode & Endpoint:** `PUT /api/v1/assignments/{assignment}` (atau `PATCH`)
- **Akses:** Dosen Pengampu Mata Kuliah Pemilik
- **Body Parameters (JSON):**
  - Parameter bersifat parsial / kadang-kadang (`sometimes`): `title`, `instructions`, `due_at`, `max_score`, `allow_late`, `status`.

#### Contoh Perintah cURL:
```bash
curl -X PUT http://localhost:8000/api/v1/assignments/2 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN_DOSEN>" \
  -d '{
    "max_score": 95,
    "allow_late": true
  }'
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": {
    "id": 2,
    "course_id": 1,
    "title": "Tugas 2: Evaluasi Keamanan Web",
    "instructions": "Lakukan audit kerentanan IDOR dan kebocoran data.",
    "due_at": "2026-10-25T23:59:00+00:00",
    "max_score": 95,
    "allow_late": true,
    "status": "published",
    "created_at": "2026-10-07T08:00:00+00:00"
  }
}
```

---

### 5.3 Hapus Tugas
Menghapus penugasan dari sistem. Hanya boleh dilakukan oleh dosen pemilik mata kuliah.

- **Metode & Endpoint:** `DELETE /api/v1/assignments/{assignment}`
- **Akses:** Dosen Pengampu Mata Kuliah Pemilik

#### Contoh Perintah cURL:
```bash
curl -X DELETE http://localhost:8000/api/v1/assignments/2 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN_DOSEN>"
```

#### Respon Sukses (`204 No Content`):
*(Tanpa bodi konten HTTP).*

---

## 6. Modul Pengumpulan & Penilaian (`/submissions`)

### 6.1 Daftar Pengumpulan Tugas
Melihat seluruh daftar berkas tugas yang dikumpulkan mahasiswa untuk tugas tertentu. Hanya dapat diakses oleh dosen pengampu mata kuliah tugas tersebut. Eager loading relasi `student` dan `grade` untuk mencegah N+1 query.

- **Metode & Endpoint:** `GET /api/v1/assignments/{assignment}/submissions`
- **Akses:** Dosen Pengampu Mata Kuliah
- **Query Parameters:**
  - `page` (integer, optional)

#### Contoh Perintah cURL:
```bash
curl -X GET http://localhost:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN_DOSEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": [
    {
      "id": 1,
      "assignment_id": 1,
      "student": {
        "id": 5,
        "name": "Budi Santoso",
        "nim_nip": "10241088"
      },
      "original_name": "laporan-tugas-budi.pdf",
      "file_size": 1542000,
      "note": "Berikut laporan pengujian tugas saya.",
      "submitted_at": "2026-10-07T08:15:00+00:00",
      "is_late": false,
      "grade": {
        "id": 1,
        "submission_id": 1,
        "score": 92.5,
        "feedback": "Pekerjaan rapi dan lengkap.",
        "graded_at": "2026-10-07T09:00:00+00:00",
        "grader": {
          "id": 2,
          "name": "Dosen Demo"
        }
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### 6.2 Unggah Pengumpulan Tugas (Mahasiswa)
Mahasiswa yang terdaftar pada mata kuliah mengunggah berkas pengumpulan tugas (PDF, DOC, DOCX, ZIP max 10MB). Sistem memvalidasi status penugasan (`published`), batas keterlambatan (`allow_late`), serta mencegah pengumpulan ganda (*anti-duplicate submission*).

- **Metode & Endpoint:** `POST /api/v1/assignments/{assignment}/submissions`
- **Akses:** Mahasiswa Terdaftar dalam Mata Kuliah
- **Content-Type:** `multipart/form-data`
- **Form Data Parameters:**
  - `file` (file binary, required, mimes: `pdf,doc,docx,zip`, max: `10240` KB)
  - `note` (string, optional, max: `1000`)

#### Contoh Perintah cURL:
```bash
curl -X POST http://localhost:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN_MAHASISWA>" \
  -F "file=@/path/to/laporan.pdf" \
  -F "note=Tugas pengujian REST API selesai dikerjakan."
```

#### Respon Sukses (`201 Created`):
```json
{
  "data": {
    "id": 2,
    "assignment_id": 1,
    "student": {
      "id": 3,
      "name": "Mahasiswa Demo",
      "nim_nip": "MHS001"
    },
    "original_name": "laporan.pdf",
    "file_size": 254010,
    "note": "Tugas pengujian REST API selesai dikerjakan.",
    "submitted_at": "2026-10-07T08:30:00+00:00",
    "is_late": false,
    "grade": null
  }
}
```

#### Respon Pengumpulan Ganda (`409 Conflict`):
```json
{
  "message": "Anda sudah mengumpulkan tugas ini."
}
```

---

### 6.3 Beri / Perbarui Nilai Pengumpulan Tugas (*Upsert*)
Dosen pengampu memberikan nilai dan umpan balik (*feedback*) kepada mahasiswa. Sesuai kontrak Bagian 5, endpoint ini bersifat *upsert*:
- Mengembalikan kode **`201 Created`** ketika nilai dibuat untuk pertama kali.
- Mengembalikan kode **`200 OK`** ketika nilai diperbarui (*re-graded*).

- **Metode & Endpoint:** `PUT /api/v1/submissions/{submission}/grade`
- **Akses:** Dosen Pengampu Mata Kuliah
- **Body Parameters (JSON):**
  - `score` (numeric, required, min: 0, max: assignment max_score)
  - `feedback` (string, optional, max: 2000)

#### Contoh Perintah cURL:
```bash
curl -X PUT http://localhost:8000/api/v1/submissions/2/grade \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TOKEN_DOSEN>" \
  -d '{
    "score": 95,
    "feedback": "Hasil implementasi sangat baik dan memenuhi standar Bagian 5."
  }'
```

#### Respon Sukses Nilai Baru Dibuat (`201 Created`):
```json
{
  "data": {
    "id": 2,
    "submission_id": 2,
    "score": 95,
    "feedback": "Hasil implementasi sangat baik dan memenuhi standar Bagian 5.",
    "graded_at": "2026-10-07T08:45:00+00:00",
    "grader": {
      "id": 2,
      "name": "Dosen Demo"
    }
  }
}
```

#### Respon Sukses Pembaruan Nilai (`200 OK`):
```json
{
  "data": {
    "id": 2,
    "submission_id": 2,
    "score": 98,
    "feedback": "Revisi perbaikan telah diterima dan diverifikasi.",
    "graded_at": "2026-10-07T08:50:00+00:00",
    "grader": {
      "id": 2,
      "name": "Dosen Demo"
    }
  }
}
```

---

## 7. Modul Notifikasi (`/notifications`)

### 7.1 Daftar Notifikasi Pengguna
Mengambil daftar notifikasi milik pengguna yang sedang login secara terpaginasi.

- **Metode & Endpoint:** `GET /api/v1/notifications`
- **Akses:** Terotentikasi (`auth:sanctum`)
- **Query Parameters:**
  - `page` (integer, optional)

#### Contoh Perintah cURL:
```bash
curl -X GET http://localhost:8000/api/v1/notifications \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": [
    {
      "id": "e9324d27-4b68-45a9-b367-efbf578e3489",
      "type": "AssignmentCreatedNotification",
      "data": {
        "title": "Tugas Baru: Pembangunan REST API",
        "course": "Pemrograman Web"
      },
      "read_at": null,
      "created_at": "2026-10-06T09:00:00+00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

---

### 7.2 Tandai Notifikasi Telah Dibaca
Menandai notifikasi tertentu sebagai telah dibaca (*read*). Pencarian dilakukan secara aman lewat relasi `$user->notifications()`, sehingga jika notifikasi tersebut milik pengguna lain akan merespons **404 Not Found** (mencegah IDOR).

- **Metode & Endpoint:** `POST /api/v1/notifications/{id}/read`
- **Akses:** Terotentikasi (`auth:sanctum`)

#### Contoh Perintah cURL:
```bash
curl -X POST http://localhost:8000/api/v1/notifications/e9324d27-4b68-45a9-b367-efbf578e3489/read \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

#### Respon Sukses (`200 OK`):
```json
{
  "data": {
    "id": "e9324d27-4b68-45a9-b367-efbf578e3489",
    "type": "AssignmentCreatedNotification",
    "data": {
      "title": "Tugas Baru: Pembangunan REST API",
      "course": "Pemrograman Web"
    },
    "read_at": "2026-10-07T08:55:00+00:00",
    "created_at": "2026-10-06T09:00:00+00:00"
  }
}
```

---

## 8. Panduan Menjalankan Skrip Pengujian Otomatis

Untuk menguji seluruh endpoint REST API dan pembuktian otorisasi di lingkungan terminal, telah disediakan skrip uji berbasis bash cURL:

```bash
# Memberikan izin eksekusi skrip
chmod +x scripts/test-api.sh

# Menjalankan rangkaian uji cURL
bash scripts/test-api.sh
```

Atau melalui rangkaian unit & feature test Laravel:
```bash
php artisan test --filter=WeekSixApiTest
```
