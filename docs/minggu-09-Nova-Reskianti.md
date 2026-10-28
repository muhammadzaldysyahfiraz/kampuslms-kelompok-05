# Catatan Praktikum Minggu 9: Upload File dan Data Dinamis

**Mata Kuliah:** Pemrograman Web
**Nama:** Nova Reskianti
**NIM:** 10241058

---

## READ: Menelusuri Satu Berkas

**Catatan:** Modul meminta mengunggah satu berkas **materi**. Namun fitur unggah materi belum ada di KampusLMS. `MaterialController::store` hanya menyimpan data teks, dan `materials/create.blade.php` tidak punya `<input type="file">`. Yang bisa diuji adalah unggah **submission** (pengumpulan tugas), jadi itu yang saya pakai. Cara kerjanya secara teknis sama.

### 1. Di mana berkas fisiknya? Apa path lengkapnya?

Saya mengunggah satu PDF lewat API:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/assignments/5/submissions \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@tugas-uji.pdf;type=application/pdf" -F "note=Peraga READ minggu 9"
# → HTTP 201, id=126, is_late=true
```

Hasil `find storage/app`:

```text
storage/app/private/submissions/5/TiMa2AAEG3hR56W3jJ2eBEvYOFEIAn8QXu607Oqj.pdf
```

Nama berkas fisiknya **acak**, bukan nama aslinya. Nama asli (`.tmp-tugas-uji.pdf`) hanya disimpan di database.

### 2. Bandingkan `file_path` di database dengan lokasi fisiknya.

```text
id=126  file_path=submissions/5/TiMa2AAEG3hR56W3jJ2eBEvYOFEIAn8QXu607Oqj.pdf
        original_name=.tmp-tugas-uji.pdf  size=209  is_late=1
abs=D:\...\kampuslms\storage\app/private/submissions/5/TiMa2AAE...pdf  exists=1
```

`file_path` di database adalah path **relatif** terhadap folder `storage/app/private` (root disk `local`). Kalau digabung dengan `storage_path('app/private/' . $submission->file_path)`, berkasnya ketemu (`exists=1`).

Sebelum saya mengunggah, `find storage/app` hanya menemukan `.gitignore`. Artinya **125 data submission dari seeder punya `file_path`, tetapi tidak ada berkas fisiknya.** Seeder hanya membuat baris database, bukan berkas. Jadi endpoint unduh yang akan dibuat harus siap menangani kasus "data ada, berkas tidak ada", dengan mengembalikan 404 (bukan error 500).

### 3. Coba akses berkas lewat browser dengan menebak URL. Berhasil atau tidak? Kenapa?

| URL yang ditebak (tanpa login) | Hasil | Alasan |
|---|---|---|
| `GET /storage/submissions/5/TiMa2AAE....pdf` (berkas di **private**, sebelum `storage:link`) | **403** | Belum ada symlink `public/storage`, jadi web server tidak menemukan berkasnya. Request diteruskan ke Laravel, lalu ditolak karena tidak punya signature (`config('filesystems.disks.local.serve') = true`). |
| URL yang sama setelah `storage:link` (berkas masih di private) | **403** | Symlink mengarah ke `storage/app/public`, sedangkan berkas kita ada di `storage/app/private`. |
| `/submissions/126` (halaman detail) tanpa login | **302 → /login** | Route ini dilindungi middleware autentikasi. |
| `GET /storage/submissions/5/...` setelah berkas **dipindah ke public** (percobaan BREAK #1) | **200 + isi PDF** | Ini kebocorannya. Penjelasan ada di BREAK. |

Jadi saat berkas ada di disk private, menebak URL gagal. Tetapi penyebabnya **bukan Policy**. Penyebabnya adalah web server memang tidak bisa menjangkau berkas itu. Perlindungan yang sebenarnya baru ada kalau nanti ada endpoint unduh yang memakai Policy.

### 4. Buka `config/filesystems.php`. Apa beda disk `local` dan `public`?

```php
'local' => [
    'driver' => 'local',
    'root'   => storage_path('app/private'),   // PRIVATE, tidak dijangkau web server
    'serve'  => true,                          // punya route sendiri, tapi butuh signature
],
'public' => [
    'driver' => 'local',
    'root'   => storage_path('app/public'),    // di bawah symlink public/storage
    'url'    => env('APP_URL').'/storage',     // bisa langsung diakses lewat URL
    'visibility' => 'public',
],
```

Bedanya: **disk `public` bisa diakses web server lewat symlink `public/storage` tanpa melewati Laravel. Disk `local` tidak bisa; semua aksesnya harus lewat kode aplikasi.**

Tambahan dari kode framework: Laravel 12 membuat route `GET /storage/{path}` untuk disk yang `serve => true`. Tetapi route itu mengecek signature. Tanpa signature hasilnya 403 (di lokal) atau 404 (di produksi). Jadi route itu bukan jalan pintas ke berkas privat.

### 5. Jalankan `php artisan storage:link`. Apa yang dibuatnya? Kenapa materi tidak boleh ada di sana?

```text
INFO  The [D:\...\public\storage] link has been connected to [D:\...\storage\app/public].
lrwxrwxrwx  storage -> .../storage/app/public
```

Perintah ini membuat **symbolic link** `public/storage` yang menunjuk ke `storage/app/public`. Akibatnya, semua berkas di `storage/app/public` bisa dibuka lewat `APP_URL/storage/nama-berkas` **tanpa login, tanpa Policy, dan tanpa signature**.

Materi kuliah dan submission tidak boleh ada di sana karena:

- (a) URL-nya bisa ditebak atau dibagikan.
- (b) Yang bisa mengakses bukan hanya pengguna aplikasi, tetapi siapa saja yang punya tautannya.

Buktinya ada di BREAK #1.

---

## BREAK: Delapan Kerusakan

### 1. Memindahkan berkas submission ke `storage/app/public`, lalu mengaksesnya tanpa login
✅ Dibuktikan langsung (nomor wajib dari modul)

Saya menyalin berkas ke `public/submissions/5/`, lalu menjalankan `curl` **tanpa cookie**. `GET /storage/submissions/5/TiMa2AAE....pdf` menghasilkan **200** dan isi `%PDF-1.4 ...`. Jadi **tugas mahasiswa bisa dibuka siapa saja dalam 10 detik.** Berkas yang sama di disk private menghasilkan 403.

Penyebabnya: web server langsung menyajikan berkas statis yang ada, **sebelum** Laravel memproses request. Jadi tidak ada middleware dan tidak ada Policy yang sempat bekerja.

### 2. Menghapus `Gate::authorize()` dari endpoint download, lalu mengakses submission orang lain
⚠️ Tidak bisa diperagakan persis; ini temuan

**Endpoint download belum ada sama sekali di KampusLMS** (`artisan route:list | grep -c download` → `0`, dan `GET /submissions/1/download` → 404). Kondisi nyatanya:

- `grep -rn "Gate::authorize" app/` menghasilkan **nol**. Otorisasi di aplikasi ini ditulis langsung dengan `abort_unless` dan `FormRequest::authorize()`, bukan Gate.
- Halaman detail submission milik orang lain **tetap tertutup**. `GET /submissions/1` (milik user 10) dibuka sebagai mahasiswa demo → **403**. Milik sendiri (`/submissions/2`) → **200**.

Hal yang perlu diperhatikan: saat endpoint unduh dibuat nanti, baris pertamanya **wajib** `Gate::authorize('view', $submission)`. Karena pola ini belum ada di kode, mudah sekali terlupa.

### 3. Menyimpan berkas dengan `storeAs()` memakai nama asli, lalu dua mahasiswa mengunggah `tugas.pdf` yang berbeda
✅ Dibuktikan (simulasi filesystem)

Dua unggahan bernama `tugas.pdf` masuk ke folder yang sama. Isi berkas akhirnya `"ISI TUGAS MAHASISWA KEDUA"`, jadi **berkas mahasiswa pertama tertimpa**.

Di KampusLMS hal ini tidak bisa terjadi. `Api\SubmissionController::store()` memakai `$file->store("submissions/{$id}", 'local')`, yang membuat nama acak. Buktinya dari unggahan di READ (`TiMa2AAE....pdf`, bukan `.tmp-tugas-uji.pdf`). Nama asli disimpan di kolom `original_name`.

### 4. Mengunggah berkas PHP yang diganti nama menjadi `.pdf`, dengan rule `extensions` dan `mimes`
✅ Dibuktikan langsung + validator

Saya membuat berkas dengan `printf '<?php system($_GET["c"]); ?>' > tugas.pdf`, lalu mengunggahnya dengan validasi `mimes:pdf,doc,docx,zip`. Hasilnya **422: "Berkas harus berformat PDF, DOC, DOCX, atau ZIP."** Laravel membaca isi berkas, dan ternyata bukan PDF.

Saya juga membandingkan kedua rule pada berkas yang sama di luar HTTP:

- `mimes:pdf` → **ditolak**
- `extensions:pdf` → **diterima (lolos)**

`extensions` hanya mencocokkan tulisan ekstensi pada nama berkas, jadi penyerang cukup mengganti `shell.php` menjadi `shell.pdf`. Kode kita memakai `mimes`, jadi aman.

### 5. Mengubah `max:5120` menjadi `max:5`, lalu mengunggah berkas 1 MB
✅ Dibuktikan (validator, 1 MB)

- `max:5` → **ditolak**
- `max:5120` → **diterima**

Satuan `max` adalah **kilobyte**. Jadi 5 berarti 5 KB (berkas 1 MB jelas melebihinya), dan 5120 berarti 5 MB. Di KampusLMS, `max:10240` = **10 MB**, dan pesan errornya juga menulis "maksimal 10 MB", jadi konsisten.

### 6. Mengecilkan `upload_max_filesize` di php.ini, lalu mengunggah berkas besar dengan validasi `max` yang besar
✅ Dibuktikan langsung, dan ada kasus serupa di lingkungan ini

Hasil `php -i` lokal: `upload_max_filesize = 2M`, `post_max_size = 8M`. Saya mengunggah berkas **3 MB** dengan validasi `max:10240` (10 MB). Hasilnya **422 "The file failed to upload."** Pesan ini kurang jelas, karena kalau divalidasi sendiri, rule `max:10240` sebenarnya **menerima** berkas 3 MB itu. Jadi yang menolak **bukan Laravel**, tetapi PHP, sebelum Laravel sempat memeriksanya.

Kasus serupa terjadi juga saat saya menyiapkan peraga ini. Server `artisan serve` lokal menolak semua unggahan dengan error `File upload error - unable to create a temporary file`. Penyebabnya, proses anak tidak mewarisi variabel `TEMP`, sehingga PHP tidak bisa memakai folder sementara. Gejalanya sama seperti peringatan di modul: unggahan gagal di lapisan server, sebelum validasi aplikasi berjalan.

### 7. Menghapus `updateOrCreate`, menggantinya dengan `create`, lalu mengumpulkan tugas dua kali
✅ Dibuktikan langsung

Kode KampusLMS berbeda dari modul. `Api\SubmissionController::store()` tidak memakai `updateOrCreate`. Ada pengecekan langsung: `if ($assignment->submissions()->where('user_id', $user->id)->exists()) abort(409)`. Unggahan kedua → **409 "Anda sudah mengumpulkan tugas ini."** Jadi pengumpulan ulang ditolak, bukan menimpa.

Lapis pengaman kedua saya uji dengan melewati pengecekan aplikasi, yaitu memasukkan data duplikat **langsung ke database**. Hasilnya **`SQLSTATE[23000]: UNIQUE constraint failed`**. Constraint unik gabungan `(assignment_id, user_id)` dari migrasi minggu 3 menolaknya, sesuai yang diramalkan modul. (Percobaan pertama saya sempat kena error `NOT NULL` karena kolom `original_name` dan `file_size` wajib diisi. Setelah kolom dilengkapi, baru tertahan oleh UNIQUE.)

### 8. Menghapus satu submission dari database lewat SQL langsung, lalu mengecek folder storage
✅ Dibuktikan langsung

`DB::table('submissions')->where('id',126)->delete()` (tanpa Eloquent) menghapus barisnya, tetapi **berkas fisiknya TETAP ADA**: `Berkas fisik masih ada: YA (BERKAS YATIM)`.

Ada dua penyebab:

- (a) `app/Models/Submission.php` **tidak punya hook `booted()`/`deleting`**. Hasil grep di seluruh `app/` hanya menemukan `Storage::disk('local')->delete($path)` di blok `catch` saat upload gagal, bukan saat data dihapus.
- (b) `cascadeOnDelete` di migrasi menghapus baris di level database, sehingga **event model tidak pernah dipicu**. Kalau ada penghapusan massal lewat relasi, semua berkasnya menjadi yatim.

> Repo `kampuslms-broken` tidak tersedia di workspace saya (sama seperti catatan minggu 6–7). Jadi semua percobaan dilakukan langsung pada KampusLMS kami, dengan manipulasi terkontrol (memindahkan berkas, menghapus baris lewat SQL, menyisipkan file uji). **Semuanya sudah dibersihkan kembali.**

---

## FIX: Repo Cacat Branch `w09` (7 Masalah) dan Pemetaannya ke KampusLMS

### 1. Submission disimpan di disk publik
✅ Sudah benar

`Api\SubmissionController::store()` memakai `$file->store("submissions/{$assignment->id}", 'local')`. Disk `local` = `storage/app/private`. Buktinya dari READ: berkas tidak bisa dijangkau lewat URL, sampai saya memindahkannya secara paksa.

### 2. Endpoint download tanpa `Gate::authorize()`
❌ Endpoint-nya belum ada

`route:list` tidak punya route download sama sekali. Ini bukan "rusak", tetapi fitur yang belum dibuat. Karena kode kita tidak punya pola `Gate::authorize()`, saat dibuat nanti harus disiplin: `abort_unless` atau `Gate::authorize('view', …)` harus jadi baris pertama, sebelum `Storage::exists()`.

### 3. `storeAs()` memakai nama asli
✅ Sudah benar

Kita memakai `store()` (nama acak) dan kolom `original_name` untuk tampilan. BREAK #3 membuktikan bagaimana nama yang bertabrakan bisa menimpa berkas, dan kode kita tidak punya masalah itu.

### 4. Validasi hanya memeriksa ekstensi
✅ Sudah benar

`StoreSubmissionRequest`: `'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'max:10240']`. `mimes` membaca isi berkas. BREAK #4 membuktikan file PHP ditolak dengan 422.

### 5. `max` salah satuan
✅ Sudah benar

`max:10240` = 10 MB, dan pesannya `"file.max" => "Ukuran berkas maksimal 10 MB."`. Angka, satuan, dan pesan sudah cocok.

### 6. Berkas fisik tidak dihapus
❌ Masih bermasalah di KampusLMS

`Submission` tidak punya `booted()`/`deleting`. BREAK #8 membuktikan berkas yatim tertinggal. Perbaikannya harus menangani dua kasus:

- Penghapusan lewat model → pakai hook `deleting`.
- Penghapusan data induk (cascade dari `assignments`/`courses`, yang tidak memicu event) → hapus berkas secara eksplisit di controller.

### 7. Komponen Livewire menyembunyikan tombol tanpa otorisasi di server
⚠️ Bentuknya berbeda, masalahnya mirip

KampusLMS tidak memakai komponen Livewire (package terpasang, tetapi tidak ada `app/Livewire` atau `resources/views/livewire`). Masalah padanannya justru lebih nyata: tombol "Kumpulkan Tugas" di `assignments/show.blade.php:145` memakai `@can('create', \App\Models\Submission::class)`. Padahal `SubmissionPolicy::create(User $user, Assignment $assignment)` butuh **2 argumen**. Akibatnya halaman tugas untuk mahasiswa **error 500** (`ArgumentCountError: Too few arguments ... exactly 2 expected`).

Untungnya `StoreSubmissionRequest::authorize()` (di sisi server) tetap menolak request yang tidak sah. Ini sesuai pelajaran modul: keputusan otorisasi harus ada di server, sedangkan tampilan UI hanya untuk kenyamanan.

**Pekerjaan besar yang tersisa (setara PR `w09`):** endpoint download submission dan materi dengan Policy, `booted()` untuk menghapus berkas, dan perbaikan `@can` di atas.

---

## BUILD: Alur Berkas KampusLMS (Status Jujur, 20 Okt 2026)

### 1. Unggah materi oleh dosen (PDF/PPTX/DOCX, maks 20 MB, disk privat, atau tautan eksternal)
❌

`MaterialController::store()` hanya memvalidasi `title/description/type/external_url`, jadi **tidak ada field berkas**. `materials/create.blade.php` tidak punya `<input type="file">`. Pesan di kode: *"Unggah berkas belum termasuk Week 5."* Jalur tautan eksternal (`type=link` + `url`) sudah berjalan.

### 2. Unduh materi oleh mahasiswa terdaftar, lewat controller + Policy
❌

Belum ada route unduh untuk materi maupun submission. Saat dibuat nanti: `Gate::authorize()` + `Storage::disk('local')->exists()` + kirim dengan `original_name`.

### 3. Pengumpulan tugas: `updateOrCreate`, `is_late` dihitung server, tolak jika `allow_late` false dan sudah lewat deadline
⚠️

- `is_late` dihitung server ✅ (`now()->greaterThan($assignment->due_at)`). Bukti: deadline 13 Okt, unggahan 20 Okt → `is_late=true`.
- Penolakan saat `allow_late=false` ✅
- **`updateOrCreate` ❌**: kode memakai cek `exists()` lalu **409**, jadi mahasiswa tidak bisa mengumpulkan ulang (berkas lama tidak pernah ditimpa).

Tiga poin sudah jalan, tetapi revisi ulang belum bisa.

### 4. Unduh submission: hanya pemilik, dosen pengampu, dan admin
❌

Endpoint-nya belum ada. Yang **sudah** ada untuk membatasi akses:

- `SubmissionController::show` → `abort_unless(pemilik ∥ admin ∥ dosen pengampu)` (terbukti 403/200 di BREAK #2)
- `SubmissionPolicy::view()`
- `StoreSubmissionRequest::authorize()` untuk jalur tulis
- `Api\SubmissionController::index` hanya untuk dosen pengampu

### 5. Daftar pengumpulan untuk dosen: siapa sudah/belum, ditandai terlambat
✅

- `GET /api/v1/assignments/{id}/submissions` (hanya dosen pengampu, `with('student','grade')`, `paginate(15)`, resource memuat `is_late`)
- Ringkasan "Sudah/Belum mengumpulkan" di `assignments/show` (`submittedCount`)
- Badge "Terlambat" di `submissions/show`

### 6. Penghapusan berkas fisik saat data dihapus, termasuk penanganan cascade
❌

Belum ada (BREAK #8 buktinya). Rencana: hook `deleting` pada `Submission`, **dan** penghapusan eksplisit saat data induk dihapus (karena cascade database tidak memicu event model).

### 7. Tampilkan `original_name` dan ukuran berkas dalam format yang mudah dibaca
⚠️

- `original_name` sudah tampil ✅ (`submissions/show.blade.php` dan `SubmissionResource`).
- `file_size` dikirim API sebagai **angka bytes biasa** (misalnya `209`) dan **tidak ditampilkan** di halaman web. Belum diformat (misalnya `1,2 MB`).

---

