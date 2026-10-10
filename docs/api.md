# KampusLMS API v1

Base URL lokal:

```text
http://127.0.0.1:8000/api/v1
```

Autentikasi menggunakan Laravel Sanctum dengan Bearer Token.

Header untuk endpoint yang membutuhkan autentikasi:

```http
Accept: application/json
Authorization: Bearer <TOKEN>
```

## Akun Demo

| Role | Email | Password |
|---|---|---|
| Admin | `admin@kampuslms.test` | `password` |
| Dosen | `dosen@kampuslms.test` | `password` |
| Mahasiswa | `mahasiswa@kampuslms.test` | `password` |

## Format Response

### Koleksi

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "total": 47
  }
}
```

### Tunggal

```json
{
  "data": {}
}
```

### Validasi — 422

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "score": ["Nilai tidak boleh melebihi nilai maksimum tugas (100)."]
  }
}
```

### Tidak berhak — 403

```json
{
  "message": "Anda tidak memiliki akses ke sumber daya ini."
}
```

### Belum terautentikasi — 401

```json
{
  "message": "Unauthenticated."
}
```

## Endpoint

### 1. Login

`POST /auth/login`

Akses: publik.

Body:

```json
{
  "email": "dosen@kampuslms.test",
  "password": "password",
  "device_name": "web"
}
```

Contoh:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"dosen@kampuslms.test","password":"password","device_name":"web"}'
```

Sukses: `200`.

```json
{
  "data": {
    "token": "1|...",
    "user": {
      "id": 1,
      "name": "Dosen Demo",
      "email": "dosen@kampuslms.test",
      "nim_nip": "DOS001",
      "role": "dosen"
    }
  }
}
```

Kredensial salah: `401`.

---

### 2. Logout

`POST /auth/logout`

Akses: authenticated user.

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `204 No Content`.

---

### 3. Profil pengguna

`GET /me`

Akses: authenticated user.

```bash
curl http://127.0.0.1:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200`.

---

### 4. Daftar mata kuliah

`GET /courses`

Akses: authenticated user.

- Admin: semua mata kuliah.
- Dosen: mata kuliah yang diajar.
- Mahasiswa: mata kuliah yang diikuti.

Parameter:

- `page` — nomor halaman.

```bash
curl "http://127.0.0.1:8000/api/v1/courses?page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200`.

---

### 5. Detail mata kuliah

`GET /courses/{id}`

Akses: authenticated user yang memiliki akses ke mata kuliah.

Response memuat detail mata kuliah, dosen, jumlah materi, dan jumlah tugas.

```bash
curl http://127.0.0.1:8000/api/v1/courses/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200`.

Akses ke mata kuliah milik dosen lain: `403`.

---

### 6. Daftar materi

`GET /courses/{id}/materials`

Akses: user yang memiliki akses ke mata kuliah.

```bash
curl http://127.0.0.1:8000/api/v1/courses/1/materials \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200`.

---

### 7. Daftar tugas mata kuliah

`GET /courses/{id}/assignments`

Akses: user yang memiliki akses ke mata kuliah.

Parameter:

- `status=draft` atau `status=published`
- `page=1`

Mahasiswa hanya menerima tugas `published`.

```bash
curl "http://127.0.0.1:8000/api/v1/courses/1/assignments?status=published&page=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200`.

---

### 8. Membuat tugas

`POST /assignments`

Akses: dosen.

Dosen hanya boleh membuat tugas pada mata kuliah yang diajarnya.

Body:

```json
{
  "course_id": 1,
  "title": "Tugas Pertemuan 1",
  "instructions": "Kerjakan sesuai instruksi.",
  "due_at": "2030-01-01 12:00:00",
  "max_score": 100,
  "allow_late": true,
  "status": "draft"
}
```

```bash
curl -X POST http://127.0.0.1:8000/api/v1/assignments \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <DOSEN_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"course_id":1,"title":"Tugas Pertemuan 1","instructions":"Kerjakan sesuai instruksi.","due_at":"2030-01-01 12:00:00","max_score":100,"allow_late":true,"status":"draft"}'
```

Sukses: `201`.

Mahasiswa atau dosen yang bukan pemilik mata kuliah: `403`.

---

### 9. Mengubah tugas

`PUT /assignments/{id}` atau `PATCH /assignments/{id}`

Akses: dosen pemilik mata kuliah.

Semua field bersifat opsional untuk update parsial.

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/assignments/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <DOSEN_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Tugas Revisi","status":"published"}'
```

Sukses: `200`.

---

### 10. Menghapus tugas

`DELETE /assignments/{id}`

Akses: dosen pemilik mata kuliah.

```bash
curl -X DELETE http://127.0.0.1:8000/api/v1/assignments/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <DOSEN_TOKEN>"
```

Sukses: `204 No Content`.

---

### 11. Melihat submission tugas

`GET /assignments/{id}/submissions`

Akses: dosen pemilik mata kuliah.

```bash
curl http://127.0.0.1:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <DOSEN_TOKEN>"
```

Sukses: `200`.

Mahasiswa atau dosen lain: `403`.

---

### 12. Mengumpulkan tugas

`POST /assignments/{id}/submissions`

Akses: mahasiswa yang terdaftar pada mata kuliah dan tugas berstatus `published`.

Multipart field:

- `file` — PDF, DOC, DOCX, atau ZIP; maksimal 10 MB.
- `note` — opsional.

```bash
curl -X POST http://127.0.0.1:8000/api/v1/assignments/1/submissions \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <MAHASISWA_TOKEN>" \
  -F "file=@tugas.pdf" \
  -F "note=Pengumpulan tugas"
```

Sukses: `201`.

Mahasiswa yang tidak terdaftar atau bukan mahasiswa: `403`.

---

### 13. Memberi / memperbarui nilai

`PUT /submissions/{id}/grade`

Akses: dosen pemilik mata kuliah submission.

Body:

```json
{
  "score": 90,
  "feedback": "Bagus, tetapi masih dapat diperbaiki."
}
```

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/submissions/1/grade \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <DOSEN_TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"score":90,"feedback":"Bagus, tetapi masih dapat diperbaiki."}'
```

Sukses saat nilai pertama dibuat: `201`.

Sukses saat nilai diperbarui: `200`.

Score di atas `max_score`: `422`.

Mahasiswa atau dosen bukan pemilik mata kuliah: `403`.

---

### 14. Daftar notifikasi

`GET /notifications`

Akses: authenticated user.

Notifikasi hanya diambil dari relasi pengguna yang sedang login.

```bash
curl http://127.0.0.1:8000/api/v1/notifications \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200`.

---

### 15. Menandai notifikasi sudah dibaca

`POST /notifications/{id}/read`

Akses: authenticated user.

ID notifikasi harus milik pengguna yang sedang login.

```bash
curl -X POST http://127.0.0.1:8000/api/v1/notifications/UUID/read \
  -H "Accept: application/json" \
  -H "Authorization: Bearer <TOKEN>"
```

Sukses: `200` dengan `NotificationResource`.

Notifikasi milik pengguna lain atau ID tidak ditemukan: `404`.

## Rate Limit

- `POST /auth/login`: maksimal 5 request per menit.
- Endpoint authenticated: maksimal 60 request per menit.

## Pengujian API

Jalankan:

```bash
bash scripts/test-api.sh
```

Script tersebut memeriksa autentikasi 401, pembatasan role 403, ownership dosen, submission mahasiswa, grading 201/200, endpoint utama, delete 204, dan logout 204.
