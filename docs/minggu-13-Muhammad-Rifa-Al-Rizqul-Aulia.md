# Catatan Praktikum Minggu 13 Pemrograman Web: Kolaborasi, Version Control, dan Peer Review

---

**Nama Mahasiswa:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05  
**Fokus Peran:** Frontend Developer (Audit Riwayat Git, Perbaikan Dashboard Scope & Keamanan Tampilan, Standardisasi Dokumentasi, dan Persiapan Peer Review)

---

## Catatan Kolaborasi & Pembagian Peran Minggu 13

Pada Minggu ke-13, fokus praktikum adalah merapikan alur kerja Git tim, menguji kegagalan alur kolaborasi (*BREAK*), memastikan standar Pull Request dan Branch Protection, serta melaksanakan Peer Review lintas kelompok di kelas yang sama:

1. **Nova Reskianti (`@NovaRes06`) — PR #69:** Memasang `.github/pull_request_template.md`, menulis laporan Minggu 13 (`docs/minggu-13-Nova-Reskianti.md`), dan menyusun draf review untuk PR #50 Kelompok 06.
2. **Muhammad Yuspa Ardiansyah (`@ardiansyahyus24`) — PR #70:** Menyempurnakan `.github/pull_request_template.md`, memperbarui bagian branching & tautan dokumentasi di `README.md`, menjalankan eksperimen CI BREAK 1–2, dan menulis laporan Minggu 13 (`docs/minggu-13-Muhammad-Yuspa-Ardiansyah.md`).
3. **Muhammad Rifa Al Rizqul Aulia (`@rifarizqul-itk`) — Cabang `feat/week-13-rifa`:** Mengaudit riwayat commit pribadi di `main`, menjalankan simulasi lokal BREAK 3 & 6, memperbaiki kebocoran scope data pada rute `/dashboard` (`routes/web.php`) beserta feature test-nya (`tests/Feature/WeekThirteenDashboardScopeTest.php`), memperbarui tautan GitHub & daftar laporan pada `README.md`, serta menyiapkan target peer review ke Kelompok 06.
4. **Muhammad Zaldy Syah Firaz (`@muhammadzaldysyahfiraz`) & Muhammad Farin Murtadho Syafiq (`@muhammadfarin18`):** Penyeragaman konfigurasi `git config` lokal serta penyusunan laporan Minggu 13 masing-masing.

---

## 1. READ — Audit Riwayat Git Kelompok 05

Pemeriksaan dilakukan pada branch `main` (per commit `c68792e`, setelah `git pull origin main`) menggunakan:

```bash
git log --oneline --graph --all
git shortlog -sne main
git shortlog -sne --no-merges main
git log main --no-merges --author="rifa" --author="Rifa" --shortstat
```

### 1.1 Berapa commit atas nama Anda? Bandingkan dengan anggota lain.

Pada branch `main`, tercatat **79 commit** atas nama identitas saya (**47 non-merge commit** dan **32 merge commit**). Pada output `git shortlog -sne main`, entri saya terpecah karena sempat menggunakan dua alamat email berbeda (`10241050@student.itk.ac.id` pada awal semester dan `182073356+rifarizqul-itk@users.noreply.github.com` sejak akhir September), serta dua penulisan nama (`rifarizqul-itk` untuk commit lokal dan `Muhammad Rifa Al Rizqul Aulia` saat melakukan *merge pull request* di GitHub):

| Anggota | Entri `git shortlog -sne main` | Total Commit (termasuk merge) | Non-Merge Commit |
|---|---|---:|---:|
| **Muhammad Rifa Al Rizqul Aulia** | `rifarizqul-itk` (`noreply` + `10241050`) & `Muhammad Rifa Al Rizqul Aulia` | **79** | **47** |
| **Muhammad Yuspa Ardiansyah** | `ardiansyah24` + `Muhammad Yuspa Ardiansyah` | **47** | **26** |
| **Muhammad Zaldy Syah Firaz** | `Muhammad Zaldy Syah Firaz` + `unknown <10241054@student.itk.ac.id>` | **36** | **20** |
| **Nova Reskianti** | `NovaRes06` + `NOVA RESKIANTI` | **26** | **14** |
| **Muhammad Farin Murtadho Syafiq** | `Muhammad Farin Murtadho Syafiq` + `BerserkerGats` + `muhammadfarin18` | **16** | **5** |
| **Bot** | `copilot-swe-agent[bot]` | **1** | **1** |

*Refleksi perbandingan:* Jumlah non-merge commit saya paling tinggi karena selain membangun komponen antarmuka Blade dan design system, saya juga mengerjakan konfigurasi awal CI (`.github/workflows/ci.yml`), integrasi autentikasi web, Dockerfile produksi, serta perbaikan *hotfix* menjelang deployment. Namun, tingginya jumlah commit juga menunjukkan perlunya pemerataan peran serta disiplin konfigurasi `user.name` dan `user.email` yang seragam di tiap laptop anggota (seperti Zaldy yang sempat tercatat sebagai `unknown` dan Farin yang memiliki tiga variasi identitas).

### 1.2 Adakah minggu di mana Anda tidak punya commit sama sekali?

Aktivitas non-merge commit saya di `main` tercatat pada 18 tanggal berbeda dari `2026-08-30` hingga `2026-11-11`:
`2026-08-30`, `2026-09-02`, `2026-09-04`, `2026-09-09`, `2026-09-15`, `2026-09-16`, `2026-09-22`, `2026-09-30`, `2026-10-07`, `2026-10-10`, `2026-10-11`, `2026-10-20`, `2026-10-21`, `2026-10-27`, `2026-10-28`, `2026-11-03`, `2026-11-10`, dan `2026-11-11`.

Terdapat satu jeda kalender sepanjang 9 hari antara `2026-10-11` hingga `2026-10-20`, yang bertepatan dengan **Minggu 8 (evaluasi UTS)** yang memang tidak menuntut penambahan fitur kode baru. Di luar pekan UTS tersebut, setiap pekan perkuliahan memiliki commit atas nama saya.

### 1.3 Berapa persen pesan commit yang mengikuti Conventional Commits?

Dari **47 non-merge commit** di `main` (mengecualikan 32 merge commit otomatis):
- **45 commit (95,74%)** sudah mengikuti pola `^(feat|fix|docs|refactor|test|perf|style|build|ci|chore|revert)(\([^)]+\))?!?:\s+\S`.
- **2 commit (4,26%)** belum mengikuti format, keduanya berasal dari minggu awal perkuliahan (`2026-09-02`):
  1. `69c7415` — `"update dikit hehe"`
  2. `3d8a5d2` — `"update file docs"`

### 1.4 Temukan satu commit Anda sendiri yang pesannya buruk. Tulis ulang seharusnya bagaimana.

* **Commit bermasalah:** `69c7415` (`2026-09-02`) — `"update dikit hehe"`
* **Kenapa buruk:** Tidak menyebutkan tipe perubahan, berkas/modul yang diubah, maupun tujuan perubahannya sehingga menyulitkan penelusuran di `git log --oneline`.
* **Seharusnya ditulis:**
  ```text
  docs(minggu-01): rapikan penulisan laporan praktikum minggu 1
  ```

### 1.5 `git log --stat` — temukan commit terbesar Anda. Apakah seharusnya dipecah?

* **Commit non-merge terbesar:** `e695b3e` (`2026-10-27`) — `"feat(ui): implementasi pusat notifikasi, lonceng realtime navbar, dan perapian kepatuhan modul"`
* **Statistik (`--shortstat`):** **62 files changed, 2.030 insertions(+), 2.324 deletions(-)** (diikuti oleh `a2adee4` dengan 34 berkas dan `5312af2` dengan 30 berkas).
* **Apakah seharusnya dipecah?** **Ya, wajib dipecah.** Commit yang menyentuh 62 berkas sekaligus menggabungkan implementasi pusat notifikasi, polling navbar, penyesuaian beberapa view Blade, dan perapian modul lain membuat *diff* terlalu besar untuk direview secara teliti. Seharusnya dipecah per tanggung jawab, misalnya:
  1. `feat(notifications): add notification center views and read actions`
  2. `feat(ui): add realtime notification bell dropdown in navbar`
  3. `refactor(views): align blade views with module compliance`

---

## 2. BREAK — Hasil Pengamatan Enam Percobaan Alur Kerja Git

Percobaan lokal dijalankan di direktori terpisah di luar working tree utama (`$env:TEMP`) agar riwayat `main` dan berkas `.env` asli proyek tidak tersentuh.

| # | Yang dicoba | Hasil Pengamatan & Bukti | Pelajaran & Mitigasi |
|:--:|---|---|---|
| **1** | Commit `.env` palsu dan `git push` | Berdasarkan inspeksi `.github/workflows/ci.yml` (`git ls-files --error-unmatch .env`) serta bukti run CI kelompok pada [workflow run 131](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126615148), pipeline CI langsung **GAGAL** pada tahap `Check .env is not committed`. *(Tidak dilakukan push `.env` palsu tambahan agar tidak mengotori riwayat run kelompok.)* | CI bertindak sebagai jaring pengaman otomatis jika pengembang tidak sengaja melakukan `git add -f .env`. |
| **2** | Hapus `.env` dengan `git rm --cached .env`, commit lagi | Pada bukti [workflow run 132](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126918135), `.env` hilang dari `HEAD` sehingga job `tests` kembali hijau, namun blob `.env` **tetap ada** di commit riwayat sebelumnya. | Menghapus `.env` dari `HEAD` tidak menghapus isi rahasia dari riwayat Git. Jika secret nyata pernah ter-push ke repo publik, seluruh kredensial wajib **segera dirotasi/diganti**. |
| **3** | Dua branch mengubah baris yang sama di `routes/web.php`, lalu `git merge` | Diuji di repo lokal sementara (`feat/simulasi-a` vs `feat/simulasi-b` pada baris `Route::get('/tentang', ...)`). Git menghentikan merge dengan pesan `CONFLICT (content): Merge conflict in web.php` dan menyisipkan marker `<<<<<<< HEAD`, `=======`, `>>>>>>> feat/simulasi-a`. | Konflik diselesaikan dengan membaca maksud kedua sisi, menulis versi gabungan yang benar, lalu memvalidasi sintaks (`php -l`) dan test. |
| **4** | Push langsung ke `main` | Dari metadata GitHub repo `muhammadzaldysyahfiraz/kampuslms-kelompok-05`, branch `main` berstatus `protected: true`, sehingga perubahan harus melalui Pull Request. *(Direct push ke `main` tidak dipaksakan demi keamanan repo kelompok.)* | Branch protection menjaga agar `main` hanya menerima kode yang telah lewat PR dan pengecekan CI. |
| **5** | PR dengan CI gagal | Belum diuji dengan PR dummy yang sengaja dibuat gagal pada repo utama agar tidak menambah PR gagal di riwayat kelompok; perilaku required status check `tests` dipantau saat PR `feat/week-13-rifa` dijalankan oleh GitHub Actions. | Tombol merge wajib menunggu status check `tests` pada `.github/workflows/ci.yml` selesai dan lulus. |
| **6** | Commit dengan `--author="Orang Lain <oranglain@example.invalid>"` | Diuji di repo lokal sementara: `git commit --author="Orang Lain <oranglain@example.invalid>"` menghasilkan commit `fc83e54` dengan `Author=Orang Lain <oranglain@example.invalid>`, sementara `Committer=rifarizqul-itk <182073356+rifarizqul-itk@users.noreply.github.com>`. | Metadata `Author` Git dapat diubah secara lokal tanpa autentikasi kriptografis, sehingga penilaian kontribusi harus diuji lewat pemahaman kode saat interview, bukan jumlah commit semata. |

---

## 3. FIX — Repo Latihan `kampuslms-broken` (`w13`)

**Status:** **Dilewati** — Pada saat pengecekan dilakukan (18 November 2026), repositori `si2514024-pemrograman-web/kampuslms-broken` tidak ditemukan / mengembalikan galat tidak tersedia pada pencarian GitHub.

---

## 4. BUILD — Perbaikan di `kampuslms` & Persiapan Peer Review

### 4.1 Perbaikan (`fix:`) dan Kelengkapan Repo Kelompok 05

Pada branch `feat/week-13-rifa`, saya menemukan dan memperbaiki masalah keamanan/cakupan data pada rute `/dashboard` (`routes/web.php`) beserta kelengkapan dokumentasi tim:

1. **`fix(dashboard): batasi scope antrean submission dosen dan tugas terbit mahasiswa` (`routes/web.php` & `tests/Feature/WeekThirteenDashboardScopeTest.php`):**
   - **Masalah sebelumnya:**
     - Pada `routes/web.php`, jika seorang `dosen` belum mengampu mata kuliah apa pun (`$lecturerCourseIds->isEmpty()`), filter `whereHas(...)` dilewati, dan ketika `$pendingSubmissions->isEmpty()`, kode jatuh ke fallback `Submission::with(...)->latest()->take(4)->get()` yang mengambil submission **milik kelas dosen lain**.
     - Jika seorang `mahasiswa` belum memiliki tugas berstatus `published`, fallback pada `$studentTasks`, `$urgentAssignment`, dan `$upcomingSchedule` menghapus syarat `where('status', 'published')`, sehingga tugas berstatus `draft` sempat terbaca di dashboard.
     - Fallback `$lecturerCourses` juga sempat mengambil `Course::take(2)` lintas dosen.
   - **Perbaikan:** Membatasi `$pendingSubmissions` dan `$lecturerCourses` hanya untuk mata kuliah milik dosen bersangkutan (mengembalikan koleksi kosong jika belum mengampu mata kuliah), menjaga filter `status = 'published'` bagi mahasiswa, dan menambahkan 2 pengujian otomatis di `tests/Feature/WeekThirteenDashboardScopeTest.php`.
   - **Hasil verifikasi:** `php artisan test --no-coverage` lulus **72 test (308 assertions)** dan `npm run build` sukses.
2. **`docs(readme): perbaiki tautan GitHub rifarizqul-itk dan lengkapi daftar laporan minggu 13` (`README.md`):**
   - Memperbaiki link akun GitHub pada baris anggota no. 1 dari `@rifarizqul` menjadi `@rifarizqul-itk` (`https://github.com/rifarizqul-itk`).
   - Melengkapi bagian *Dokumentasi Praktikum* dengan tautan laporan Minggu 13 milik Rifa, Nova, dan Yuspa.
3. **Checklist kelengkapan repo kelompok (poin 9–11):**
   - `.github/pull_request_template.md`: **Sudah terpasang** (PR #69 & PR #70).
   - Branch protection `main`: Status API GitHub `protected: true`; pengaturan rinci (wajib PR, CI `tests` hijau, minimal 1 approval) dikonfirmasi langsung oleh pemilik repo di menu *Settings → Branches*.

### 4.2 Peer Review ke Kelompok 06 (Komponen Review 10% Individu)

* **Repositori target:** [`ItsShika24/kampuslms-kelompok-06`](https://github.com/ItsShika24/kampuslms-kelompok-06)
* **Pull Request yang direview:** [PR #31 — `fix(w10): perbaikan layout navbar admin, perbaikan routing rekap nilai, dan pembersihan UI dashboard`](https://github.com/ItsShika24/kampuslms-kelompok-06/pull/31) (penulis: `@tikaacode`, branch `Tika-Mila-Wahyuni-10` → `main`)
* **Bukti komentar review di GitHub (`@rifarizqul-itk`):** [Review #5948645787 pada PR #31](https://github.com/ItsShika24/kampuslms-kelompok-06/pull/31#pullrequestreview-5948645787)
* **Catatan kondisi PR target:** Seluruh Pull Request pada repositori Kelompok 06 sudah berstatus `closed`/merged pada 18 November 2026 (termasuk PR #50 yang sudah diambil oleh Nova), sehingga dipilih PR #31 yang belum direview anggota Kelompok 05 lain dan berkaitan langsung dengan area Frontend.

**Ringkasan 3 temuan yang dikirim (Keamanan, Performa, Struktur & Keterbacaan):**

1. **Keamanan (`resources/views/components/layout.blade.php:38-53, :151` & `app/Http/Controllers/CourseController.php:69`):**  
   * **Risiko:** Ketika `!$headerUser`, blok `@php` di `layout.blade.php` tetap menjalankan query `User::where('email', ...)` berdasarkan `session('demo_role')` dan merender navigasi berbasis `$demoRole`. Pada `CourseController@show` (tujuan rute `courses.show` yang ditambahkan di `routes/web.php:157`), penentuan `$role` juga memprioritaskan `session('demo_role', $user?->role ?? 'mahasiswa')`, sehingga sesi `demo_role` yang tertinggal berisiko menampilkan tugas `draft` atau daftar mahasiswa kepada peran yang tidak semestinya.
   * **Saran:** Hapus query fallback user demo pada `layout.blade.php` untuk state tamu dan gunakan `auth()->user()?->role` sebagai sumber otoritas peran di `CourseController@show` (batasi fallback `demo_role` hanya pada `local`/`testing`).
2. **Performa (`resources/views/components/layout.blade.php:179-181` & `resources/views/grades/index.blade.php:167`):**  
   * **Risiko:** `layout.blade.php` mengeksekusi 2 query notifikasi langsung pada setiap render halaman terautentikasi, sementara `grades/index.blade.php` memanggil `$assignment->submissions->firstWhere('user_id', $student->id)` di dalam nested loop mahasiswa × tugas (pemindaian linear $O(N \times M \times K)$).
   * **Saran:** Pindahkan data notifikasi navbar ke `View::composer` (atau muat via endpoint notifikasi saat dropdown dibuka), dan siapkan lookup map submission berbasis `user_id` (`keyBy`) di controller.
3. **Struktur & Keterbacaan (`routes/web.php:156-157`, `resources/views/grades/index.blade.php:104, 165-172, 191-193, 238`, `resources/views/components/layout.blade.php:35-63`):**  
   * **Risiko:** Penambahan `Route::get('/courses/{course}', ...)->name('courses.show')` tepat di bawah `mata-kuliah.show` bertolak belakang dengan perubahan di `grades/index.blade.php` yang mengganti `courses.show` menjadi `mata-kuliah.show`, menciptakan dua URL untuk action yang sama. Selain itu, agregasi nilai dan query data masih bercampur di blok `@php` view.
   * **Saran:** Tetapkan satu route kanonik (gunakan `Route::redirect` jika hanya butuh kompatibilitas URL lama) dan pindahkan perhitungan skor/rata-rata nilai ke controller.

---

## 5. Checkpoint 13.5 — Evaluasi Pembelajaran Minggu 13

1. **Berapa commit atas nama Anda? Tunjukkan `git shortlog -sn`.**  
   Tercatat **79 commit** di branch `main` (47 non-merge commit dan 32 merge commit) di bawah entri `rifarizqul-itk` dan `Muhammad Rifa Al Rizqul Aulia`.
2. **Kenapa `git rm --cached .env` tidak cukup kalau `.env` sudah pernah ter-push?**  
   Karena `git rm --cached .env` hanya menghentikan pelacakan berkas tersebut mulai commit berikutnya (`HEAD`), sedangkan snapshot commit lama yang berisi `.env` tetap tersimpan di riwayat repositori dan bisa diakses siapa saja. Kredensial yang terlanjur ter-push wajib diganti/dirotasi.
3. **Tunjukkan satu temuan review yang Anda tulis. Jelaskan risikonya tanpa membaca catatan.**  
   Pada PR #31 Kelompok 06 (`layout.blade.php:38-53` dan `CourseController@show:69`), fallback pembacaan `session('demo_role')` dan query user demo masih aktif tanpa membatasi environment ke `local`/`testing`. Risikonya, jika sesi `demo_role` tertinggal atau terisi di produksi, halaman detail mata kuliah atau navigasi header dapat menampilkan menu admin maupun data kelas yang tidak sesuai dengan peran asli pengguna yang sedang login.
4. **Tunjukkan satu temuan yang masuk ke kelompok Anda. Anda terima atau tolak? Kenapa?**  
   **Diterima dan diperbaiki** — Pada PR #49, `@Marchelinosk` menemukan bahwa `GradeController@indexForCourse` hanya memanggil `Gate::authorize('view', $course)` dan `Gate::authorize('viewAny', Grade::class)` yang sama-sama mengizinkan mahasiswa terdaftar, serta tombol *Rekap Nilai Kelas* di `courses/show.blade.php` masih memakai `@if` role mentah dan navigasi `layout.blade.php` terduplikasi antara desktop dan mobile. Seluruhnya diterima dan diperbaiki pada branch `fix/pr-49-peer-review` (menambahkan `CoursePolicy::viewCourseGrades`, `@can('viewCourseGrades', $course)`, serta menyatukan `$navItems` berbasis Policy via `View::composer`).
5. **Kenapa PR sebaiknya kecil dan satu fitur?**  
   PR yang kecil dan berfokus pada satu perubahan mudah dibaca reviewer tanpa terlewat celah keamanan, cepat diuji CI, minim konflik merge, dan aman untuk di-`revert` jika muncul regresi di produksi.
6. **Kenapa penilaian kontribusi tidak bisa hanya berdasarkan jumlah commit?**  
   Karena jumlah commit dapat digelembungkan lewat commit kecil tidak bermakna, berbeda ritme antar peran (Frontend vs Backend vs Database), dan metadata `--author` Git bisa diatur secara lokal tanpa verifikasi server; oleh karena itu kontribusi dinilai bersama pemahaman saat wawancara.
