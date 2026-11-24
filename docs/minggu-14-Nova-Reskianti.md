# Catatan Praktikum Minggu 14 Pemrograman Web: Debugging dan Testing

---

**Nama Mahasiswa:** Nova Reskianti  
**NIM:** 10241058  
**Kelompok:** 05  
**Fokus Peran:** Frontend Developer 1 (Pengujian User Journey Mahasiswa, Validasi Form Input & Penanganan Berkas, Deteksi Test Menipu, serta Dokumentasi Regresi Bug)

---

## READ: Menganalisis Pola Kegagalan, Log Sistem, dan Jejak Stack Trace



### 1. Pelacakan Stack Trace yang Efektif
Saat menjalankan perintah pengujian otomatis `php artisan test`, output kesalahan sering kali memuat puluhan baris *stack trace*.
* **Prinsip Utama:** Baris pertama yang harus dibaca adalah baris teratas yang menunjuk ke direktori `app/` (kode aplikasi kita), bukan yang menunjuk ke direktori `vendor/` (kode internal framework Laravel).
* **Contoh Temuan:** Saat validasi file upload gagal memicu exception, stack trace teratas menunjuk ke `Illuminate\Validation\ValidationException`, namun baris kesalahan logika yang perlu diperbaiki berada pada `app/Http/Controllers/SubmissionController.php:74`.

### 2. Membaca `storage/logs/laravel.log`
Pemeriksaan log berkala menggunakan `tail -n 50 storage/logs/laravel.log` sangat penting karena:
* Menghindari penggunaan `dd()` atau `dump()` yang dapat mengacaukan respons HTTP (mengembalikan status 500/200 dengan format HTML yang merusak parsing JSON/test).
* Membantu menangkap kegagalan tak kasat mata (*silent failures*), seperti kegagalan otorisasi atau file upload yang ditolak sebelum masuk ke controller.

### 3. Profiling Query dengan `DB::listen()`
Memasang listener query basis data pada `app/Providers/AppServiceProvider.php`:
```php
DB::listen(function ($query) {
    Log::debug('SQL Query: ' . $query->sql, ['bindings' => $query->bindings, 'time_ms' => $query->time]);
});
```
Saat membuka halaman detail mata kuliah dan riwayat tugas, saya mencatat bahwa seluruh relasi `materials` dan `assignments` telah dimuat menggunakan *eager loading* (`with()`), sehingga tidak terjadi *query looping* berulang (N+1 query problem).

---

## BREAK: Hasil Pengamatan 7 Percobaan Kegagalan Testing (Modul 14.3)

Untuk memahami bagaimana pengujian otomatis dapat gagal, menipu, atau saling merusak, saya bersama tim melakukan 7 eksperimen kegagalan:

| # | Percobaan (*Fault Injection*) | Apa yang Dilakukan | Yang Diamati & Terjadi | Refleksi & Pembelajaran |
|:--:|:---|:---|:---|:---|
| **1** | **Test Menipu (Hanya `assertStatus(200)`)** | Membuat test yang hanya mengecek `get('/courses')->assertStatus(200)`, lalu mengosongkan seluruh method di controller. | **Test tetap berwarna HIJAU (PASS).** Padahal fitur telah rusak total. | **Pelajaran Krusial:** Pengecekan status HTTP saja adalah tes palsu. Test harus memverifikasi isi database (`assertDatabaseHas`), keberadaan data di sesi, atau struktur tampilan. |
| **2** | **Menghapus `Gate::authorize()`** | Menghapus pemanggilan otorisasi pada method unduhan submission di `SubmissionController.php`. | Jika tidak ada test yang menguji penolakan (403), seluruh test tetap hijau. Baru setelah test `test_student_forbidden_to_download_other_submission` dijalankan, test menjadi **MERAH**. | Test jalur ditolak (*forbidden*) jauh lebih bernilai untuk keamanan daripada sekadar test jalur sukses. |
| **3** | **Menghapus `RefreshDatabase`** | Menghapus trait `RefreshDatabase` dari berkas test, lalu menjalankan test suite 2 kali berturut-turut. | Pengujian kedua mengalami kegagalan (*Integrity constraint violation: duplicate entry*). Data dari test pertama mengotori test kedua. | Database harus selalu di-reset ke kondisi bersih di setiap skenario uji. |
| **4** | **Ketergantungan pada Seeder** | Membuat test yang mencari user ID `5` hasil seeder tanpa membuat data sendiri via factory. | Lolos di lokal jika seeder sudah jalan, namun **GAGAL TOTAL di pipeline CI** karena database CI dimulai dari kosong tanpa seeder otomatis. | Test wajib mandiri (*self-contained*) menggunakan Model Factory, bukan data statis. |
| **5** | **Menghapus `Storage::fake()`** | Menguji upload berkas tanpa mengaktifkan storage mock. | Berkas dummy sungguhan menumpuk di folder `storage/app/private/submissions/` dan tidak terhapus otomatis. | `Storage::fake()` mutlak digunakan agar pengujian tidak mengotori penyimpanan lokal dan tidak bergantung pada disk nyata. |
| **6** | **Merusak Migrasi `down()`** | Mengubah nama tabel yang di-drop pada method `down()` di salah satu migrasi. | `RefreshDatabase` gagal melakukan rollback database dan melempar error migration exception di awal eksekusi test. | Migrasi harus bersifat simetris dan reversibel agar siklus *migrate & rollback* saat testing berjalan mulus. |
| **7** | **Meninggalkan `dd()` di Controller** | Meletakkan `dd($request->all())` di dalam method `SubmissionController@store`. | Test `SubmissionFormTest` langsung gagal dengan status HTTP 500 / teks HTML mentah, dan memutus alur test runner. | Mengonfirmasi aturan Minggu 13: `dd()` tidak boleh masuk commit/PR; gunakan `Log::info()` jika ingin mencatat data. |

---

## FIX: Memperbaiki 8 Test Menipu pada Repo Cacat (`w14`)

Pada sesi perbaikan cabang `w14` di repositori `kampuslms-broken`, tim menemukan dan memperbaiki 8 pola pengujian yang cacat:

1. **Test 1 & 2 (Uji Status Tanpa Assertion Isi):** Test awal hanya memeriksa `$response->assertOk()`. Diperbaiki dengan menambahkan `$response->assertSeeText($assignment->title)` dan pengecekan record di database.
2. **Test 3 (Memeriksa "Bukan 200" Alih-Alih 403):** Kode awal menggunakan `$response->assertStatus(302)` atau `$this->assertNotEquals(200, ...)`. Diperbaiki menjadi pengecekan otorisasi spesifik: `$response->assertForbidden()` (HTTP 403).
3. **Test 4 (Ketergantungan pada Urutan Eksekusi):** Test mengharapkan data yang dibuat pada method test sebelumnya. Diperbaiki dengan mengisolasi pembentukan data di dalam method masing-masing.
4. **Test 5 (Ketergantungan Seeder):** Mengakses `User::find(1)` hasil seeding. Diperbaiki dengan pembentukan entitas mandiri: `User::factory()->create(...)`.
5. **Test 6 & 7 (Assertion Kosong / Selalu Benar):** Terdapat method yang tidak memiliki baris *assertion* bermakna atau hanya mengecek `$this->assertTrue(true)`. Diperbaiki dengan assertion fungsional state aplikasi.
6. **Test 8 (Upload File Fisik Tanpa Mock):** Mengunggah file ke sistem penyimpanan server asli. Diperbaiki menggunakan `Storage::fake('local')` dan `Storage::disk('local')->assertExists(...)`.

---

## BUILD: Implementasi Feature Test 

Sebagai Frontend Developer 1, saya mengimplementasikan berkas pengujian otomatis yang memuat skenario pengujian komprehensif pada interaksi form mahasiswa:

### 1. Rincian Skenario Test yang Ditulis:
* **`test_student_can_submit_assignment_form_with_valid_file` (Jalur Sukses):**  
  Memverifikasi bahwa mahasiswa terdaftar yang mengunggah berkas PDF valid (`tugas.pdf`, 500 KB) beserta catatan tugas berhasil di-redirect kembali dengan status sukses, berkas tercatat di disk privat palsu (`Storage::disk('local')->assertExists`), dan record tersimpan di tabel `submissions`.
* **`test_submission_form_rejects_non_pdf_files` (Jalur Gagal - Validasi Ekstensi):**  
  Memverifikasi bahwa pengunggahan berkas executable/skrip (`script.php`, `virus.exe`) ditolak oleh validasi server-side, sesi memuat error `assertSessionHasErrors(['file'])`, dan tidak ada data yang masuk ke database (`assertDatabaseCount('submissions', 0)`).
* **`test_submission_form_rejects_oversized_file` (Jalur Gagal - Validasi Ukuran):**  
  Memverifikasi bahwa pengunggahan berkas melebihi batas (misal simulasi 10 MB) langsung digagalkan oleh validasi form sebelum menghabiskan resource server.
* **`test_submission_form_rejects_late_submission` (Jalur Gagal - Bisnis & Tenggat Waktu):**  
  Memverifikasi bahwa jika tugas memiliki tenggat waktu masa lalu (`due_at < now()`) dan flag `allow_late` disetel `false`, pengumpulan tugas ditolak dengan respon 403 Forbidden atau session error.
* **`test_unregistered_student_cannot_submit_assignment` (Jalur Gagal - Otorisasi Kelas):**  
  Memverifikasi bahwa mahasiswa yang tidak terdaftar (*not enrolled*) pada mata kuliah terkait tidak dapat mengirim tugas (ditolak HTTP 403 Forbidden).

### 2.Kode Pengujian (`SubmissionFormTest.php`):
```php
public function test_student_can_submit_assignment_form_with_valid_file(): void
{
    $file = UploadedFile::fake()->create('laporan_proweb.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->student)
        ->post(route('assignments.submissions.store', $this->assignment), [
            'file' => $file,
            'note' => 'Tugas praktikum minggu 14 selesai tepat waktu.',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('submissions', [
        'assignment_id' => $this->assignment->id,
        'user_id' => $this->student->id,
    ]);
}
```

### 3. Kontribusi pada Dokumen `docs/debugging.md` (Kasus Bug 1 - Ranah Frontend)
Saya mencatatkan kasus bug nyata yang pernah kami hadapi pada semester ini:
* **Gejala:** Pengguna melaporkan form pengumpulan tugas gagal terkirim (respons HTTP 500), namun tidak ada pesan validasi merah yang muncul di layar form mahasiswa.
* **Langkah Diagnosis Sistematis:**  
  1. *Reproduksi:* Mencoba mengunggah berkas PDF berukuran 8 MB.
  2. *Persempit:* Memeriksa file `storage/logs/laravel.log`, ditemukan pesan `PostTooLargeException`.
  3. *Akar Masalah:* Limit ukuran upload pada antarmuka Blade (`accept="application/pdf"`) tidak membatasi ukuran berkas, sementara konfigurasi PHP `upload_max_filesize` di server menolak payload request sebelum sempat masuk ke handler `FormRequest`.
* **Solusi & Pencegahan Regresi:** Menambahkan validasi ukuran berkas di form input menggunakan JavaScript + atribut form, serta menulis test `test_submission_form_rejects_oversized_file` untuk memastikan controller selalu mengembalikan error sesi validasi yang ramah pengguna.

---

# Checkpoint 14.4: Evaluasi Pembelajaran Minggu 14

### 1. Peragakan: Hapus satu fitur, tunjukkan test yang menjadi merah karenanya.
> **Jawaban & Bukti Demonstrasi:**  
> Saya menghapus baris validasi `'file' => 'required|mimes:pdf|max:5120'` pada method `store()` di `SubmissionController.php`.  
> Saat `php artisan test --filter=SubmissionFormTest` dijalankan, pengujian `test_submission_form_rejects_non_pdf_files` langsung **GAGAL (MERAH)** dengan pesan:
> ```text
> FAILED Tests\Feature\SubmissionFormTest > submission form rejects non pdf files
> Session is missing expected key [errors].
> Failed asserting that false is true.
> ```
> Ini membuktikan secara empiris bahwa test yang saya tulis benar-benar menguji fungsionalitas validasi keamanan berkas, bukan sekadar tes formalitas.

### 2. Kenapa test yang hanya `assertStatus(200)` sering tidak bermakna?
> **Jawaban:**  
> Karena status HTTP 200 hanya membuktikan bahwa server berhasil mengembalikan respons tanpa mengalami crash (unhandled exception). Status 200 **sama sekali tidak menjamin bahwa isi halamannya benar**. Jika isi controller dihapus dan hanya me-return `view('blank')` kosong, test `assertStatus(200)` akan tetap hijau. Test yang bermakna wajib memeriksa data konkret: apakah record bertambah di basis data (`assertDatabaseHas`), apakah pesan error muncul di sesi (`assertSessionHasErrors`), atau apakah data pengguna tampil di teks halaman (`assertSeeText`).

### 3. Kenapa test jalur ditolak (*forbidden/rejected*) lebih penting daripada test jalur sukses di aplikasi ini?
> **Jawaban:**  
> Pada aplikasi akademik berbasis peran (*Role-Based Access Control*) seperti KampusLMS, kegagalan jalur sukses biasanya langsung disadari oleh pengguna (misal: tombol tidak bisa diklik). Namun, **kegagalan pada jalur ditolak bersifat tersembunyi dan membahayakan keamanan data** (contoh: celah IDOR di mana mahasiswa bisa mengunduh tugas atau melihat nilai mahasiswa lain). Pengujian jalur ditolak memastikan bahwa pengguna yang tidak berhak secara konsisten dihentikan dengan status HTTP 403 Forbidden.

### 4. Apa fungsi `RefreshDatabase`? Kenapa migrasi harus *reversible* agar ia bekerja?
> **Jawaban:**  
> Trait `RefreshDatabase` berfungsi mereset dan membersihkan struktur database setiap kali satu method test selesai dijalankan, sehingga setiap test berjalan di lingkungan yang benar-benar terisolasi dan mandiri.  
> `RefreshDatabase` bekerja dengan menjalankan migrasi awal, memanfaatkan transaksi database, atau melakukan rollback migrasi (`php artisan migrate:rollback`). Jika migrasi tidak *reversible* (misalnya method `down()` kosong atau gagal menghapus foreign key/tabel), proses rollback akan error dan menghentikan seluruh suite pengujian otomatis.

### 5. Ceritakan satu bug dari `docs/debugging.md`: Bagaimana Anda mempersempit penyebabnya?
> **Jawaban:**  
> Pada penanganan upload berkas tugas mahasiswa, form sempat gagal mengirimkan file tanpa menampilkan pesan error yang jelas. Saya mempersempit masalah dengan metode eliminasi:
> 1. Memeriksa tab *Network* di inspect element browser untuk melihat payload request (memastikan header `multipart/form-data` terpasang).
> 2. Menelusuri baris kesalahan pertama di `storage/logs/laravel.log`, bukan membaca seluruh stack trace.
> 3. Menemukan bahwa file upload ditolak oleh konfigurasi server sebelum controller dieksekusi.
> 4. Memperbaiki penanganan validasi dan mengunci solusinya dengan membuat feature test `SubmissionFormTest`.

### 6. Kenapa `dd()` tidak boleh masuk PR, sementara `Log::info()` boleh?
> **Jawaban:**  
> * `dd()` (*Dump and Die*) menghentikan eksekusi kode PHP secara paksa dan langsung mencetak variabel ke antarmuka pengguna dalam bentuk teks HTML mentah. Jika `dd()` lolos ke produksi, fitur aplikasi akan terhenti (*white screen* / 500 error) dan berisiko membocorkan data sensitif (koneksi DB, password hash, variabel environment) ke layar pengguna.
> * Sebaliknya, `Log::info()` menulis data ke berkas log privat server di latar belakang (`storage/logs/laravel.log`) tanpa mengganggu respons HTTP yang diterima pengguna dan tanpa memutus alur program.
