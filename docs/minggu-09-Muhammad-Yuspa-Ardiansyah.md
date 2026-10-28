# Catatan Pemrograman Web Minggu 9

Muhammad Yuspa Ardiansyah

10241052

# READ
## 1. Konsep inti

### 1.1 Alur unggah berkas

Alur unggah berkas yang aman tidak berhenti pada memilih file di browser. Backend perlu memvalidasi berkas, menyimpannya di lokasi yang tepat, lalu menyimpan metadata yang diperlukan ke database.

```text
Browser mengirim berkas
        ↓
Form Request memeriksa izin dan validasi
        ↓
Controller menyimpan berkas ke disk privat
        ↓
Database menyimpan path relatif dan metadata
        ↓
Endpoint unduh memeriksa izin sebelum mengirim berkas
```

Jangan percaya nama file, ukuran, MIME type, atau identitas pemilik yang dikirim klien tanpa pemeriksaan server-side.

### 1.2 Disk `local` dan `public`

Pada `config/filesystems.php`, disk `local` diarahkan ke `storage/app/private`, sedangkan disk `public` diarahkan ke `storage/app/public`.

| Disk | Lokasi | Akses |
|---|---|---|
| `local` | `storage/app/private` | Tidak langsung disajikan dari URL publik; akses seharusnya melalui aplikasi yang memeriksa izin. |
| `public` | `storage/app/public` | Dapat dijangkau melalui `public/storage` setelah `php artisan storage:link`; jangan untuk dokumen privat. |

`storage:link` membuat symbolic link dari `public/storage` ke `storage/app/public`. Link itu bukan pengaman akses. Berkas di disk publik dapat diakses tanpa melewati otorisasi Laravel.

### 1.3 Path tersimpan dan nama file

Jangan simpan file tugas menggunakan nama asli sebagai nama fisik. Dua pengguna dapat mengunggah nama yang sama dan berisiko saling menimpa. Laravel `store()` membuat nama penyimpanan unik; nama yang diberikan pengguna dapat disimpan terpisah sebagai metadata untuk ditampilkan.

```php
$path = $file->store("submissions/{$assignment->id}", 'local');
```

`$path` adalah path relatif terhadap root disk. Nama asli bukan path untuk membuka file dan harus diperlakukan sebagai data tampilan.

### 1.4 Validasi berkas

Di `app/Http/Requests/StoreSubmissionRequest.php`, validasi berkas submission yang digunakan proyek adalah:

```php
'file' => ['required', 'file', 'mimes:pdf,doc,docx,zip', 'max:10240'],
```

- `required` memastikan berkas dikirim.
- `file` memastikan input dikenali sebagai berkas.
- `mimes` memeriksa tipe berkas berdasarkan konten yang dikenali Laravel, bukan sekadar tulisan ekstensi.
- `max:10240` berarti batas 10.240 KB, sekitar 10 MB.

Validasi aplikasi harus selaras dengan batas PHP seperti `upload_max_filesize` dan `post_max_size`. Jika batas PHP lebih kecil, unggahan dapat gagal sebelum mencapai validasi Laravel.

### 1.5 Metadata dan aturan submission

`Api\SubmissionController::store()` menghitung status keterlambatan di server, bukan mempercayai nilai `is_late` dari browser. Controller juga menolak submission kedua dari mahasiswa yang sama untuk tugas yang sama dengan respons `409`.

Metadata yang disimpan meliputi path privat, nama asli, ukuran, waktu pengumpulan, dan status terlambat. Kolom `file_path` disembunyikan dari serialisasi model agar path internal tidak ikut diekspos sebagai JSON.

### 1.6 Otorisasi unduhan

File privat tetap memerlukan otorisasi aplikasi. Sebelum mengirim file, endpoint unduh harus memastikan pengguna boleh melihat submission atau materi tersebut, lalu memastikan file memang tersedia. Menyembunyikan tombol unduh di Blade saja tidak melindungi endpoint.

Pada kondisi repo yang ditinjau untuk catatan ini, endpoint khusus untuk mengunduh submission maupun file materi belum tersedia. Halaman detail submission dan proses unggah memiliki pemeriksaan akses, tetapi itu tidak sama dengan endpoint unduh.

---

## 2. READ

### Menelusuri implementasi KampusLMS

1. `config/filesystems.php` menunjukkan disk `local` memakai root privat dan disk `public` memakai root publik.
2. `StoreSubmissionRequest` membatasi unggahan ke PDF, DOC, DOCX, atau ZIP dengan batas 10 MB, serta memeriksa bahwa pengguna boleh mengumpulkan tugas yang dipublikasikan.
3. `Api\SubmissionController::store()` menyimpan file dengan `store()` ke disk `local`, menghitung keterlambatan di server, dan menolak pengumpulan ulang dengan status `409`.
4. `SubmissionResource` dapat menampilkan metadata submission, sementara `Submission::$hidden` menyembunyikan `file_path`.
5. `MaterialController` menangani metadata materi dan tautan, tetapi belum menyediakan alur unggah file materi.
6. Sebelum membuat fitur unduh, perlu ditambahkan route/controller yang memeriksa otorisasi dan keberadaan file. Jangan menganggap URL tebakan sebagai cara unduh yang sah.

Perintah untuk menelusuri route dan aturan terkait:

```bash
php artisan route:list --path=submissions
rg -n "store\\(|mimes:|max:|file_path|download" app routes
```

---

## 3. BREAK

| # | Kerusakan/skenario | Dampak yang perlu dipahami | Kondisi yang benar |
|---:|---|---|---|
| 1 | Menyimpan berkas privat ke disk `public` | Berkas dapat diakses melalui URL publik tanpa Policy. | Simpan submission privat di disk `local`. |
| 2 | Menebak URL file pada disk privat | Path bukan pengganti endpoint yang memeriksa hak akses; endpoint unduh khusus belum tersedia. | Buat endpoint privat dengan pemeriksaan izin sebelum mengirim file. |
| 3 | Menyimpan menggunakan nama asli (`storeAs`) | Nama yang sama dapat bertabrakan dan berpotensi menimpa file lain. | Gunakan nama penyimpanan unik dari `store()` dan simpan nama asli sebagai metadata. |
| 4 | Hanya memeriksa ekstensi nama file | File dengan ekstensi yang dipalsukan dapat lolos pemeriksaan berbasis nama. | Validasi file dan tipe konten dengan aturan server-side seperti `mimes`. |
| 5 | Menganggap `max:5` sebagai 5 MB | Pada validasi file Laravel, angka `max` menggunakan KB; `5` berarti sekitar 5 KB. | Samakan angka, satuan, dan pesan validasi. `10240` berarti sekitar 10 MB. |
| 6 | Mengunggah melebihi batas konfigurasi PHP | PHP dapat menolak unggahan sebelum validasi Laravel dijalankan. | Selaraskan batas PHP, reverse proxy, dan validasi aplikasi. |
| 7 | Menghapus baris submission tanpa menghapus file | File fisik dapat tertinggal sebagai file yatim. Model `Submission` saat ini belum memiliki hook penghapusan file. | Hapus file secara eksplisit pada alur penghapusan model dan data induk, dengan mempertimbangkan cascade database. |
| 8 | Mengirim submission kedua untuk tugas yang sama | Endpoint saat ini menolak pengumpulan ulang dengan status `409`; file lama tidak diganti. | Jika revisi submission diwajibkan, rancang penggantian file dan metadata secara atomik serta pertahankan constraint database. |

---

## 4. FIX

### Status perbaikan dan pekerjaan yang perlu dicatat

| Area | Kondisi KampusLMS | Tindak lanjut |
|---|---|---|
| Penyimpanan submission | Sudah menggunakan `store()` pada disk `local`. | Pertahankan penyimpanan privat. |
| Validasi submission | Sudah memakai `file`, `mimes`, dan `max:10240`; Form Request juga memeriksa hak membuat submission. | Pastikan batas PHP dan pesan error sesuai. |
| Penamaan file | Nama fisik dibuat oleh `store()`; nama asli disimpan sebagai metadata. | Jangan gunakan nama asli sebagai nama path. |
| Pengumpulan terlambat | `is_late` dihitung server dan submission terlambat ditolak jika `allow_late` tidak aktif. | Jangan menerima flag keterlambatan dari klien. |
| Pengumpulan ulang | Saat ini ditolak dengan `409`. | Jangan mengklaim `updateOrCreate`; dukungan revisi belum ada. |
| Download privat | Route unduh khusus belum tersedia. | Implementasikan otorisasi, pengecekan keberadaan file, dan respons unduh yang aman. |
| Upload file materi | Alur unggah file materi belum tersedia; materi saat ini mengelola metadata/tautan. | Implementasikan secara terpisah bila memang menjadi persyaratan. |
| Penghapusan file | Model belum menghapus file fisik saat baris dihapus. | Tangani penghapusan melalui model serta alur cascade/data induk. |

Saat fitur unduh dibuat, urutannya harus seperti ini:

```php
// Contoh alur; sesuaikan dengan Policy dan model proyek.
Gate::authorize('view', $submission);

abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

return Storage::disk('local')->download(
    $submission->file_path,
    $submission->original_name
);
```

Otorisasi harus berjalan sebelum file disajikan. Untuk alur produksi, jangan mengembalikan path internal atau detail sistem berkas kepada pengguna.

---

## 5. BUILD

- [x] Submission divalidasi di server: tipe PDF/DOC/DOCX/ZIP dan batas 10 MB.
- [x] Submission disimpan pada disk privat dengan nama fisik unik.
- [x] Path relatif disimpan sebagai metadata internal dan disembunyikan dari JSON.
- [x] Status terlambat dihitung server; kebijakan `allow_late` diterapkan.
- [x] Submission duplikat ditolak dengan status `409`.
- [ ] Endpoint unduh submission privat dengan otorisasi pemilik, dosen pengampu, atau admin sesuai Policy.
- [ ] Endpoint unduh materi privat jika unggah materi menjadi persyaratan.
- [ ] Penghapusan file fisik saat submission atau data induk dihapus.
- [ ] Tampilkan ukuran file dengan format yang mudah dibaca bila dibutuhkan antarmuka.
- [ ] Uji berkas tidak valid, file hilang, pengguna tidak berwenang, dan file dengan nama asli yang sama.

---

## 6. Checkpoint Minggu 9

### 1. Di mana file submission disimpan?

File submission disimpan di disk `local`, yang root-nya `storage/app/private`. Database menyimpan path relatifnya pada `file_path`; nama file fisik dibuat unik oleh `store()`.

### 2. Apa beda disk `local` dan `public`?

Disk `local` bersifat privat dan tidak untuk diakses langsung sebagai URL publik. Disk `public` terhubung ke `public/storage` melalui `storage:link`, sehingga isinya dapat disajikan tanpa pemeriksaan otorisasi Laravel.

### 3. Bagaimana validasi tipe dan ukuran file dilakukan?

`StoreSubmissionRequest` memakai `file`, `mimes:pdf,doc,docx,zip`, dan `max:10240`. Angka `10240` adalah KB, kira-kira 10 MB. Batas PHP juga perlu diperiksa karena unggahan bisa ditolak sebelum sampai ke Laravel.

### 4. Mengapa nama file fisik tidak menggunakan nama asli?

Nama file fisik yang unik mencegah tabrakan nama dan mengurangi risiko file tertimpa. Nama asli tetap dapat disimpan sebagai metadata untuk ditampilkan atau menjadi nama saat unduh.

### 5. Apakah mahasiswa dapat mengumpulkan ulang tugas?

Belum. Implementasi saat ini memeriksa submission untuk assignment dan mahasiswa tersebut; jika sudah ada, server menolak request dengan status `409`. Revisi submission perlu dirancang sebagai fitur tersendiri.

### 6. Apakah file dapat langsung diunduh melalui URL?

Jangan menganggap demikian. Disk privat bukan URL publik, dan pada repo yang ditinjau belum ada endpoint unduh khusus. Endpoint tersebut harus memeriksa hak akses pengguna dan keberadaan file sebelum mengirimkannya.

### 7. Apa pekerjaan penting yang belum tersedia?

Endpoint unduh privat, unggah file materi, dan penghapusan file fisik saat data dihapus belum tersedia. Catatan ini membedakan fitur yang sudah ada dari pekerjaan lanjutan agar status proyek tetap akurat.

---