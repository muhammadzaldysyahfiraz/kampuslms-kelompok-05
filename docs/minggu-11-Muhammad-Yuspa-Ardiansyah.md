# Catatan Praktikum Minggu 11 Pemrograman Web

**Nama Mahasiswa:** Muhammad Yuspa Ardiansyah
**NIM:** 10241052

---

# READ: Optimisasi Performa dari Sisi Backend

Performa bukan hanya soal server yang cepat. Query berulang, mengambil data berlebihan, atau memakai data cache yang sudah basi juga membuat aplikasi lambat atau memberi hasil yang keliru.

## 1. N+1 dan eager loading

N+1 terjadi ketika aplikasi mengambil daftar data, lalu menjalankan query tambahan untuk setiap baris saat membaca relasinya. Contohnya, daftar 15 mata kuliah yang mengambil dosen satu per satu dapat menghasilkan sekitar 15 query tambahan.

Eager loading dengan `with()` mengambil relasi secara berkelompok:

```php
$courses = Course::with('lecturer')->paginate(15);
```

Di KampusLMS, [AppServiceProvider](../app/Providers/AppServiceProvider.php) mengaktifkan `Model::preventLazyLoading()` di luar produksi. Ini membantu menemukan relasi yang terlupa dimuat saat pengembangan dan pengujian.

## 2. `withCount()` dan pagination

Gunakan `withCount()` jika yang dibutuhkan hanya jumlah relasi. Ini meminta database menghitung jumlahnya tanpa memuat semua model terkait ke memori:

```php
$courses = Course::withCount('students')->paginate(15);
```

Pagination membatasi jumlah baris yang diproses sekaligus. Contohnya, daftar mata kuliah memakai `paginate(15)`, sedangkan daftar submission dosen memakai `paginate(20)`.

## 3. Index database

Index membantu database menemukan baris yang sering dicari atau difilter. Index perlu dipilih berdasarkan pola query; terlalu banyak index menambah ruang simpan dan dapat memperlambat operasi tulis.

Migrasi performa menambahkan index untuk status mata kuliah, pencarian submission per mahasiswa, status tugas, dan notifikasi yang belum dibaca. Index role yang duplikat kemudian dihapus oleh migrasi terpisah. Untuk memeriksa apakah index dipakai, jalankan `EXPLAIN` pada query yang relevan.

## 4. Cache dan data yang harus tetap segar

Cache cocok untuk data ringkasan yang tidak harus berubah setiap detik. Di proyek ini, jumlah global dashboard disimpan dengan key `dashboard.global_counts` selama 10 menit dan cache dibersihkan saat data terkait berubah.

Nilai, feedback, status pengumpulan, tenggat, dan notifikasi belum dibaca sebaiknya tidak ditampilkan dari cache yang mungkin basi. Untuk setiap cache, tentukan masa berlaku dan kapan cache harus dibersihkan.

## 5. Optimisasi konfigurasi

Di produksi, Laravel dapat menyimpan konfigurasi melalui `php artisan config:cache`. Setelah konfigurasi di-cache, jangan mengandalkan `env()` di controller atau view; akses konfigurasi melalui `config('...')`. Pemeriksaan proyek dapat dilakukan dengan:

```bash
rg "env\(" app resources routes
```

---

# BREAK: Skenario yang Perlu Dipahami

| Skenario | Dampak | Cara mencegah atau memeriksa |
|---|---|---|
| Mengakses relasi tanpa eager loading | Query berulang atau N+1 | Muat relasi dengan `with()`; periksa peringatan lazy loading di lingkungan non-produksi. |
| Menghitung `count($course->students)` | Semua model mahasiswa dapat dimuat hanya untuk memperoleh jumlah | Gunakan `withCount('students')` atau query agregat yang sesuai. |
| Menghapus index yang dipakai query | Database dapat melakukan pemindaian lebih luas | Jalankan `EXPLAIN` dan ukur query sebelum mengubah index. |
| Cache ringkasan tidak dibersihkan setelah data berubah | Dashboard menampilkan angka lama hingga cache kedaluwarsa | Tentukan invalidasi cache pada alur perubahan data. |
| Cache nilai atau status pengumpulan | Mahasiswa dapat melihat nilai atau status yang sudah tidak berlaku | Baca data akademik penting dari sumber terbaru dan terotorisasi. |
| Memanggil `env()` di luar `config/` | Nilai konfigurasi bisa tidak tersedia setelah `config:cache` | Gunakan `config()` dan audit kode sebelum deployment. |

---

# FIX: Penerapan pada KampusLMS

1. **Mendeteksi lazy loading.** `AppServiceProvider` mengaktifkan pencegahan lazy loading di lingkungan non-produksi.
2. **Memuat relasi yang dibutuhkan.** `CourseController::show()` memuat relasi seperti `lecturer`, `materials.course`, dan `assignments.course` sebelum mengirim data ke view.
3. **Mengurangi query pada rekap nilai.** `GradeController::indexForCourse()` mengambil tugas, mahasiswa, dan submission beserta nilai secara berkelompok, kemudian menyusun matriks di memori PHP.
4. **Menambahkan index yang sesuai.** Migrasi performa menambahkan index pada kolom yang dipakai untuk pencarian dan penyaringan. Migrasi berikutnya menghapus index role yang ternyata duplikat.
5. **Membatasi daftar.** Daftar mata kuliah, materi, tugas, notifikasi, dan submission menggunakan pagination.
6. **Menggunakan cache secara selektif.** Statistik global dashboard memakai TTL 10 menit dan cache dibersihkan ketika data terkait berubah. Nilai dan status submission tidak boleh bergantung pada cache yang basi.

Catatan pengukuran tim dan rincian optimisasi tersedia di [docs/performa.md](./performa.md). Angka performa perlu dibaca bersama skenario dan dataset pengukurannya; jangan menganggap hasil lokal sebagai jaminan yang sama di produksi.

---

# BUILD: Verifikasi dan Hasil

- [x] `Model::preventLazyLoading()` aktif di lingkungan non-produksi.
- [x] Relasi yang dibutuhkan pada detail mata kuliah dimuat melalui eager loading.
- [x] Rekap nilai kelas menggunakan query berkelompok dan pemetaan data di memori.
- [x] Migrasi index performa dan penghapusan index duplikat tersedia.
- [x] Statistik global dashboard memakai cache dengan masa berlaku dan invalidasi.
- [x] Kode aplikasi, view, dan route diaudit agar tidak memanggil `env()` langsung.
- [x] Daftar data besar menggunakan pagination.
- [ ] Ukur ulang query, waktu, dan memori pada lingkungan serta dataset yang dipakai untuk demo.
- [ ] Jalankan `EXPLAIN` pada query target di database yang akan didemonstrasikan.

Perintah pemeriksaan yang dapat diulang:

```bash
php artisan test
rg "env\\(" app resources routes
```

---

# Checkpoint 11.4: Pertanggungjawaban Backend Developer

### 1. Apa itu N+1?

N+1 terjadi saat satu query mengambil daftar data, lalu setiap baris memicu query tambahan untuk relasinya. `with()` mengambil relasi secara berkelompok dan mengurangi query berulang.

### 2. Kenapa `withCount()` lebih hemat daripada menghitung koleksi?

`withCount()` meminta database menghitung jumlah relasi dan mengembalikan angka. Menghitung koleksi dapat memuat semua model terkait ke memori, padahal yang dibutuhkan hanya jumlahnya.

### 3. Apa fungsi index?

Index membantu database mencari baris lebih cepat pada kolom yang sering dipakai. Manfaatnya dapat diperiksa dengan `EXPLAIN`, sambil mempertimbangkan biaya tambahan pada ruang simpan dan operasi tulis.

### 4. Data apa yang tidak cocok ditampilkan dari cache basi?

Nilai, feedback, status submission, tenggat, dan notifikasi belum dibaca. Data tersebut harus mencerminkan keadaan terbaru karena dapat memengaruhi keputusan atau pengalaman pengguna.

### 5. Apa yang berubah setelah `config:cache`?

Laravel memakai konfigurasi hasil cache dan aplikasi tidak seharusnya mengandalkan `env()` di luar file konfigurasi. Gunakan `config()` di controller dan view.

### 6. Apa bagian yang dibantu AI dan bagaimana memeriksanya?

AI dapat membantu merangkum pola query, menyarankan eager loading, atau menjelaskan index. Saya tetap harus mencocokkan saran dengan query dan relasi yang benar-benar dipakai, menguji perilaku halaman, serta memeriksa hasil dengan pengukuran dan `EXPLAIN`.
