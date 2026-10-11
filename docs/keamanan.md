# Titik Rawan IDOR KampusLMS

Route web dan API memakai middleware autentikasi/role sebagai lapisan awal. Controller atau Form Request kemudian memeriksa Policy/Gate atau pemeriksaan kepemilikan khusus (misalnya enrollment dan notifikasi); query daftar dibatasi sebelum pagination. `@can` di Blade hanya menyembunyikan aksi, sedangkan keputusan akses tetap dilakukan di server.

| Titik rawan | Risiko | Pengaman backend |
|---|---|---|
| `GET /dashboard` | Pengguna melihat ringkasan milik role/course lain | Middleware `auth`; closure dashboard mengambil role dari akun login dan membatasi query course serta turunannya menurut peran |
| `GET /courses/{course}` dan `GET /api/v1/courses/{id}` | Membuka mata kuliah di luar kewenangan | `CoursePolicy::view`; mahasiswa hanya melihat course terdaftar dan dosen hanya course ajarannya |
| `GET /courses` dan `GET /api/v1/courses` | Kebocoran daftar mata kuliah | Query `CourseController::index` / API `CourseController::index` dibatasi melalui `taughtCourses()` atau `courses()` sebelum pagination |
| `PUT/DELETE /courses/{course}` | Dosen mengubah course dosen lain | `CoursePolicy::update/delete`, dipanggil oleh Form Request dan controller |
| `GET /courses/{course}/materials` | Membaca materi course tanpa akses | `MaterialController@index` memeriksa `CoursePolicy::view` sebelum query materi; nested detail binding juga membatasi parent course |
| `GET /materials/{material}` | Membuka materi melalui ID langsung milik course lain | `MaterialPolicy::view` meneruskan pemeriksaan ke `CoursePolicy::view` |
| `PUT/DELETE /materials/{material}` | Mengubah atau menghapus materi pihak lain | `MaterialPolicy::update/delete` memeriksa admin atau dosen pemilik course |
| `GET /courses/{course}/assignments` | Membaca tugas course lain atau draft sebagai mahasiswa | `AssignmentController@index` memeriksa `CoursePolicy::view`; query mahasiswa hanya mengambil `published` |
| `GET /assignments/{assignment}` | Membuka tugas lewat ID langsung tanpa enrollment atau melihat draft | `AssignmentPolicy::view` memeriksa akses course; controller menyamarkan tugas draft mahasiswa dengan 404 |
| `PUT/DELETE /assignments/{assignment}` | Mengubah tugas dosen lain | `AssignmentPolicy::update/delete`; aksi create juga memeriksa course lewat Policy |
| `GET /api/v1/assignments/{assignment}/submissions` | Melihat pengumpulan di luar kelas yang diajar | `SubmissionPolicy::viewSubmissions` memeriksa admin atau dosen pengampu; query diambil melalui relasi assignment |
| `GET /submissions/{submission}` | Mahasiswa membaca pengumpulan mahasiswa lain | `SubmissionPolicy::view` memeriksa `user_id`, dosen pengampu, atau admin |
| `PUT /api/v1/submissions/{submission}/grade` dan `POST /dosen/submissions/{submission}/grade` | Pengguna tidak berwenang memberi nilai | `GradePolicy::create` memeriksa dosen pengampu |
| `PUT/DELETE /dosen/grades/{grade}` | Mengubah atau menghapus nilai pihak lain | `GradePolicy::update/delete` memeriksa dosen pengampu |
| `POST/PUT /users` | Pengguna menaikkan role menjadi admin | User CRUD berada di middleware `role:admin`; `UserController` mengambil field tervalidasi dan menulis role hanya di alur admin |
| `POST /admin/courses/{course}/enrollments` dan `/dosen/courses/{course}/enrollments`; `DELETE .../{student}` | Pengguna mengubah roster course yang bukan tanggung jawabnya atau mendaftarkan akun non-mahasiswa | `EnrollmentController` membatasi ke admin/dosen pengampu, memvalidasi role mahasiswa, dan menolak student ID yang bukan mahasiswa |
| `GET /{role}/notifications/{notification}` dan `PATCH /{role}/notifications/{notification}/read` | Membaca atau mengubah notifikasi orang lain | Notifikasi dicari melalui relasi pengguna yang login di `NotificationController` |

Untuk verifikasi API, jalankan `scripts/test-authz.sh` dengan token dan ID objek dari database development. Skrip itu menguji tiga skenario lintas pengguna; ia belum membuktikan semua baris tabel lulus. HTTP `403` berarti akses ditolak; `404` dapat diterima untuk route scoped yang menyamarkan objek. Jalur yang belum disebut skrip harus tetap dicoba manual atau ditambahkan sebagai feature test.

Login memakai session Laravel, regenerasi ID setelah autentikasi, dan logout menginvalidasi session serta token CSRF. Reset kata sandi menggunakan broker Laravel dengan token sekali pakai dan kadaluarsa; konfigurasi mail harus aktif agar tautan sampai ke pengguna. Tidak ada registrasi publik: akun dibuat oleh admin melalui manajemen pengguna.
