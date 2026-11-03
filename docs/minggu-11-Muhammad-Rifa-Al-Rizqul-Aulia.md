# Catatan Praktikum Minggu 11 Pemrograman Web

---

**Nama Mahasiswa:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05  
**Fokus Peran:** Frontend Developer (Manajemen Halaman Tugas, Materi Perkuliahan, Pengumpulan Berkas/Submissions, Pusat Notifikasi, Asset Bundling Vite, dan Optimisasi Performa Antarmuka Pengguna)  

---

# READ: Optimisasi Performa Aplikasi Web dari Perspektif Frontend Developer

Pada Minggu ke-11 ini, perkuliahan Pemrograman Web berfokus pada **Optimisasi Performa**. Sebagai Frontend Developer yang berkolaborasi dalam tim (bersama Nova di Frontend, Farin di Database, serta Zaldy dan Yuspa di Backend), tanggung jawab utama saya adalah memastikan sisi antarmuka (*client-side runtime*) dan template Blade berjalan dengan efisiensi maksimal:

---

### 1. Prinsip Utama: Frontend Harus Efisien dan Ramah Peramban
Di sisi frontend, performa bukan hanya tentang kecepatan database, melainkan tentang apa yang dialami langsung oleh peramban pengguna (*browser experience*):
* **Beban Render DOM (*DOM Size & Bloat*):** Merender ratusan elemen tabel sekaligus dapat membekukan peramban (*browser main-thread freezing*). Oleh karena itu, pagination (15 baris per halaman) wajib dipertahankan secara konsisten di seluruh daftar penugasan dan riwayat notifikasi.
* **Pencegahan Pergeseran Tata Letak (*Cumulative Layout Shift / CLS*):** Gambar atau ilustrasi yang dimuat tanpa dimensi eksplisit atau tanpa lazy loading akan mendorong konten ke bawah saat selesai terunduh.
* **Pemisahan Peran (*Separation of Concerns*):** Template Blade adalah lapisan presentasi. Blade **tidak boleh** mengeksekusi query database langsung di dalam view (misalnya memanggil method relasi Eloquent yang memicu `SELECT` baru). Seluruh data harus disajikan bersih oleh backend atau diperiksa secara *in-memory*.

---

### 2. Kompilasi Aset Produksi: `npm run build` vs `npm run dev`
Modul 11 secara tegas mengingatkan:
> *"`npm run build`, bukan `npm run dev`, di produksi."*

* Pada lingkungan pengembangan (*development*), Vite menjalankan server lokal dengan injeksi skrip modul dan CSS tanpa minifikasi untuk memfasilitasi *Hot Module Replacement* (HMR).
* Pada lingkungan produksi, `npm run build` melakukan:
  1. **Minifikasi & Tree-shaking:** Membuang whitespace, komentar, dan pustaka JavaScript/CSS yang tidak pernah digunakan.
  2. **Content Hashing:** Menghasilkan nama file unik dengan hash (misal: `app-C6R_vE18.css`) agar peramban dapat menyimpan aset di cache (*long-term browser caching*) namun otomatis memperbarui saat kode berubah (*cache-busting*).
  3. Mengurangi ukuran transfer jaringan secara drastis (CSS menjadi ~16 kB gzip, JS ~27 kB gzip).

---

### 3. Pemuatan Gambar Cerdas: `loading="lazy"` & `decoding="async"`
Setiap elemen grafis dan ilustrasi *OpenDoodles* SVG pada halaman tugas dan materi perkuliahan diberi atribut:
* `loading="lazy"`: Memerintahkan peramban untuk menunda pengunduhan gambar hingga posisinya mendekati *viewport* layar pengguna.
* `decoding="async"`: Memproses dekode gambar di latar belakang (*off-main-thread*) sehingga peramban tidak mengalami stutter saat scrolling.

---

### 4. Kebersihan Template Blade (*Blade Template Hygiene*)
Salah satu penyebab tersembunyi yang sering memperlambat aplikasi Laravel adalah **query yang bocor di dalam Blade view**. Contoh:
* Memanggil `$assignment->course->hasStudent($user)` di dalam Blade akan mengeksekusi query database baru secara diam-diam.
* Solusi frontend: Menggunakan pemeriksaan koleksi yang sudah ada di memori via `relationLoaded()` atau membaca variabel yang sudah dipersiapkan controller, sehingga rendering tampilan murni memproses data di RAM tanpa I/O database tambahan.

---

# BREAK: Pengamatan Eksperimen Modul 11 pada Domain Frontend

Berdasarkan 7 percobaan modul 11.3, berikut hasil pengamatan saya pada domain halaman tugas, materi, dan notifikasi:

| # | Percobaan Modul | Pengamatan pada Domain Frontend Saya | Refleksi & Solusi Frontend |
|:--:|:---|:---|:---|
| **1** | Pasang `Model::preventLazyLoading()` | Mengetahui bahwa Blade view yang memanggil relasi tanpa eager load akan langsung memicu error di local. | Memastikan Blade di domain saya tidak pernah memanggil properti relasi yang belum dimuat controller. |
| **2** | Mematikan eager loading | Tampilan daftar tugas terasa lebih lambat saat data kartu tugas bertambah banyak. | Berkoordinasi dengan tim backend (Zaldy) agar endpoint penugasan selalu menyediakan data dosen dan course secara lengkap. |
| **3** | `withCount()` vs `count()` koleksi | Menampilkan badge jumlah pengumpulan pada header penugasan (`$assignment->submissions_count`). | Di Blade, selalu gunakan `$assignment->submissions_count` alih-alih `count($assignment->submissions)`. |
| **4** | Efisiensi Index Database | Memahami bahwa riwayat pengumpulan tugas mahasiswa (`submissions`) membutuhkan index `user_id` agar filter tugas belum selesai cepat direspon. | Memberikan rekomendasi index ini ke Farin (Database) agar dicantumkan di migrasi database tim. |
| **5** | Uji Seeder Skala Besar | Dengan 125 submission, tabel penugasan dosen tetap responsif karena menggunakan pagination dan scroll container terisolasi. | Mengunci tinggi tabel dan membatasi baris per halaman agar DOM tidak melebihi 1.500 node per halaman. |
| **6** | `config:cache` dan `env()` | Memastikan tidak ada satu pun tag Blade yang memanggil fungsi `env()`. | Seluruh link atau nama aplikasi di Blade diakses melalui `config('app.name')`. |
| **7** | Caching Data Basi | Mengamati bahwa status keterlambatan pengumpulan (`is_late`) dan form feedback nilai tidak boleh di-cache. | Desain UI penugasan dan formulir penyerahan berkas selalu menampilkan status terkini langsung dari server. |

---

# FIX: Pembenahan Template Tampilan Tugas & Notifikasi

1. **Eliminasi Query Bocor di `assignments/show.blade.php`:**
   * Di dalam blok otorisasi pengumpulan tugas mahasiswa, pemeriksaan kepesertaan sebelumnya memanggil `$assignment->course->hasStudent($user)`.
   * Diperbaiki menjadi pemanfaatan koleksi memori yang aman:
     ```blade
     @php
         $user = auth()->user();
         $isEnrolled = $isEnrolled ?? ($assignment->course->relationLoaded('students') 
             ? $assignment->course->students->contains('id', $user->id) 
             : $assignment->course->hasStudent($user));
         $submission = $mySubmission ?? ($assignment->relationLoaded('submissions') 
             ? $assignment->submissions->firstWhere('user_id', $user->id) 
             : $assignment->submissions()->where('user_id', $user->id)->first());
     @endphp
     ```
2. **Optimalisasi Desain Tabel Pengumpulan Dosen:**
   * Tabel daftar pengumpulan mahasiswa dibungkus menggunakan container responsif `overflow-x-auto` dengan `focus:ring-2` untuk aksesibilitas keyboard dan navigasi responsif.

---

# BUILD: Hasil Kompilasi Produksi & Kesiapan Frontend

1. **Build Aset Vite (`npm run build`):**
   * CSS: `public/build/assets/app-C6R_vE18.css` (**83.83 kB** / gzip: **16.73 kB**)
   * JavaScript: `public/build/assets/app-D85z6fHB.js` (**73.97 kB** / gzip: **27.74 kB**)
   * Manifest: `public/build/manifest.json` (0.27 kB)
   * Waktu build: **4.15 detik**. Seluruh modul teroptimasi tanpa pesan error maupun peringatan bundle berlebih.
2. **Penerapan Lazy Loading & Async Decoding:**
   * Seluruh ilustrasi pendukung (*OpenDoodles*) pada halaman tugas dan materi telah diverifikasi memiliki atribut `loading="lazy"` dan `decoding="async"`.
3. **Penyelarasan Kolaborasi dengan Tim Backend & Database:**
   * Menyerahkan catatan teknis migrasi index database kepada Farin (Database) dan pola eager loading kepada Zaldy & Yuspa (Backend) agar arsitektur menyeluruh KampusLMS selaras.

---

# Checkpoint 11.4: Pertanggungjawaban Frontend Developer

1. **Dari sudut pandang frontend, apa yang menyebabkan halaman terasa berat sebelum dioptimasi?**  
   Penyebab utama di sisi antarmuka adalah *DOM bloat* (merender seluruh baris data tanpa pagination), query database yang tidak sengaja terpanggil dari dalam loop Blade view, serta pemuatan aset gambar berukuran besar secara sinkron (*render-blocking*).
2. **Mengapa `npm run build` wajib dijalankan untuk rilis produksi?**  
   Karena `npm run dev` menyajikan aset mentah yang tidak di-minify dan membutuhkan koneksi konstan ke server Vite lokal. `npm run build` mengompres CSS dan JS, melakukan *tree-shaking*, dan menghasilkan hash unik untuk efisiensi *browser caching*.
3. **Bagaimana cara frontend membantu mencegah pemborosan memori server?**  
   Dengan menggunakan properti agregat yang sudah disediakan backend (seperti `$assignment->submissions_count`) daripada memanggil fungsi hitung pada relasi koleksi (`count($assignment->submissions)`).
4. **Apa peran atribut `loading="lazy"` pada gambar di aplikasi kita?**  
   Mencegah peramban mengunduh berkas ilustrasi yang berada di luar layar pengguna (*below-the-fold*), sehingga waktu muat awal (*First Contentful Paint*) menjadi lebih cepat dan menghemat kuota jaringan pengguna.
5. **Data apa di halaman penugasan yang tidak boleh di-cache?**  
   Status pengumpulan tugas mahasiswa, tenggat waktu penyerahan berkas, serta nilai dan umpan balik dosen. Menampilkan data basi pada elemen ini dapat berakibat fatal bagi status akademik mahasiswa.
6. **Kenapa helper `env()` tidak boleh dipakai di view Blade?**  
   Karena ketika perintah optimisasi produksi `php artisan config:cache` dijalankan minggu depan (Minggu 12), file `.env` tidak akan dimuat lagi oleh Laravel dan fungsi `env()` akan mengembalikan `null`. Seluruh nilai konfigurasi di Blade wajib dipanggil melalui `config('...')`.
