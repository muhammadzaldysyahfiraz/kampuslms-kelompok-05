# Catatan Praktikum Minggu 13 Pemrograman Web: Kolaborasi, Version Control, dan Peer Review

---

**Nama Mahasiswa:** Nova Reskianti  
**NIM:** 10241058  
**Kelompok:** 05  
**Fokus Peran:** Frontend Developer (Audit Repositori Git, Standardisasi Template Pull Request, Evaluasi Keamanan & Performa UI/UX, dan Peer Review Kode)

---

## Catatan Kolaborasi & Pembagian Peran Minggu 13

Pada Minggu ke-13 ini, fokus utama perkuliahan beralih dari implementasi fitur teknis ke **kematangan kolaborasi rekayasa perangkat lunak (*software engineering collaboration*)**:
1. **Frontend Developer:**
   - **Nova Reskianti (@NovaRes06 - Saya):** Mengaudit kebersihan alur Git repositori kelompok, menyusun dan mengintegrasikan template Pull Request resmi (`.github/pull_request_template.md`), melakukan evaluasi riwayat commit pribadi, menjalankan simulasi 6 skenario kegagalan alur Git (*BREAK*), serta melaksanakan Peer Review independen pada Pull Request kelompok lain (fokus: Keamanan otorisasi Blade/Controller, Performa N+1/DOM, dan Keterbacaan kode).
   - **Muhammad Rifa Al Rizqul Aulia (@rifarizqul-itk):** Melakukan peer review kelompok eksternal, validasi integrasi aset frontend Vite, dan peninjauan Pull Request tim internal.
2. **Backend & Database Developer:**
   - **Muhammad Zaldy Syah Firaz (@muhammadzaldysyahfiraz), Muhammad Yuspa Ardiansyah (@ardiansyahyus24), & Muhammad Farin Murtadho Syafiq (@BerserkerGats):** Mengonfigurasi aturan *Branch Protection* di GitHub, meninjau alur integrasi CI Actions, merapikan dependensi, serta menanggapi masukan review yang masuk ke repositori Kelompok 05.

---

# READ: Audit Riwayat Git Kelompok 05 dari Sudut Pandang Pengembang

Berdasarkan inspeksi riwayat Git repositori `kampuslms-kelompok-05` menggunakan perintah:
```bash
git log --oneline --graph --all
git shortlog -sn --all
git log --author="Nova" --pretty=format:"%h - %an, %ad : %s"
```

Berikut adalah analisis objektif terhadap kontribusi dan kedisiplinan alur Git:

### 1. Statistik Commit per Anggota Tim (`git shortlog -sn --all`)
Rekapitulasi kontribusi commit seluruh anggota kelompok hingga Minggu 13:
* **Muhammad Rifa Al Rizqul Aulia (`rifarizqul-itk` / `Muhammad Rifa Al Rizqul Aulia`):** $56 + 22 = 78$ commit
* **Muhammad Zaldy Syah Firaz (`Muhammad Zaldy Syah Firaz`):** 35 commit
* **Muhammad Yuspa Ardiansyah (`ardiansyah24` / `Muhammad Yuspa Ardiansyah`):** $25 + 17 = 42$ commit
* **Nova Reskianti (`NovaRes06` / `NOVA RESKIANTI`):** $18 + 12 = 30$ commit
* **Muhammad Farin Murtadho Syafiq (`Muhammad Farin Murtadho Syafiq` / `BerserkerGats` / `muhammadfarin18`):** $8 + 6 + 2 = 16$ commit

*Refleksi Perbandingan:*  
Jumlah commit saya berada pada posisi proporsional untuk ranah Frontend bersama Rifa. Rifa memiliki commit lebih banyak karena menangani setup awal Vite dan banyak penyesuaian styling mikro, sedangkan commit saya terpusat pada blok-blok fitur besar (Sistem Rekap Nilai Dosen & Transkrip Mahasiswa, optimasi layout responsif, audit Blade hygiene, dan perapian dokumen deployment).

### 2. Konsistensi Kehadiran Commit Setiap Minggu
* **Apakah ada minggu tanpa commit sama sekali?**  
  Tidak ada kekosongan panjang. Sejak Minggu 1 hingga Minggu 13, saya aktif berkontribusi setiap minggu. Namun, tercatat perbedaan ritme: pada Minggu 8 (masa evaluasi UTS), aktivitas commit berupa penggabungan branch dan review dokumen, sedangkan lonjakan commit terjadi pada Minggu 10 (implementasi fitur buku nilai) dan Minggu 11 (optimisasi performa).

### 3. Persentase Pesan Commit yang Mengikuti *Conventional Commits*
Dari total 18 commit fitur/dokumentasi atas nama `NovaRes06`:
* Commit dengan prefix standar (`feat:`, `docs:`, `refactor:`, `fix:`): **11 commit (~61%)**
* Commit tanpa prefix standar (contoh: *"catatan Minggu 9 dan Minggu 10"*, *"nambahkan punya nova"*): **7 commit (~39%)**
* *Evaluasi:* Di awal semester (Minggu 1–3), pemahaman saya mengenai Conventional Commits belum konsisten. Mulai Minggu 4 hingga Minggu 13, hampir seluruh commit telah mengadopsi format `feat(ui): ...`, `docs(deployment): ...`, `docs: ...`, dan `refactor: ...`.

### 4. Evaluasi Commit dengan Pesan Buruk
* **Commit Bermasalah:** `8309eb0` — *"nambahkan punya nova"* (2 September 2026)
* **Kelemahan:** Pesan commit ini tidak menjelaskan berkas apa yang diubah, apa tujuan perubahannya, dan komponen apa yang terpengaruh.
* **Format Seharusnya:**
  ```text
  docs(minggu-01): tambah laporan praktikum minggu 1 dan setup awal blade
  ```

### 5. Evaluasi Commit Terbesar (`git log --stat`)
* **Commit Terbesar:** `791f282` — *"feat(m3): Implementasi Sistem Tampilan Rekap Nilai Mahasiswa & Dosen"* (27 Oktober 2026)
* **Statistik Perubahan:** 6 berkas berubah, 1.185 baris penambahan (+), 32 baris pengurangan (-).
  - Mengubah Controller (`GradeController.php`), Views (`grades/course.blade.php`, `grades/mine.blade.php`, `courses/show.blade.php`, `layout.blade.php`), dan Test (`WeekTenGradebookTest.php`).
* **Apakah Seharusnya Dipecah?**  
  **YA, MUTLAK SEHARUSNYA DIPECAH.** Menggabungkan controller backend, dua view Blade kompleks, dan unit test dalam satu commit tunggal mempersulit proses peer review. Seharusnya dipecah menjadi minimal 3 commit atomik:
  1. `feat(grades): implement backend logic and routing for grades ledger and student transcript`
  2. `feat(ui): add responsive gradebook matrix and student grade views`
  3. `test(grades): add feature tests for gradebook access and authorization`

---

# BREAK: Hasil Pengamatan 6 Percobaan Alur Kerja Git (Modul 13.3)

Eksperimen berikut dijalankan untuk membedah titik kegagalan (*failure modes*) kolaborasi Git dan sistem CI/CD:

| # | Skenario Percobaan | Yang Diamati & Terjadi | Refleksi & Mitigasi |
|:--:|:---|:---|:---|
| **1** | Commit `.env` palsu dan push | Pipeline GitHub Actions (`CI KampusLMS`) langsung **GAGAL** pada langkah `Check .env is not committed` (`git ls-files --error-unmatch .env` menghasilkan exit code 1). | Mekanisme pengaman CI terbukti efektif. Rahasia kredensial terdeteksi sebelum aplikasi sempat diuji atau di-deploy. |
| **2** | Hapus `.env` dari HEAD dengan `git rm --cached .env` lalu commit ulang | Berkas `.env` memang hilang dari commit terbaru (*HEAD*), tetapi **MASIH TERCATAT LENGKAP** di commit riwayat sebelumnya. Siapa pun yang melakukan `git checkout <commit-lama>` atau membaca riwayat GitHub tetap bisa melihat kredensial tersebut. | **Pelajaran Krusial:** Sekali kredensial/secret ter-push ke GitHub publik, menghapusnya dari commit terbaru **TIDAK CUKUP**. Kredensial itu harus dianggap bocor (*compromised*) dan wajib dirotasi/dibuat ulang (`APP_KEY`, DB password, API secret). |
| **3** | Dua anggota tim mengubah baris rute yang sama di `routes/web.php` lalu merge | Terjadi **Merge Conflict** dengan marker `<<<<<<< HEAD`, `=======`, dan `>>>>>>>`. Git menghentikan proses merge dan meminta intervensi manual. | Konflik diselesaikan bukan dengan memilih salah satu pihak secara acak, melainkan membaca kedua intensi, menggabungkan rute yang sah, dan menjalankan `php artisan route:list` serta `php artisan test` untuk memastikan kebenaran. |
| **4** | Push langsung (*direct push*) ke branch `main` | Perintah `git push origin main` ditolak oleh server GitHub (`remote: error: GH006: Protected branch hook declined`). | Menegaskan bahwa seluruh perubahan kode wajib melalui Pull Request dan minimal 1 persetujuan (*review approval*). |
| **5** | Buka PR dengan test yang gagal di CI, lalu coba merge | Tombol **"Merge pull request"** di GitHub berubah abu-abu (*disabled*) dengan peringatan *"Required status check 'tests' has failed"*. | Mencegah kode cacat masuk ke branch produksi secara tidak sengaja. |
| **6** | Commit dengan `--author="Orang Lain <x@x.com>"` | Git menerima commit dan mencatat nama orang lain di log lokal (`git log`). Nama author pada commit Git dapat dipalsukan dengan mudah karena Git lokal tidak memverifikasi identitas. | **Alasan Pengujian Wawancara Dosen:** Dosen tidak bisa semata-mata menilai dari jumlah commit di `git shortlog`, melainkan melalui tanya jawab mendalam untuk memvalidasi apakah mahasiswa benar-benar memahami kode yang diklaimnya. |

---

# FIX: Standardisasi Alur Kolaborasi Repositori Kelompok 05

Sebagai wujud kontribusi perbaikan alur kerja tim, saya melakukan pembenahan berikut pada repositori:

### 1. Inisiasi Template Pull Request (`.github/pull_request_template.md`)
Sebelumnya repositori Kelompok 05 belum memiliki template PR bawaan. Saya membuat berkas `.github/pull_request_template.md` agar setiap anggota tim yang membuka PR wajib mengisi struktur standar:
```markdown
## Apa yang diubah

## Kenapa

## Cara menguji
1. 
2. 

## Daftar periksa
- [ ] Validasi ada di server-side
- [ ] Otorisasi diperiksa (`Gate::authorize()` dipanggil di controller)
- [ ] Tidak ada N+1 (diperiksa dengan Debugbar / query counter)
- [ ] Ada feature test untuk jalur sukses dan jalur ditolak
- [ ] Tidak ada `dd()`, `dump()`, atau `console.log` tertinggal
- [ ] `.env` tidak tersentuh (tidak ada kredensial/secret bocor)
- [ ] Aset frontend terkompilasi bersih (`npm run build`)
- [ ] Seluruh pengujian otomatis lolos (`php artisan test`)

## Bagian yang dibantu AI
(Sebutkan bagian mana yang dibantu AI dan apa penyesuaian yang Anda ubah dari keluaran aslinya agar sesuai dengan konteks KampusLMS)
```

### 2. Kebijakan Branch Protection Repo
Memverifikasi bersama Backend Lead (Zaldy) bahwa branch `main` terkunci dengan aturan:
* Wajib Pull Request sebelum merge (*Require a pull request before merging*).
* Wajib minimal 1 approval dari anggota tim.
* Wajib status check CI hijau (*Require status checks to pass before merging: tests*).
* Cegah *force push* dan *deletion* pada branch `main`.

---

# BUILD: Pelaksanaan Peer Review (Komponen Nilai 10% Individu)

### A. Informasi Repositori & Pull Request Target yang Direview
* **Repositori Target:** [`ItsShika24/kampuslms-kelompok-06`](https://github.com/ItsShika24/kampuslms-kelompok-06)
* **Pull Request yang Direview:** [Pull Request #50: `fix(review): eager load materials and submissions in course destroy to prevent N+1`](https://github.com/ItsShika24/kampuslms-kelompok-06/pull/50)
* **Penulis PR (Author):** Shabrina Putri Az-Zahra ([@shabrinakeif](https://github.com/shabrinakeif))
* **Cabang Integrasi:** `Shabrina-Azzahra-13` $\rightarrow$ `main`
* **Status PR saat Direview:** *Open*

---

### B. Temuan Review yang Diajukan (Format: Lokasi, Risiko, Saran)

Berikut adalah 3 temuan hasil audit langsung pada kode Kelompok 06 yang saya submitkan ke kolom diskusi PR #50:

#### 1. Kategori KEAMANAN (Celah Autentikasi & Privilege Escalation pada Helper Sync)
* **Lokasi:** `app/Http/Controllers/CourseController.php:19-29` (dan `app/Http/Controllers/SubmissionController.php:17-27`)
* **Risiko:**  
  Method `syncAuthUser()` mengeksekusi login otomatis menggunakan `auth()->login($demoUser)` setiap kali session `demo_role` ditemukan tanpa memeriksa status environment (`app()->isLocal()`). Jika aplikasi berjalan di server produksi dan terdapat sesi `demo_role` yang tertinggal atau disuntikkan oleh pengguna tamu (*guest*), siapa pun dapat langsung terautentikasi otomatis sebagai Admin atau Dosen tanpa perlu melewati form login kredensial password. Ini merupakan kerentanan eskalasi hak akses (*privilege escalation*) yang sangat berbahaya.
* **Saran Perbaikan:**  
  Batasi helper tersebut agar hanya dapat berjalan di lingkungan lokal (*local development*) atau pengujian:
  ```php
  protected function syncAuthUser(): ?User
  {
      if (app()->isLocal() && ! auth()->check() && session()->has('demo_role')) {
          $demoUser = User::where('role', session('demo_role'))->first();
          if ($demoUser) {
              auth()->login($demoUser);
          }
      }

      return auth()->user();
  }
  ```

#### 2. Kategori PERFORMA (Efisiensi Alokasi Memori saat Penghapusan Berkas Fisik)
* **Lokasi:** `app/Http/Controllers/CourseController.php:156` (perubahan kode pada PR #50)
* **Risiko:**  
  Penambahan `$course->loadMissing(['materials', 'assignments.submissions'])` berhasil mencegah N+1 query. Namun, memuat seluruh relasi `assignments.submissions` akan menghidrasi (*hydrate*) ratusan objek Model Eloquent lengkap ke dalam RAM PHP hanya untuk membaca string `file_path`. Pada mata kuliah dengan 15 tugas dan 40 mahasiswa (total 600 baris submission), konsumsi RAM server akan melonjak drastis dan berisiko memicu *Out of Memory* (OOM) saat proses penghapusan kelas berlangsung.
* **Saran Perbaikan:**  
  Cukup ambil string jalur berkas fisik secara langsung menggunakan agregasi `pluck()` tanpa perlu menghidrasi model Eloquent yang berat:
  ```php
  $materialPaths = $course->materials()->whereNotNull('file_path')->pluck('file_path');
  $submissionPaths = \App\Models\Submission::whereIn('assignment_id', $course->assignments()->pluck('id'))
      ->whereNotNull('file_path')
      ->pluck('file_path');

  foreach ($materialPaths->merge($submissionPaths) as $path) {
      if (Storage::disk('local')->exists($path)) {
          Storage::disk('local')->delete($path);
      }
  }
  ```

#### 3. Kategori STRUKTUR & KETERBACAAN (Separation of Concerns & Transaksi Database)
* **Lokasi:** `app/Http/Controllers/CourseController.php:155-171`
* **Risiko:**  
  Logika I/O penghapusan berkas fisik penyimpanan server (`Storage::disk('local')->delete(...)`) dieksekusi secara manual di dalam method controller `destroy()`. Jika proses penghapusan mata kuliah dipanggil dari tempat lain (seperti seeder, command artisan, atau antarmuka API), berkas fisik di disk privat tidak akan terhapus dan menjadi berkas sampah (*orphaned files*). Selain itu, tidak adanya pembungkus transaksi (`DB::transaction`) berisiko menyebabkan berkas fisik terhapus tetapi record database gagal terhapus jika terjadi kegagalan sistem di tengah jalan.
* **Saran Perbaikan:**  
  Pindahkan pembersihan berkas fisik ke dalam model event `deleting` pada Model `Course` atau `Material`/`Submission`, dan bungkus eksekusi penghapusan di controller menggunakan database transaction:
  ```php
  DB::transaction(function () use ($course) {
      // Hapus berkas fisik terisolasi
      $course->delete();
  });
  ```

---

### C. Tanggapan atas Review yang Masuk ke Repositori Kelompok 05
Repositori Kelompok 05 menerima masukan peer review dari kelompok lain pada dua Pull Request:

#### 1. Ulasan pada PR #55 (Domain Frontend Saya: `resources/views/courses/show.blade.php`)
* **Kategori:** *Structure & Readability (Blade Template Hygiene)*
* **Isi Masukan Reviewer:**
  > *"Perhitungan fallback jumlah mahasiswa (`$stdCount`) diletakkan langsung di dalam template Blade menggunakan blok `@php`, diikuti oleh pengecekan defensif yang berulang seperti `$course->relationLoaded('students')` pada baris 247, 329, dan 331 serta `isset($availableStudents)` pada baris 296 dan 298. Template tampilan menjadi penuh dengan logika pemeriksaan status relasi dan ketersediaan variabel. Pindahkan perhitungan jumlah mahasiswa ke accessor pada model Course atau pastikan controller selalu melakukan eager loading relasi students dan mengirimkan `$availableStudents` sebagai koleksi kosong."*
* **Keputusan Saya:** **DITERIMA DENGAN BAIK & LANGSUNG DIPERBAIKI (FIXED)**.
* **Alasan & Tindak Lanjut Nyata:**  
  Pengecekan defensif `relationLoaded()` tersebut awalnya saya pasang saat eksperimen optimisasi performa Minggu 11 untuk mencegah lazy loading jika controller tidak memuat relasi. Namun, karena controller backend (`CourseController@show`) sudah menjamin pemanggilan `$course->load(['students', ...])`, `$course->loadCount('students')`, dan inisialisasi `$availableStudents = collect()`, pengecekan defensif di Blade tersebut memang berlebihan dan mengotori kebersihan template (*code clutter*).  
  **Aksi Perbaikan Kode:** Saya langsung menghapus blok `@php $stdCount = ... @endphp`, menggantinya dengan pemanggilan bersih `$course->students_count`, menyederhanakan pengecekan menjadi `$availableStudents->isNotEmpty()`, dan menghapus seluruh pengawal `relationLoaded()` berulang. Template Blade kini kembali deklaratif dan bersih sesuai kaidah *Separation of Concerns*.

#### 2. Ulasan pada PR #61 (Domain Seeder & Entrypoint Container oleh @ferdiansyaafq)
* **Kategori:** *Keamanan (`docker/entrypoint.sh:43-56`) & Performa (`DatabaseSeeder.php`)*
* **Keputusan Tim:** **DITERIMA PENUH**.
* **Alasan & Tindak Lanjut:** Reviewer menemukan bahwa logika operator `&&` pada evaluasi `DEMO_*_PASSWORD` dapat melewatkan pembaruan kata sandi akun admin produksi jika salah satu kredensial kosong (*fail silently*). Kami menerima masukan ini untuk menambahkan validasi peringatan dan penghentian proses (`exit 1`) di lingkungan produksi, serta mengadopsi bulk insert pivot pada seeder pendaftaran mahasiswa.

---

# Checkpoint 13.5: Evaluasi Pembelajaran Minggu 13

### 1. Berapa commit atas nama Anda? Tunjukkan `git shortlog -sn`.
> **Jawaban:**  
> Berdasarkan perintah `git shortlog -sn --all`, tercatat **30 commit** atas nama saya:
> - **18 commit** dengan identitas akun `NovaRes06` (fitur Blade, optimasi layout, styling, template PR, dan dokumentasi mingguan).
> - **12 commit** dengan identitas `NOVA RESKIANTI` (penggabungan Pull Request / merge commit ke branch utama).
> 
> Potongan keluaran terminal:
> ```text
> 56  rifarizqul-itk
> 35  Muhammad Zaldy Syah Firaz
> 25  ardiansyah24
> 22  Muhammad Rifa Al Rizqul Aulia
> 18  NovaRes06
> 17  Muhammad Yuspa Ardiansyah
> 12  NOVA RESKIANTI
>  8  Muhammad Farin Murtadho Syafiq
>  6  BerserkerGats
>  2  muhammadfarin18
> ```

### 2. Kenapa `git rm --cached .env` tidak cukup kalau `.env` sudah pernah ter-push?
> **Jawaban:**  
> Karena Git adalah sistem pencatat riwayat berbasis *snapshot* yang tidak dapat diubah begitu saja (*immutable history*). Perintah `git rm --cached .env` hanya menghapus file `.env` dari *staging area* dan commit berikutnya (*HEAD*). Seluruh commit masa lalu yang pernah memuat berkas `.env` tersebut **masih tersimpan utuh di dalam riwayat repositori**.  
> Siapa pun yang mengkloning repositori atau melihat commit history di GitHub dapat membuka kembali isi `.env` lama. Oleh sebab itu, jika berkas rahasia terlanjur ter-push ke server publik, rahasia tersebut harus dianggap telah diketahui publik dan **wajib dirotasi/diganti**, bukan sekadar dihapus dari commit terakhir.

### 3. Tunjukkan satu temuan review yang Anda tulis. Jelaskan risikonya tanpa membaca catatan!
> **Jawaban:**  
> **Temuan Keamanan pada `CourseController@syncAuthUser` (Kelompok 06):**  
> Method `syncAuthUser()` melakukan autentikasi otomatis `auth()->login($demoUser)` berdasarkan session `demo_role` tanpa memastikan bahwa aplikasi berjalan di lingkungan `local` (`app()->isLocal()`).  
> **Risikonya:** Pengunjung luar / *guest* di lingkungan produksi dapat menyuntikkan sesi `demo_role=admin` dan langsung masuk ke sistem sebagai Administrator tanpa validasi email maupun kata sandi sama sekali (*authentication bypass* & *privilege escalation*).

### 4. Tunjukkan satu temuan yang masuk ke kelompok Anda. Anda terima atau tolak? Kenapa?
> **Jawaban:**  
> **Temuan Struktur & Keterbacaan pada PR #55 (`resources/views/courses/show.blade.php`):**  
> Reviewer mengkritisi adanya blok `@php` untuk variabel fallback `$stdCount` dan pengecekan defensif berulang `$course->relationLoaded('students')` serta `isset($availableStudents)` di dalam template Blade.  
> **Keputusan:** **DITERIMA**.  
> **Alasan & Tindak Lanjut:** Masukan ini sangat tepat untuk menjaga *Blade template hygiene*. Pengecekan defensif tersebut berlebihan karena controller `CourseController@show` sudah memuat eager load count `$course->students_count` dan menjamin `$availableStudents` selalu berupa *Collection*. Saya menerima kritik ini dan telah memperbaikinya langsung dengan menghapus blok `@php`, mengganti variabelnya menjadi `$course->students_count` secara deklaratif, serta menyederhanakan kondisi pengecekan menjadi `$availableStudents->isNotEmpty()`.

### 5. Kenapa PR sebaiknya kecil dan satu fitur?
> **Jawaban:**  
> 1. **Dapat Direview Secara Mendalam:** PR berukuran kecil (kurang dari 200–300 baris diff) memungkinkan reviewer membaca setiap baris dengan teliti tanpa mengalami kelelahan kognitif (*review fatigue*).
> 2. **Mudah Dilacak & Diuji:** Jika terjadi bug atau kegagalan CI, tim dapat dengan cepat menemukan penyebabnya.
> 3. **Minim Konflik Merge:** Branch kecil berumur pendek lebih jarang bertabrakan dengan branch anggota lain.
> 4. **Aman untuk di-Rollback:** Jika suatu fitur bermasalah di produksi, PR kecil dapat di-*revert* dengan satu klik tanpa membatalkan fitur-fitur lain yang sudah stabil.

### 6. Kenapa penilaian kontribusi tidak bisa hanya berdasarkan jumlah commit?
> **Jawaban:**  
> 1. **Kuantitas Bukan Kualitas:** Seseorang bisa membuat 50 commit kecil hanya untuk memperbaiki spasi atau typo kata (*commit inflation*), sedangkan anggota lain membuat 3 commit berbobot yang mengimplementasikan arsitektur keamanan atau fitur kalkulasi rumit ratusan baris.
> 2. **Author Git Mudah Dipalsukan:** Melalui argumen `git commit --author="Nama Lain <email>"`, kepenulisan commit dapat diubah tanpa validasi identitas server.
> 3. **Distribusi Peran Berbeda:** Pengembang Frontend mungkin memiliki lebih banyak commit mikro karena iterasi styling visual, sedangkan Database Engineer memiliki commit lebih sedikit tetapi menyangkut integritas skema database seluruh sistem.
> Oleh karena itu, penilaian harus diuji secara komprehensif melalui pemahaman kode saat wawancara/tanya jawab langsung oleh dosen.
