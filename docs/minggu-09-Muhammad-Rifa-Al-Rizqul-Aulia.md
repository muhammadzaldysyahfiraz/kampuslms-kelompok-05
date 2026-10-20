# Catatan Praktikum Minggu 9 Pemrograman Web

---

**Nama Mahasiswa:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05   

---

# READ: Arsitektur Pengelolaan Berkas Privat, Keamanan Unggahan, dan Data Dinamis di Laravel 12

Pada Minggu ke-9 ini, fokus pengembangan KampusLMS beralih ke penanganan aset pengguna yang dinamis: **Pengelolaan Unggahan Berkas Privat (*Private File Storage*)**, **Validasi Keamanan Konten (*MIME Magic Bytes*)**, **Pencegahan Akses Ilegal (*IDOR Download Protection*)**, serta **Penyajian Data Dinamis Berbasis Peran pada Antarmuka Blade**.

Sebagai salah satu frontend developer di kelompok, pemahaman mendalam mengenai daur hidup berkas dari antarmuka pengguna (`<form enctype="multipart/form-data">`), penanganan penyimpanan di sistem berkas server, hingga streaming berkas terotorisasi menjadi fondasi mutlak agar sistem tidak mengalami kebocoran data akademik.

---

### 1. Klasifikasi Berkas: Publik vs Privat

Salah satu keputusan arsitektural paling krusial pada penanganan berkas adalah menentukan lokasi penyimpanan dan rute aksesnya:

| Karakteristik | Berkas Publik (*Public Files*) | Berkas Privat (*Private Files*) |
|---|---|---|
| **Contoh di KampusLMS** | Foto avatar profil pengguna, logo institusi ITK, favicon sistem. | Berkas modul materi kuliah (PDF/PPTX/DOCX), berkas jawaban tugas mahasiswa. |
| **Lokasi Fisik di Server** | `storage/app/public/...` | `storage/app/private/...` (disk `'local'`). |
| **Jalur Simbolik (`storage:link`)** | Terhubung ke direktori publik web server (`public/storage`). | **TIDAK PERNAH** terhubung ke symlink publik. |
| **Mekanisme Akses** | URL langsung via peramban web (misal: `https://lms.itk.ac.id/storage/avatars/1.png`). | Melalui controller terproteksi yang mengevaluasi Policy (`Gate::authorize()`). |
| **Dampak Jika Salah Tempat** | Aset umum dapat diakses siapa saja tanpa autentikasi (sesuai peruntukan). | **BOCOR TOTAL:** Berkas jawaban tugas dan silabus dapat diunduh siapa pun yang mengetahui tautannya tanpa login. |

> **Mengapa Berkas Akademik DILARANG Disimpan di `storage/app/public`?**  
> Direktori `storage/app/public` diekspos langsung oleh web server (Nginx/Apache) melalui symlink `public/storage`. Pada berkas publik, seluruh lapisan aplikasi Laravel (middleware, autentikasi sesi, Policy, dan database query) **dilewati sepenuhnya (*bypassed*)**. Jika tugas mahasiswa disimpan di disk publik, penyerang cukup menebak atau mengiterasi nama berkas (misal: `/storage/submissions/1/tugas.pdf`), dan server akan langsung menyajikan dokumen tersebut kepada publik tanpa meminta login.

---

### 2. Anatomi Unggahan Privat & Pengacakan Nama Berkas Fisik

Ketika berkas diunggah melalui formulir HTML, data biner dikirimkan via stream HTTP `multipart/form-data`. Di sisi controller (`SubmissionController@store` dan `MaterialController@store`):

```php
$file = $request->file('file');

// Disimpan di storage/app/private/submissions/{assignment_id}/{hash_acak}.pdf
$storedPath = $file->store("submissions/{$assignment->id}", 'local');
```

Tiga prinsip penting yang diterapkan di KampusLMS:
1. **Nama Berkas Fisik Dihash Secara Acak:**  
   Metode `$file->store()` secara otomatis menghasilkan string hash unik 40 karakter (SHA-1). Nama asli berkas yang diberikan pengguna (`getClientOriginalName()`) **tidak pernah dipakai sebagai nama file di disk**. Hal ini mencegah:
   - **Serangan *Path Traversal*:** Mengunggah berkas dengan nama `../../etc/passwd` atau `../../../shell.php`.
   - **Tumpang-Tindih Berkas (*File Overwrite Collision*):** Menghindari berkas milik Mahasiswa B menimpa berkas milik Mahasiswa A jika keduanya menamai dokumen mereka `Tugas_Pemweb.pdf`.
   - **Eksekusi Script Berbahaya:** Mencegah peramban atau server mengeksekusi script yang berkedok nama ganda (`laporan.php.pdf`).
2. **Penyimpanan Metadata Terpisah:**  
   Nama asli (`original_name`), ukuran berkas (`file_size`), dan MIME type (`mime_type`) disimpan ke tabel basis data agar saat diunduh, pengguna tetap menerima nama berkas aslinya.
3. **Pemberian Jalur Berdasarkan Entitas Induk:**  
   Direktori penyimpanan dikelompokkan berdasarkan ID modul induk, misal `materials/{course_id}` dan `submissions/{assignment_id}` untuk kemudahan pengarsipan data.

---

### 3. Validasi Keamanan Unggahan: `mimes` vs `extensions`

Validasi berkas pada Laravel harus dilakukan secara cermat agar tidak terkecoh oleh manipulasi ekstensi di sisi klien:

```php
$request->validate([
    'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
]);
```

* **Pemeriksaan Isi Berkas (*Magic Bytes Detection*):**  
  Aturan `mimes:pdf,doc,docx` menggunakan pustaka PHP `finfo` (Fileinfo) untuk membaca header biner (*magic bytes*) berkas. Contoh: berkas PDF selalu diawali dengan byte biner `%PDF-`. Jika penyerang membuat script PHP berbahaya (`malicious.php`) lalu mengubah ekstensinya menjadi `malicious.pdf`, aturan `mimes` akan mendeteksi bahwa MIME sebenarnya adalah `text/x-php` atau `text/plain` dan langsung menolak berkas tersebut.
* **Perbedaan dengan `extensions`:**  
  Aturan `extensions:pdf,docx` (tersedia sejak Laravel 10) hanya mencocokkan string ekstensi nama berkas tanpa membaca isinya. Oleh karena itu, pengamanan sistem wajib memprioritaskan `mimes`.
* **Satuan Ukuran Berkas `max:5120`:**  
  Aturan `max` untuk jenis `file` di Laravel dihitung dalam satuan **Kilobyte (KB)**, bukan Megabyte (MB) atau Byte. Nilai `5120` berarti $5120 \text{ KB} = 5 \text{ MB}$.
* **Batasan Konfigurasi PHP Engine (`php.ini`):**  
  Validasi Laravel berada di lapisan aplikasi. Jika konfigurasi `upload_max_filesize` atau `post_max_size` pada `php.ini` lebih kecil daripada batas Laravel (misal bernilai `2M`), maka web server/PHP akan memotong request sebelum mencapai router Laravel, menghasilkan error kosong atau kegagalan yang membingungkan.

---

### 4. Unduh Berkas Privat Terotorisasi (*Controlled Download Streaming*)

Karena berkas privat tidak memiliki URL publik langsung, unduhan wajib dialirkan melalui endpoint controller yang diproteksi oleh Policy:

```php
public function download(Submission $submission): StreamedResponse
{
    // 1. Verifikasi Kebijakan Akses (Otorisasi)
    Gate::authorize('view', $submission);

    // 2. Pastikan jejak berkas tercatat dan berkas fisik ada di disk privat
    abort_unless($submission->file_path, 404, 'Berkas tidak tercatat.');
    $disk = Storage::disk('local');
    abort_unless($disk->exists($submission->file_path), 404, 'Berkas fisik tidak ditemukan.');

    // 3. Alirkan berkas ke browser dengan nama asli
    return $disk->download(
        $submission->file_path,
        $submission->original_name ?: 'submission-' . $submission->id . '.pdf'
    );
}
```

* **Pencegahan IDOR (Insecure Direct Object Reference):**  
  Panggilan `Gate::authorize('view', $submission)` mengevaluasi aturan di [`SubmissionPolicy`](file:///d:/ITK/SEMESTER%205/PEMROGRAMAN%20WEB/kampuslms/app/Policies/SubmissionPolicy.php):
  - Mahasiswa pemilik tugas (`$user->id === $submission->user_id`) $\rightarrow$ **Diizinkan**.
  - Dosen pengampu mata kuliah terkait $\rightarrow$ **Diizinkan**.
  - Administrator sistem $\rightarrow$ **Diizinkan**.
  - Mahasiswa lain $\rightarrow$ **HTTP 403 Forbidden**.
* **Streaming Tanpa Membebani RAM:**  
  Metode `$disk->download()` menghasilkan `StreamedResponse` yang membaca dan mengalirkan paket berkas dalam potongan kecil (*chunks*), sehingga berkas berukuran besar (misal modul 20 MB) tidak dimuat sekaligus ke dalam memori RAM PHP.

---

### 5. Daur Hidup Berkas & Pembersihan Berkas Fisik Yatim (*Orphaned Files*)

Masalah umum pada sistem manajemen berkas adalah timbulnya *orphaned files* (berkas fisik yang tersisa di storage namun barisnya sudah dihapus dari database, atau berkas sisa saat mahasiswa mengunggah ulang revisi tugas).

#### A. Pembersihan Saat Pengumpulan Ulang (*Resubmission*)
Ketika mahasiswa mengumpulkan berkas baru untuk tugas yang sama, sistem mengambil path berkas lama, menyimpan berkas baru, lalu menghapus berkas lama:
```php
$existing = Submission::where('assignment_id', $assignment->id)->where('user_id', $user->id)->first();
$oldPath = $existing?->file_path;

// Simpan berkas baru...
// Jika sukses dan berkas fisik lama ada:
if ($oldPath && $oldPath !== $storedPath) {
    Storage::disk('local')->delete($oldPath);
}
```

#### B. Pembersihan Otomatis via Model Deleting Event
Pada model `Submission` dan `Material`, dipasang lifecycle hook:
```php
protected static function booted(): void
{
    static::deleting(function (Submission $submission) {
        if ($submission->file_path) {
            Storage::disk('local')->delete($submission->file_path);
        }
    });
}
```

> **Waspada `cascadeOnDelete` di Level Database:**  
> Ketika sebuah mata kuliah dihapus dan relasi migration menggunakan `cascadeOnDelete()`, basis data menghapus baris tugas dan submission secara langsung di level SQL mesin basis data. **Pada kondisi ini, event Eloquent `deleting` pada model anak TIDAK terpicu.** Oleh karena itu, jika operasi penghapusan induk dijalankan melalui controller, berkas anak harus dihapus secara eksplisit sebelum entitas induk dimusnahkan.

---

### 6. Integritas Data Penugasan

1. **Idempotensi via `firstOrNew` / `updateOrCreate`:**  
   Menegakkan integritas *composite unique* `(assignment_id, user_id)` yang telah dirancang sejak Minggu 3. Mahasiswa yang mengumpulkan ulang tidak membuat baris baru, melainkan memperbarui data submission yang sudah ada.
2. **Kalkulasi Keterlambatan di Sisi Server (`is_late`):**  
   Waktu pengumpulan dihitung menggunakan jam server (`now()`), lalu dibandingkan dengan batas waktu tugas (`$assignment->due_at`). Waktu dari perangkat klien atau browser tidak pernah dipercaya karena zona waktu dan jam sistem pengguna dapat dimanipulasi dengan mudah.
3. **Penegakan Kebijakan `allow_late`:**  
   Jika `now()->gt($assignment->due_at)` bernilai `true` dan tugas menyetel `allow_late == false`, request langsung ditolak dengan pesan kesalahan validasi sebelum berkas sempat disimpan di disk.

---

# BREAK: Delapan Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Sesuai instruksi Modul 9.3 (BREAK), dilakukan 8 eksperimen pengujian kegagalan terencana untuk membuktikan kerentanan dan memverifikasi ketahanan sistem pengelolaan berkas KampusLMS:

| # | Yang Dicoba / Skenario Kerusakan | Prediksi Sebelum Menguji | Hasil & Konsekuensi Sebenarnya |
|:--:|:---|:---|:---|
| **1** | Memindahkan berkas submission ke direktori publik `storage/app/public` dan mengakses URL-nya langsung dari browser Incognito tanpa login. | Berkas dapat diunduh bebas tanpa autentikasi karena bypass middleware Laravel. | **Tugas Seluruh Kelas Terbuka Bebas:** File langsung terunduh via URL statis `http://127.0.0.1:8000/storage/submissions/1/jawaban.pdf`. Siapa pun yang mendapatkan URL atau melakukan *directory traversal* dapat mencuri jawaban tugas mahasiswa lain. Disk publik mutlak dilarang untuk berkas privat. |
| **2** | Menghapus pemanggilan `Gate::authorize('view', $submission)` pada method `download()` di `SubmissionController`. | Mahasiswa lain dapat mengunduh berkas jawaban temannya cukup dengan mengubah angka ID submission di URL. | **Celah IDOR Kritis Terbuka:** Mahasiswa B dapat mengunduh berkas tugas Mahasiswa A melalui `GET /submissions/1/download`. Respon mengembalikan `HTTP 200 OK` padahal bukan haknya. `Gate::authorize()` wajib dipertahankan. |
| **3** | Mengubah logika penyimpanan menggunakan `storeAs()` dengan nama asli pengguna (`$file->getClientOriginalName()`), lalu dua akun mahasiswa mengunggah berkas dengan nama `tugas.pdf`. | Berkas mahasiswa kedua akan menimpa berkas mahasiswa pertama karena jalur dan nama filenya identik. | **Kerusakan Integritas Data Fatal:** Berkas fisik Mahasiswa A tertimpa oleh isi berkas Mahasiswa B. Ketika dosen mengunduh tugas Mahasiswa A, isi yang muncul adalah jawaban Mahasiswa B. Metode `store()` dengan nama acak wajib digunakan. |
| **4** | Mengunggah berkas script PHP berbahaya (`webshell.php`) yang diganti namanya menjadi `webshell.pdf`, diuji dengan validasi `extensions:pdf` vs `mimes:pdf`. | Validasi `extensions` akan lolos, sedangkan validasi `mimes` akan memblokir karena mendeteksi magic bytes teks PHP. | Pada aturan `extensions:pdf`, berkas lolos validasi dan tersimpan di server. Pada aturan `mimes:pdf`, Laravel membaca konten file dan menolak unggahan dengan error: *"Format berkas harus berupa PDF, DOC, atau DOCX."* |
| **5** | Mengubah aturan validasi dari `max:5120` menjadi `max:5`, lalu mengunggah berkas PDF tugas berukuran 1 MB. | Unggahan akan ditolak karena sistem mengira batas maksimalnya adalah 5 Kilobyte (5120 Byte). | Berkas 1 MB gagal diunggah dengan pesan validasi *"Ukuran berkas maksimal adalah 5 KB"*. Ini membuktikan satuan aturan `max` untuk berkas di Laravel adalah Kilobyte, bukan Megabyte. |
| **6** | Mengatur `upload_max_filesize = 1M` pada konfigurasi `php.ini`, lalu mengunggah berkas 3 MB dengan aturan validasi Laravel `max:5120` (5 MB). | Server PHP akan menolak berkas sebelum Laravel sempat menjalankan validasi Form Request. | Unggahan gagal secara tiba-tiba tanpa pesan validasi khas Laravel. Variabel `$_FILES` kosong dan request payload terpotong. Batas konfigurasi engine PHP terbukti mengalahkan validasi framework. |
| **7** | Menghapus penanganan pengumpulan ulang (`updateOrCreate`), lalu menggantinya dengan `Submission::create()`, kemudian mahasiswa mengumpulkan tugas dua kali. | Percobaan kedua akan memicu exception pelanggaran integritas basis data. | Muncul error fatal: `SQLSTATE[23000]: Integrity constraint violation: UNIQUE constraint failed: submissions.assignment_id, submissions.user_id`. Logika pengumpulan ulang wajib menggunakan mekanisme pembaruan record. |
| **8** | Menghapus satu baris submission langsung melalui query database SQL (`DELETE FROM submissions WHERE id = 1`), lalu memeriksa folder `storage/app/private`. | Berkas fisik PDF submission tetap tertinggal di hard drive server menjadi berkas yatim. | **Timbul Berkas Yatim (*Orphaned File*):** File fisik di direktori server tetap ada padahal datanya sudah tidak ada di basis data. Ini membuktikan operasi SQL langsung atau cascade DB melewati model hook Eloquent. |

---

# FIX: Analisis dan Penanganan Masalah (*Code Hardening*)

Berdasarkan temuan pengujian terencana di atas, berikut adalah langkah standardisasi dan mitigasi yang diimplementasikan pada basis kode KampusLMS:

### 1. Isolasi Disk Privat `local` & Pengacakan Nama Berkas Fisik
Seluruh berkas materi dan tugas dialihkan secara mutlak ke disk `'local'` (`storage/app/private`). Penamaan fisik berkas diserahkan sepenuhnya kepada algoritma hash Laravel:
```php
// app/Http/Controllers/SubmissionController.php
$storedPath = $file->store("submissions/{$assignment->id}", 'local');
```
Metadata asli tetap dicatat di kolom `original_name` dan dipotong maksimal 255 karakter (`Str::limit($file->getClientOriginalName(), 255, '')`) demi menghindari buffer overflow pada skema basis data.

### 2. Implementasi Otorisasi Berlapis (*Dual-Layer Authorization*)
Pengamanan hak akses berkas diterapkan pada dua titik sekaligus:
* **Lapisan Server-Side (Gerbang Utama):** `Gate::authorize('view', $submission)` di method `download()`. Mengembalikan respon **`403 Forbidden`** jika diakses oleh pihak yang tidak sah.
* **Lapisan Client-Side (Tampilan Blade):** Direktif `@can('view', $submission)` digunakan untuk menampilkan tombol aksi secara kondisional guna menjaga kerapian antarmuka.

### 3. Mitigasi Berkas Yatim (*Lifecycle Hooks & Resubmission Cleanup*)
* **Pada Pengumpulan Ulang:** Controller mendeteksi keberadaan berkas lama (`$oldPath`) dan menghapusnya dari disk privat segera setelah transaksi penyimpanan berkas baru berhasil.
* **Pada Penghapusan Baris:** Model `Submission` dan `Material` mengimplementasikan method `booted()` dengan listener `deleting` yang memanggil `Storage::disk('local')->delete($model->file_path)`.
* **Rollback Berkas Fisik:** Jika terjadi kegagalan query database saat penyimpanan (`Throwable $e`), blok `try-catch` segera menghapus berkas fisik yang baru saja disimpan agar storage tidak kotor.

### 4. Penanganan Keamanan *Mass-Assignment* pada Model
Model `Submission` dan `Material` sengaja membatasi atribut `$fillable` (hanya `note` pada `Submission`, dan informasi teks umum pada `Material`) untuk mencegah eksploitasi parameter HTTP. 

Oleh karena itu, pengisian foreign keys (`assignment_id`, `user_id`, `course_id`, `uploaded_by`) dan jalur berkas internal (`file_path`, `is_late`, `submitted_at`) diisi secara eksplisit melalui penugasan atribut model langsung pada controller, bukan di-pass secara mentah melalui array `updateOrCreate()`.

### 5. Format Ukuran Berkas Ramah Pengguna
Agar ukuran berkas mudah dipahami oleh mahasiswa dan dosen, digunakan helper bawaan Laravel `Number::fileSize()` pada tampilan Blade:
```blade
{{ \Illuminate\Support\Number::fileSize($material->file_size) }}
```
Mengubah representasi byte mentah (`1048576 B`) menjadi format yang manusiawi (`1 MB`).

---

# BUILD: Implementasi Fitur Unggah Berkas & Antarmuka Dinamis

Berikut adalah rincian komponen antarmuka, formulir interaktif, dan integrasi data dinamis yang dibangun pada Minggu 9:

### 1. Antarmuka Modul Materi Perkuliahan (Dosen)

* **Formulir Tambah & Edit Materi (`materials/create.blade.php` & `materials/edit.blade.php`):**
  * Ditambahkan atribut `enctype="multipart/form-data"` pada tag form untuk mengaktifkan transmisi biner.
  * **Interaktivitas Dynamic Switcher (JavaScript):** Disediakan toggle radio/pilihan antara tipe **"Tautan Eksternal"** (*Link*) dan **"Unggah Berkas"** (*File*). Kontainer input URL atau input berkas akan muncul dan tersembunyi secara otomatis berdasarkan pilihan pengguna:
    ```javascript
    function toggleMaterialType(type) {
        if (type === 'file') {
            fileContainer.classList.remove('hidden');
            urlContainer.classList.add('hidden');
        } else {
            fileContainer.classList.add('hidden');
            urlContainer.classList.remove('hidden');
        }
    }
    ```
  * Input berkas dibatasi pada ekstensi `.pdf,.pptx,.docx` dengan batas ukuran hingga 20 MB (`20480 KB`).
  * Pada halaman edit, jika materi sudah memiliki berkas fisik tersimpan, antarmuka menampilkan kotak informasi berkas saat ini (*Current File Info*) lengkap dengan nama asli, ukuran berkas, dan catatan bahwa pengunggahan berkas baru akan otomatis menggantikan berkas lama.

* **Aksi Unduh Materi (`materials/show.blade.php` & `courses/show.blade.php`):**
  * Pada halaman detail materi dan daftar materi perkuliahan, disediakan tombol aksi primer **"Unduh Berkas"** dengan ikon unduhan yang menghubungkan ke rute aman `route('materials.download', $material)`.
  * Menampilkan informasi ukuran berkas yang terformat (`Number::fileSize`).

---

### 2. Antarmuka Pengumpulan Tugas Mahasiswa (`assignments/show.blade.php`)

Halaman detail tugas dirancang adaptif berdasarkan peran pengguna yang sedang login:

* **Sisi Mahasiswa (Pengumpulan & Revisi Tugas):**
  * Menggantikan teks statis (*placeholder*) lama dengan panel pengumpulan tugas interaktif.
  * **Status Pengumpulan:** Menampilkan kartu ringkasan status tugas:
    - Label *Badge* status: `"Belum Mengumpulkan"` (abu-abu/kuning), `"Sudah Dikumpulkan"` (hijau), `"Terlambat"` (merah).
    - Batas waktu pengumpulan beserta peringatan sisa waktu atau status kedaluwarsa.
  * **Formulir Pengumpulan:**
    - Input berkas mandiri (`.pdf, .doc, .docx`, batas 5 MB).
    - Kolom catatan pengumpulan (*Notes/Komentar*) opsional hingga 1.000 karakter.
    - Tombol aksi: `"Kumpulkan Tugas"` untuk pengumpulan perdana, atau `"Perbarui Berkas (Kumpul Ulang)"` jika mahasiswa merevisi tugas sebelum dinilai.
  * **Tampilan Berkas Tersimpan:** Jika sudah mengumpulkan, mahasiswa dapat melihat nama berkas yang telah dikirim, waktu pengumpulan persis, dan tautan tombol `"Unduh Berkas Saya"` untuk memeriksa ulang dokumen yang telah diunggah.
  * **Penolakan Tugas Kedaluwarsa:** Jika batas waktu telah terlewati dan tugas disetel tidak menerima keterlambatan (`allow_late == false`), formulir otomatis dinonaktifkan dan digantikan dengan peringatan merah informatif.

---

### 3. Tabel Roster Pengumpulan & Penilaian Tugas untuk Dosen/Admin

Pada halaman tugas yang sama (`assignments/show.blade.php`), dosen pengampu dan admin disajikan antarmuka pemantauan kelas (*Class Assignment Roster*):

* **Statistik Cepat Pengumpulan:**
  - Kartu metrik: Jumlah mahasiswa terdaftar, jumlah mahasiswa yang sudah mengumpulkan tugas, dan jumlah tugas yang belum dinilai.
* **Tabel Roster Mahasiswa Terdaftar:**
  - Menampilkan seluruh mahasiswa yang terdaftar di kelas bersangkutan (`$enrolledStudents`).
  - **Kolom Status:** Menampilkan indikator apakah mahasiswa tertentu sudah mengumpulkan tugas atau belum.
  - **Kolom Waktu Pengumpulan & Keterlambatan:** Menampilkan tanggal dan jam submit, dilengkapi label merah `"Terlambat"` jika diserahkan setelah `due_at`.
  - **Kolom Berkas Jawaban:** Menyediakan tombol `"Unduh Jawaban"` yang langsung mengunduh dokumen mahasiswa melalui rute privat.
  - **Kolom Nilai & Evaluasi:** Menampilkan skor nilai (skala 0–100) jika sudah dinilai, atau tombol pintasan aksi `"Beri Nilai"` yang mengarahkan dosen ke antarmuka formulir penilaian di `submissions.show`.

---

### 4. Verifikasi Pengujian Otomatis (`tests/Feature/WeekNineFileTest.php`)

Untuk memastikan seluruh fungsionalitas berkas dan otorisasi berjalan tanpa cacat, dibuat berkas pengujian fitur terdedikasi `tests/Feature/WeekNineFileTest.php`:

```text
PASS  Tests\Feature\WeekNineFileTest
✓ lecturer can upload material to private disk
✓ enrolled student can download material
✓ non enrolled student forbidden to download material
✓ enrolled student can submit assignment file
✓ resubmission replaces old file and deletes old physical file
✓ late submission rejected when allow late is false
✓ student forbidden to download other student submission

Tests:    7 passed (23 assertions)
Duration: 1.66s
```

Seluruh rangkaian pengujian sistem dari Minggu 1 hingga Minggu 9 dijalankan secara menyeluruh via `php artisan test`:

```text
PASS Tests\Unit\ExampleTest (1 test)
PASS Tests\Feature\AuditPagesTest (1 test)
PASS Tests\Feature\AuthTest (5 tests)
PASS Tests\Feature\ExampleTest (1 test)
PASS Tests\Feature\WeekFiveAccessTest (12 tests)
PASS Tests\Feature\WeekNineFileTest (7 tests)
PASS Tests\Feature\WeekSixApiTest (15 tests)

Tests:    42 passed (179 assertions)
Duration: 7.83s
```
