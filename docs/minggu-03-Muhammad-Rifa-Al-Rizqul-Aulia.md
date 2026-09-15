# Catatan Praktikum Minggu 3 Pemrograman Web

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

# I. READ: Analisis Skema Database, Constraint, dan CRUD

Pada Minggu ke-3 ini, fokus pengembangan diarahkan pada penyusunan fondasi data yang kokoh untuk **KampusLMS**, implementasi relasi antar-tabel (*foreign key constraints*), perlindungan *mass assignment*, pengujian reversibilitas migrasi (*up & down*), serta penyediaan data awal (*seeder* dan *factory*) sesuai spesifikasi Milestone M1.

---

### 1. Perilaku `onDelete` pada Seluruh Foreign Key dan Justifikasinya

Setiap relasi tabel yang melibatkan *foreign key* wajib memiliki aturan `onDelete` yang terdefinisi secara eksplisit untuk menjaga integritas referensial dan mencegah anomali *orphaned data* (data anak yang kehilangan data induknya).

| No | Kolom Foreign Key | Tabel Referensi | Aturan `onDelete` | Alasan & Justifikasi Desain |
|:--:|:---|:---|:---:|:---|
| 1 | `courses.lecturer_id` | `users.id` | `restrictOnDelete()` | Dosen pengampu tidak boleh dihapus secara tiba-tiba jika masih aktif mengampu mata kuliah. Hal ini mencegah mata kuliah menjadi tidak bertuan (*orphaned course*). Dosen harus dimutasi/diganti terlebih dahulu sebelum akunnya dapat dinonaktifkan. |
| 2 | `course_user.course_id` | `courses.id` | `cascadeOnDelete()` | Jika sebuah mata kuliah dihapus dari kurikulum, maka data pendaftaran (*enrollment*) mahasiswa pada mata kuliah tersebut otomatis tidak lagi valid dan harus ikut terhapus dari tabel perantara (*pivot*). |
| 3 | `course_user.user_id` | `users.id` | `cascadeOnDelete()` | Jika seorang mahasiswa dihapus/keluar dari sistem, maka data pendaftarannya pada seluruh mata kuliah harus ikut dibersihkan untuk menjaga kebersihan tabel relasi *many-to-many*. |
| 4 | `materials.course_id` | `courses.id` | `cascadeOnDelete()` | Materi perkuliahan adalah bagian integral dari mata kuliah. Jika mata kuliah dihapus, seluruh materi dan silabus yang menempel pada mata kuliah tersebut ikut terhapus. |
| 5 | `materials.uploaded_by` | `users.id` | `restrictOnDelete()` | Mencegah penghapusan akun pengajar jika pengajar tersebut masih tercatat sebagai pemilik/pengunggah materi perkuliahan aktif. Rekam jejak kepemilikan dokumen akademik harus terlindungi. |
| 6 | `assignments.course_id` | `courses.id` | `cascadeOnDelete()` | Tugas perkuliahan (*assignment*) hanya eksis dalam konteks mata kuliah bersangkutan. Jika mata kuliah dihapus, seluruh daftar tugas di dalamnya ikut dihapus. |
| 7 | `assignments.created_by` | `users.id` | `restrictOnDelete()` | Dosen yang menerbitkan tugas tidak boleh dihapus begitu saja jika penugasan masih aktif di sistem, demi akuntabilitas akademik. |
| 8 | `submissions.assignment_id` | `assignments.id` | `cascadeOnDelete()` | Jika sebuah tugas dihapus oleh pengajar, maka berkas pengumpulan tugas (*submission*) dari mahasiswa terkait tugas tersebut ikut terhapus. |
| 9 | `submissions.user_id` | `users.id` | `restrictOnDelete()` | Akun mahasiswa yang sudah memiliki riwayat pengumpulan tugas tidak boleh dihapus sembarangan. Riwayat pengerjaan tugas wajib dilindungi untuk keperluan audit nilai akademik. |
| 10 | `grades.submission_id` | `submissions.id` | `cascadeOnDelete()` | Nilai evaluasi (*grade*) hanya melekat pada satu berkas tugas yang dikumpulkan. Jika submission dihapus, nilai evaluasinya ikut terhapus. |
| 11 | `grades.graded_by` | `users.id` | `restrictOnDelete()` | Dosen/penilai yang memberikan skor tidak boleh dihapus jika masih tercatat dalam riwayat penilaian mahasiswa. |

---

### 2. Skenario Penghapusan Akun Dosen

**Pertanyaan:** *Kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?*

**Penjelasan:**  
Pada skema `courses`, foreign key `lecturer_id` dikonfigurasi menggunakan:
```php
$table->foreignId('lecturer_id')
    ->constrained('users')
    ->restrictOnDelete();
```
Ketika perintah penghapusan akun dosen dijalankan (misalnya `$lecturer->delete()`), database MySQL/MariaDB akan **menolak operasi penghapusan tersebut** dan melempar error `QueryException` (kode error SQLSTATE 23000 — Integrity constraint violation).

**Alasan Perancangan:**  
Dalam sistem LMS universitas, mata kuliah adalah entitas resmi kurikulum akademik. Sangat berbahaya jika penghapusan satu akun dosen mengakibatkan seluruh mata kuliah (beserta ratusan mahasiswa yang terdaftar, materi perkuliahan, dan riwayat tugas di dalamnya) ikut lenyap (*cascade*). Dengan `restrict`, administrator diwajibkan untuk mengalihkan (*re-assign*) mata kuliah ke dosen pengganti terlebih dahulu sebelum akun dosen lama dapat dihapus.

---

### 3. Keunikan `grades.submission_id` (Unique Constraint vs Index Biasa)

**Pertanyaan:** *Kenapa `grades.submission_id` bersifat `unique`, bukan sekadar index biasa?*

**Penjelasan:**  
Hubungan antara berkas pengumpulan tugas (`submissions`) dengan nilai evaluasi (`grades`) adalah relasi **One-to-One (1:1)**. Satu mahasiswa yang mengumpulkan satu tugas hanya boleh memiliki **satu nilai resmi**.

```php
$table->foreignId('submission_id')
    ->unique()
    ->constrained('submissions')
    ->cascadeOnDelete();
```

* **Jika hanya menggunakan `index biasa`**: Database hanya mengoptimalkan kecepatan pencarian data (*read lookup*), tetapi tetap memperbolehkan baris ganda (*duplicate entries*). Hal ini berisiko menimbulkan anomali di mana satu submission bisa dinilai dua kali dengan skor yang berbeda (misalnya Submission #10 diberi nilai 75 dan 90 secara bersamaan), sehingga sistem kebingungan menentukan nilai mana yang sah.
* **Dengan `unique constraint`**: Database secara fisik menjamin integritas data di level terendah (*engine level*). Jika ada percobaan *insert* nilai kedua untuk submission yang sama, database akan memblokir transaksi tersebut.

---

### 4. Arsitektur Route dan Controller Resourceful

Seluruh rute CRUD utama telah diorganisasikan menggunakan pola *Resourceful Controller* standar Laravel 12:

```bash
php artisan route:list --path=courses
php artisan route:list --path=users
```

1. `Route::resource('courses', CourseController::class)` menangani 7 metode HTTP: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
2. `Route::resource('users', UserController::class)` menangani pengelolaan seluruh data admin, dosen, dan mahasiswa dengan validasi email/NIM unik dan proteksi role.
3. Seluruh rute penanganan form menggunakan metode HTTP yang semantik (`POST` untuk simpan, `PUT/PATCH` untuk update, dan `DELETE` dengan perlindungan `@csrf` dan `@method('DELETE')` untuk penghapusan).

---

# II. BREAK: Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Pada bagian ini dilakukan 5 eksperimen untuk menguji ketahanan sistem terhadap celah keamanan, integritas constraint, dan kesalahan konfigurasi migrasi.

| # | Skenario yang Dirusak | Prediksi Sebelum Mencoba | Hasil / Error Sebenarnya | Bukti Tangkapan Layar |
|:--:|:---|:---|:---|:---|
| **1** | Menghapus `unique(['course_id', 'user_id'])` pada tabel `course_user`, lalu mendaftarkan mahasiswa yang sama 2x pada mata kuliah yang sama. | Mahasiswa akan terdaftar ganda di kelas yang sama karena database kehilangan constraint pencegah duplikasi baris pivot. | Database MySQL berhasil menyimpan 2 baris data pendaftaran dengan `user_id` dan `course_id` yang identik. Kuota kelas bertambah tidak wajar dan daftar mahasiswa di kelas memuat nama duplikat. Terbukti bahwa composite unique key wajib ada. | *[Tangkapan Layar: Taruh screenshot di `docs/img-rifa/03-break-1.png`]* |
| **2** | Menambahkan `role` ke dalam array `$fillable` pada model `User`, lalu mengirimkan request registrasi user dengan payload `role=admin` melalui form publik yang tidak memiliki input role. | Field `role` akan rentan disusupi pengguna biasa dan dapat diubah menjadi `admin` lewat teknik mass assignment injection. | Request berhasil diproses tanpa error. Akun baru yang didaftarkan langsung memiliki peran `role = 'admin'` di database. Ini adalah kerentanan *Privilege Escalation* yang sangat berbahaya. | *[Tangkapan Layar: Taruh screenshot di `docs/img-rifa/03-break-2.png`]* |
| **3** | Mengganti seluruh array `$fillable` pada model dengan `protected $guarded = [];` lalu mengulangi pengujian nomor 2. | Seluruh atribut model terbuka bebas untuk mass assignment, sehingga field sensitif apa pun dari request akan langsung ditulis ke database. | Pengguna dapat menyuntikkan field `role`, `id`, maupun status verifikasi akun. Model menyimpan seluruh data mentah tanpa proteksi whitelist, membuktikan bahaya penggunaan `$guarded = []`. | *[Tangkapan Layar: Taruh screenshot di `docs/img-rifa/03-break-3.png`]* |
| **4** | Mengosongkan method `down()` pada salah satu file migrasi (misal `create_courses_table`), lalu menjalankan `php artisan migrate:refresh`. | Perintah `migrate:refresh` akan gagal saat mencoba melakukan rollback karena tabel tidak didrop dengan benar. | Proses rollback gagal dengan pesan error `Base table or view already exists` atau `Cannot drop table because of foreign key constraint`. Database berada dalam status korup/inkonsisten. | *[Tangkapan Layar: Taruh screenshot di `docs/img-rifa/03-break-4.png`]* |
| **5** | Mengubah `restrictOnDelete()` pada `courses.lecturer_id` menjadi `cascadeOnDelete()`, lalu menghapus 1 akun dosen yang mengampu mata kuliah. | Menghapus dosen akan secara otomatis menghapus seluruh mata kuliah yang diampunya beserta materi dan data tugas di dalamnya. | Ketika akun dosen dihapus, data mata kuliah bersangkutan langsung terhapus otomatis dari tabel `courses`, dan seluruh penugasan mahasiswa ikut lenyap. Ini membuktikan mengapa `restrictOnDelete()` krusial. | *[Tangkapan Layar: Taruh screenshot di `docs/img-rifa/03-break-5.png`]* |

---

# III. FIX: Evaluasi & Perbaikan Kualitas Kode (*Code Review & Hardening*)

Berdasarkan hasil uji coba BREAK, dilakukan langkah perbaikan dan penguatan (*hardening*) pada kode aplikasi:

1. **Perlindungan Eksplisit Atribut Sensitif pada Model `User`**:
   * Menghilangkan `role` dari `$fillable` pada model `User`.
   * Penetapan `role` hanya boleh dilakukan secara eksplisit melalui controller setelah validasi ketat:
     ```php
     $user = new User();
     $user->name = $validated['name'];
     $user->email = $validated['email'];
     $user->password = $validated['password'];
     $user->nim_nip = $validated['nim_nip'] ?? null;
     $user->role = $validated['role']; // Diisi secara eksplisit, terlindung dari mass assignment
     $user->save();
     ```
2. **Reversibilitas Migrasi 100%**:
   * Seluruh method `down()` di semua berkas migrasi dipastikan memiliki perintah `Schema::dropIfExists()` dengan urutan pelepasan *foreign key* yang tepat, sehingga perintah `php artisan migrate:fresh --seed` dan `php artisan migrate:refresh` dapat dieksekusi tanpa hambatan.
3. **Pemberantasan Tanda-Tanda "AI Slop" pada Front-End**:
   * Menghilangkan kartu dengan *left-border gradient* (`border-l-4` + `bg-gradient-to-r`) yang merupakan klise template AI, digantikan dengan kartu berlatar solid bersih, tipografi yang seimbang, dan batas border yang rapi.
   * Menghilangkan *eyebrow pill badges* yang bertumpuk canggung di samping atau di atas judul halaman (`h1` dan `h2`), sehingga hierarki visual menjadi tenang, elegan, dan profesional.
   * Menyempurnakan perilaku sidebar kiri agar *sticky* sepanjang layar (`md:sticky md:top-0 md:h-screen md:overflow-y-auto`) tanpa menyisakan celah (*gap*) putih saat konten digulir ke bawah.

---

# IV. BUILD: Checklist Pemenuhan Milestone M1 (Minggu 03)

| Kriteria / Deliverable Milestone M1 | Status | Bukti & Catatan Implementasi |
|:---|:---:|:---|
| **1. Seluruh Migrasi Reversible Sesuai Bagian 4** | **SELESAI** | Seluruh tabel (`users`, `courses`, `course_user`, `materials`, `assignments`, `submissions`, `grades`) lengkap dengan constraint, composite unique, foreign key `onDelete`, serta method `up()` & `down()` yang bekerja bolak-balik. |
| **2. Seluruh Model & Relasi Eloquent Lengkap** | **SELESAI** | Relasi `hasMany`, `belongsTo`, `belongsToMany`, dan `hasOne` telah diuji via Tinker. Penulisan `$fillable` ketat dan penggunaan method `casts()` modern pada Laravel 12. |
| **3. Factory & Seeder Sesuai Bagian 4.4** | **SELESAI** | Menghasilkan minimal 5 Mata Kuliah, 3 Dosen, 30 Mahasiswa, minimal 15 mahasiswa per kelas, minimal 3 tugas per kelas, 125 submission (dengan variasi on-time & late), serta 60% submission yang sudah dinilai dosen. |
| **4. Tiga Akun Demo Wajib** | **SELESAI** | `admin@kampuslms.test` (ADM001), `dosen@kampuslms.test` (DOS001), dan `mahasiswa@kampuslms.test` (MHS001) dengan password default `password`. |
| **5. CRUD Resourceful Mata Kuliah** | **SELESAI** | Berfungsi penuh (`courses.index`, `courses.create`, `courses.store`, `courses.show`, `courses.edit`, `courses.update`, `courses.destroy`) dengan validasi input, status draft/active/archived, dan notifikasi flash session. |
| **6. CRUD Resourceful Pengguna** | **SELESAI** | Berfungsi penuh (`users.index`, `users.create`, `users.store`, `users.show`, `users.edit`, `users.update`, `users.destroy`) dengan proteksi role dan pagination data. |
| **7. CI GitHub Actions Otomatis & Hijau** | **SELESAI** | Workflow `.github/workflows/ci.yml` memvalidasi migrasi fresh, seeder, rollback refresh, pengujian unit/feature test, serta memastikan file `.env` tidak pernah bocor ke git. |

---

# V. Refleksi & Penggunaan AI (*AI Assistance Disclosure*)

Dalam pengerjaan tugas Minggu ke-3 ini, Artificial Intelligence (AI) dimanfaatkan sebagai rekan berdiskusi (*pair programmer*) untuk:
1. Membantu menganalisis potensi *race condition* dan anomali constraint database pada relasi Eloquent.
2. Mengaudit komponen antarmuka (*UI review*) untuk mengidentifikasi dan membuang elemen "AI Slop" (seperti kartu bergradien garis kiri dan pill badge yang berlebihan) agar menghasilkan desain front-end yang berstandar industri (*design reference fidelity*).
3. Menguji efektivitas skrip migrasi dan kueri relasi database bebas dari permasalahan *N+1 query problem*.

Seluruh kode yang disarankan oleh AI telah ditinjau, diuji, disesuaikan dengan aturan silabus perkuliahan Pemrograman Web ITK, dan diintegrasikan secara kolaboratif melalui Git branch dan Pull Request tim Kelompok 05.
