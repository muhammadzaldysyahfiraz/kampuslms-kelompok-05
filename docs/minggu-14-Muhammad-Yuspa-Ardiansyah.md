# Catatan Praktikum Minggu 14 — Debugging dan Testing Backend

**Nama Mahasiswa:** Muhammad Yuspa Ardiansyah

**NIM:** 10241052

**Kelompok:** 05

**Fokus Peran:** Backend — otorisasi berbasis peran, batas akses data, feature test, dan notifikasi.

## Ringkasan pekerjaan

Fokus saya minggu ini adalah memastikan request yang tidak berhak tidak mengubah data, dan jalur penting backend memiliki test yang dapat dijalankan ulang. Saya meninjau Policy, Form Request, controller, rute, dan feature test yang sudah ada. Ketika skenario tambahan diuji, aturan backend yang ada menolak request yang tidak berhak, sehingga perubahan utama sesi ini berupa test regresi dan pemetaan dokumentasi—bukan perubahan Policy atau controller.

## READ — menelusuri alur request

Saya menjalankan `php artisan test` dan membaca hasil per berkas untuk membedakan test yang gagal pada autentikasi, validasi, dan otorisasi. Untuk akses data, pemeriksaan dilakukan di sisi server; menyembunyikan tombol di Blade saja tidak menjadi batas keamanan.

Contohnya, `GradeSubmissionRequest::authorize()` memeriksa izin sebelum API menyimpan nilai. Feature test mengirim request melalui endpoint API sebagai mahasiswa pemilik dan mahasiswa lain:

```php
foreach ([$student, $outsider] as $actor) {
    $token = $actor->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->putJson("/api/v1/submissions/{$submission->id}/grade", [
            'score' => 100,
            'feedback' => 'Percobaan penilaian oleh mahasiswa.',
        ])
        ->assertForbidden();
}

$this->assertDatabaseMissing('grades', ['submission_id' => $submission->id]);
```

Test tersebut memeriksa dua hal: API menjawab 403 dan record nilai tidak dibuat. Test lengkapnya ada di `WeekSixApiTest::test_student_cannot_grade_submission_via_api`.

## BREAK — memeriksa jalur yang harus ditolak

Alih-alih menghapus `Gate` atau merusak migrasi pada kode aplikasi, saya menguji request tidak berwenang melalui feature test. Dengan demikian, test memeriksa hasil yang diterima pengguna tanpa meninggalkan perubahan sementara pada backend.

| Skenario | Hasil yang diperiksa |
|---|---|
| Dosen lain mencoba mengubah atau menghapus tugas | 403; data tugas tetap ada |
| Dosen lain mencoba membuat, mengubah, atau menghapus materi | 403; baris materi dan berkas tetap ada |
| Materi dipanggil melalui ID mata kuliah yang salah | 404; pasangan mata kuliah dan materi yang benar tetap dapat dibuka |
| Dosen/mahasiswa mencoba mengubah atau menghapus mata kuliah | 403; admin berhasil menjalankan operasi yang diizinkan |
| Dosen/mahasiswa mencoba membuat user | 403 dan akun tidak tercatat; admin dapat membuat akun |
| Mahasiswa mencoba memberi nilai lewat API | 403 dan tidak ada record nilai |
| Pengguna mencoba membuka, menandai, atau menghapus notifikasi milik orang lain | 404; notifikasi pemilik tidak berubah |

Test-test tersebut membuat jalur penolakan dapat diperiksa ulang, tidak hanya mengandalkan respons sukses halaman.

## FIX — mengunci perilaku dengan test dan dokumentasi

Saya menambahkan test pada berkas feature test yang sudah ada, dengan data yang dibuat melalui factory dan `RefreshDatabase`. Test upload memakai `Storage::fake('local')`; test notifikasi memakai `Notification::fake()`.

Pemetaan risiko dan test dicatat di [docs/keamanan.md](keamanan.md). Saya juga menambahkan [docs/debugging.md](debugging.md), yang merangkum tiga kasus dari catatan praktikum dan riwayat proyek: ketidakcocokan argumen pada `SubmissionPolicy::create`, kebocoran scope data dashboard, dan request upload besar yang ditolak oleh batas PHP sebelum validasi Laravel. Untuk kasus upload, konfigurasi Docker saat ini menetapkan `upload_max_filesize = 25M` dan `post_max_size = 30M`; test validasi 6 MB hanya menguji request yang sudah mencapai Laravel, bukan penolakan PHP di lapisan server.

## BUILD — hasil verifikasi lokal

Test tambahan memeriksa otorisasi assignment, materi, mata kuliah, user, grading API, dan notifikasi:

- `WeekFiveAccessTest`: dosen bukan pengampu, binding materi lintas mata kuliah, CRUD mata kuliah, dan pembuatan user.
- `WeekNineFileTest`: larangan dosen lain membuat/mengubah/menghapus materi.
- `WeekSixApiTest`: mahasiswa pemilik maupun bukan pemilik submission ditolak saat mencoba memberi nilai.
- `WeekTenNotificationTest`: notifikasi nilai hanya terkirim kepada mahasiswa pemilik submission; akses notifikasi pengguna lain ditolak.

Hasil lokal terakhir:

```text
php artisan test
Tests: 88 passed (380 assertions)
```

Pemeriksaan format PHP dengan Pint dan `git diff --check` juga lulus. Workflow [`.github/workflows/ci.yml`](../.github/workflows/ci.yml) menjalankan `php artisan test`, tetapi perubahan lokal ini belum dipush, jadi hasil CI GitHub untuk perubahan tersebut belum tersedia.

## Jawaban checkpoint

1. **Bagaimana membuktikan otorisasi diuji?** Test mengirim request sebagai peran yang tidak berhak, mengharapkan 403/404, lalu memastikan data tetap ada atau tidak tercipta. Saya belum melakukan mutation test dengan menghapus `Gate` dari controller; bukti yang tersedia saat ini adalah hasil feature test pada kode yang berjalan.
2. **Mengapa `assertStatus(200)` saja lemah?** Status 200 hanya membuktikan ada respons; belum membuktikan isi atau perubahan data benar. Periksa juga konten, database, atau efek penyimpanan.
3. **Mengapa jalur ditolak penting?** Kebocoran data antarperan bisa terjadi tanpa diketahui pemilik data. Karena itu akses tidak sah harus diuji langsung.
4. **Apa fungsi `RefreshDatabase`?** Menyiapkan database pengujian yang terisolasi, biasanya dengan transaksi per test. Migrasi yang dapat dibalik juga diperlukan agar perintah refresh migration di workflow berjalan.
5. **Apa contoh bug yang ditelusuri?** Catatan diagnosis dan test regresinya ada di [docs/debugging.md](debugging.md).
6. **Mengapa `dd()` tidak untuk PR?** `dd()` menghentikan request dan menampilkan data pada respons; `Log::info()` mencatat ke log tanpa menghentikan alur. Isi log tetap harus dipilih agar tidak membocorkan data sensitif.

**Bantuan AI:** AI membantu merapikan struktur dan bahasa laporan. Nama test, rute, hasil pengujian, serta batas validasi diperiksa terhadap repository dan output perintah lokal.
