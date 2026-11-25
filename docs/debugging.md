# Catatan Debugging KampusLMS

Dokumen ini mencatat bug nyata yang dilaporkan dan diselesaikan selama pengembangan, penyebabnya, perbaikannya, dan test yang mencegah bug serupa kembali.

## 1. Otorisasi submission gagal karena argumen Policy

- **Gejala:** Pengumpulan tugas memicu `ArgumentCountError` saat Laravel memanggil `SubmissionPolicy::create`.
- **Penyebab:** Pemanggilan `Gate::authorize('create', [Submission::class, $assignment])` mengirim assignment sebagai argumen tambahan, tetapi signature Policy sebelumnya tidak cocok.
- **Perbaikan:** Sesuaikan signature Policy dan periksa peran serta enrollment mahasiswa sebelum mengizinkan pengumpulan.
- **Test regresi:** `SubmissionFormTest::test_unregistered_student_cannot_submit_assignment` memastikan mahasiswa yang tidak terdaftar ditolak.

## 2. Dashboard menampilkan data kelas lain

- **Gejala:** Dashboard dosen tanpa kelas menampilkan submission milik dosen lain; dashboard mahasiswa dapat menampilkan tugas berstatus draft.
- **Penyebab:** Fallback query dashboard mengambil data tanpa filter kepemilikan dosen atau status publikasi tugas.
- **Perbaikan:** Pertahankan filter kepemilikan kelas dan `status = published` pada query dashboard, termasuk saat hasil utama kosong.
- **Test regresi:** `WeekThirteenDashboardScopeTest::test_lecturer_without_courses_does_not_see_other_lecturers_submissions_on_dashboard` dan `WeekThirteenDashboardScopeTest::test_student_does_not_see_draft_assignments_on_dashboard`.

## 3. Unggahan besar berhenti sebelum validasi Laravel

- **Gejala:** Unggahan berkas besar menghasilkan `PostTooLargeException` sebelum pesan validasi aplikasi muncul.
- **Penyebab:** Batas `upload_max_filesize` atau `post_max_size` PHP pada lingkungan yang menjalankan aplikasi lebih kecil dari berkas yang dikirim.
- **Perbaikan:** Samakan batas PHP dengan batas aplikasi. Konfigurasi Docker saat ini menetapkan `upload_max_filesize = 25M` dan `post_max_size = 30M`; validasi submission aplikasi tetap membatasi berkas sampai 5 MB.
- **Test regresi:** `SubmissionFormTest::test_submission_form_rejects_oversized_file` memastikan berkas 6 MB yang mencapai Laravel ditolak dan tidak tersimpan. Test ini tidak mensimulasikan penolakan PHP sebelum request masuk ke aplikasi.

## 4. Ketergantungan data seeder pada Model Factories (Seederless DB Failure)

- **Gejala:** Pemanggilan model factories (`Course::factory()`, `Assignment::factory()`, `Submission::factory()`, `GradeFactory`) pada database pengujian bersih melempar error `QueryException: NOT NULL constraint failed: courses.lecturer_id`.
- **Penyebab:** Kolom relasi foreign key pada factory menggunakan `User::where('role', 'dosen')->inRandomOrder()->value('id')` yang mengembalikan `NULL` saat pengujian berjalan di database kosong tanpa seeder.
- **Perbaikan:** Menambahkan closure fallback otomatis pada seluruh model factories yang memanggil factory relasinya saat data induk belum ada di database.
- **Test regresi:** `WeekFourteenDatabaseTest::test_seederless_database_factories_can_generate_all_models_without_seeder` dan `WeekFourteenDatabaseTest::test_database_cascade_and_restrict_integrity_constraints`.

---

Jalankan test terkait dengan `php artisan test --filter=NamaTest`. Test menjaga perilaku aplikasi; untuk batas PHP, periksa juga konfigurasi lingkungan deployment.
