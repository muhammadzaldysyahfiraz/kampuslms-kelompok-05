# Catatan Praktikum Minggu 13 Pemrograman Web: Kolaborasi, Version Control, dan Peer Review

**Nama Mahasiswa:** Muhammad Yuspa Ardiansyah

**NIM:** 10241052

**Kelompok:** 05

**Fokus Peran:** Backend Developer (Audit Riwayat Git, Kolaborasi Repository, Standardisasi Pull Request, CI/Branch Protection, dan Peer Review)

---

## Catatan Kolaborasi dan Pembagian Peran Minggu 13

Catatan ini merangkum audit riwayat proyek, eksperimen Git pada lingkungan terpisah, standardisasi dokumentasi repository, dan persiapan peer review. Audit menggunakan riwayat Git yang tersedia, sedangkan eksperimen BREAK tidak mengubah `main` atau berkas environment asli. Peer review terhadap kelompok lain belum dikirim karena target perlu dikonfirmasi dan isi temuan harus terlebih dahulu ditelaah sendiri.

## 1. READ — Audit Riwayat Git Kelompok 05

Audit ini menggunakan riwayat commit yang tersedia pada branch lokal dan remote repository Kelompok 05. Pemeriksaan dilakukan dengan `git log --oneline --graph --all`, `git shortlog -sn --all`, log bertanggal, log dengan filter author, dan `git log --all --numstat`. Untuk membedakan riwayat proyek dari checkpoint sesi alat, statistik kontribusi di bawah dihitung dari branch lokal dan `origin/*`, bukan dari `refs/agents/*`.

### 1.1 Statistik Commit Pribadi dan Tim

Git mencatat dua nama author untuk identitas saya. Saya mengonfirmasi bahwa `ardiansyah24` adalah alias Git saya. Kedua nama memakai email yang sama; konfigurasi Git global saat audit juga memakai nama `ardiansyah24` dan email tersebut. Metadata author Git tetap dapat diatur oleh pemilik komputer dan bukan bukti kriptografis tentang siapa yang menulis setiap perubahan.

| Nama author di Git | Email | Commit pada branch proyek |
|---|---|---:|
| `ardiansyah24` | `10241052@student.itk.ac.id` | 25 |
| `Muhammad Yuspa Ardiansyah` | `10241052@student.itk.ac.id` | 17 |

Jumlah pribadi yang tercatat pada branch proyek adalah **42 commit**: 25 dengan nama author `ardiansyah24` dan 17 dengan nama `Muhammad Yuspa Ardiansyah`. Dari jumlah itu, 21 merupakan merge commit dan 21 non-merge commit. Pada `git shortlog -sn --all`, nama `ardiansyah24` muncul dengan 94 commit. Angka itu mencakup riwayat dari `refs/agents/*` dengan pesan checkpoint sesi alat, sehingga tidak dipakai sebagai jumlah kontribusi proyek. Pembatasan ke branch lokal dan branch remote menghasilkan 25 commit untuk identitas tersebut.

Secara hitungan `shortlog`, 42 lebih besar daripada satu entri author lain, yaitu 35 commit. Namun, 21 dari 42 adalah merge commit; perbandingan ini tidak mengukur besar atau mutu pekerjaan dan tidak boleh dipakai sendirian untuk menilai kontribusi.

Ringkasan `git shortlog` untuk 200 commit unik pada branch proyek:

| Nama author | Email yang tercatat | Commit |
|---|---|---:|
| Muhammad Zaldy Syah Firaz | `10241054@student.itk.ac.id` | 35 |
| rifarizqul-itk | `182073356+rifarizqul-itk@users.noreply.github.com` | 32 |
| ardiansyah24 | `10241052@student.itk.ac.id` | 25 |
| rifarizqul-itk | `10241050@student.itk.ac.id` | 24 |
| Muhammad Yuspa Ardiansyah | `10241052@student.itk.ac.id` | 17 |
| NovaRes06 | `10241058@student.itk.ac.id` | 14 |
| Muhammad Rifa Al Rizqul Aulia | `182073356+rifarizqul-itk@users.noreply.github.com` | 13 |
| NOVA RESKIANTI | `10241058@student.itk.ac.id` | 12 |
| Muhammad Rifa Al Rizqul Aulia | `10241050@student.itk.ac.id` | 10 |
| Muhammad Farin Murtadho Syafiq | `yoasobi.soca@gmail.com` | 8 |
| BerserkerGats | `farin.raihan@gmail.com` | 6 |
| muhammadfarin18 | `yoasobi.soca@gmail.com` | 2 |
| copilot-swe-agent[bot] | `198982749+Copilot@users.noreply.github.com` | 1 |
| unknown | `10241054@student.itk.ac.id` | 1 |

Daftar tersebut mempertahankan variasi identitas sesuai keluaran Git dan tidak menggabungkan nama anggota lain. Jumlah commit menunjukkan volume perubahan yang tercatat, bukan mutu, tingkat kesulitan, atau pemerataan beban kerja. Peran frontend, backend, dan database juga menghasilkan pola commit berbeda: pekerjaan UI sering berupa iterasi tampilan, backend dapat mencakup alur lintas controller dan otorisasi, sedangkan pekerjaan database dapat terkonsentrasi pada migrasi dan integritas data. Penilaian perlu dilengkapi dengan isi perubahan dan pemahaman pembuatnya.

### 1.2 Konsistensi Aktivitas Commit

Commit pada dua nama author yang telah dikonfirmasi sebagai identitas saya tercatat dari 2 September sampai 11 November 2026. Tanggal aktivitas proyek yang ditemukan adalah:

`2026-09-02`, `2026-09-03`, `2026-09-04`, `2026-09-08`, `2026-09-13`, `2026-09-15`, `2026-09-22`, `2026-09-23`, `2026-09-30`, `2026-10-10`, `2026-10-11`, `2026-10-27`, `2026-10-28`, `2026-11-10`, dan `2026-11-11`.

Ada jeda aktivitas kalender yang cukup panjang: 15–22 September (7 hari), 23–30 September (7 hari), 30 September–10 Oktober (10 hari), 11–27 Oktober (16 hari), dan 28 Oktober–10 November (13 hari). Jeda tersebut hanya menunjukkan tidak ditemukannya commit pada rentang tanggal itu. Kalender awal perkuliahan dan pembagian kerja mingguan tidak tersedia dalam riwayat ini; karena itu, tidak dapat disimpulkan bahwa ada minggu perkuliahan tertentu tanpa kontribusi.

### 1.3 Evaluasi Conventional Commits

Untuk konsistensi penghitungan, sebuah pesan dinilai mengikuti Conventional Commits jika cocok dengan pola berikut:

```regex
^(feat|fix|docs|refactor|test|perf|style|build|ci|chore|revert)(\([^)]+\))?!?:\s+\S
```

Pola menerima tipe dengan atau tanpa scope, serta penanda breaking change `!`. Merge commit dikecualikan dari pembilang dan penyebut karena pesan otomatis seperti `Merge pull request ...` tidak menjelaskan jenis perubahan. Dari 21 commit non-merge dengan identitas saya, 11 pesan cocok dengan pola tersebut, yaitu **11 / 21 = 52,38%**. Seluruh 17 commit dengan nama author `Muhammad Yuspa Ardiansyah` adalah merge commit, sehingga tidak menambah penyebut atau pembilang.

Sebagai pembanding, pada seluruh 200 commit unik branch proyek terdapat 91 merge dan 109 non-merge. Sebanyak 77 dari 109 pesan non-merge cocok dengan pola tersebut, yaitu **70,64%**. Masih ada pesan seperti `tes again`, `CRUD`, `Week 4`, dan `Update backend Minggu 7` yang tidak menyampaikan tujuan perubahan secara cukup spesifik.

### 1.4 Evaluasi Satu Commit dengan Pesan Buruk

- **SHA:** `d489ae9888e10a5fb45eae43945df5607ca46b91`
- **Tanggal:** 30 September 2026
- **Pesan asli:** `tes again`
- **Alasan:** Pesan ini hanya menyatakan bahwa sesuatu diuji ulang, tanpa menjelaskan fitur atau hasil perubahan. Commit tersebut menambahkan pemeriksaan akses untuk halaman detail submission, middleware role, registrasi alias middleware, serta halaman 403 dan route terkait.
- **Contoh pesan yang lebih informatif:** `feat(submissions): add authorization checks to submission detail page`

Pesan historis tidak diubah; contoh di atas hanya menunjukkan bagaimana maksud commit dapat dijelaskan dengan lebih baik.

### 1.5 Evaluasi Commit Terbesar

Commit non-merge dengan perubahan baris terbesar pada identitas saya adalah:

- **SHA:** `282d1c8a3846df0265214b2563c2192fa9745b6d`
- **Tanggal:** 8 September 2026
- **Pesan:** `Selesaikan BUILD Minggu 2`
- **Statistik:** 24 file, 2.986 penambahan baris dan 277 penghapusan baris. Angka baris berasal dari `--numstat`; file biner tercatat sebagai file terdampak tetapi tidak memiliki hitungan baris.

File yang terdampak:

`app/Http/Controllers/CourseController.php`; `routes/web.php`; `resources/views/components/layout.blade.php`; `resources/views/courses/index.blade.php`; `resources/views/courses/show.blade.php`; `resources/views/dashboard.blade.php`; `resources/views/errors/404.blade.php`; `resources/views/welcome.blade.php`; `package-lock.json`; `docs/minggu-02-Muhammad-Yuspa-Ardiansyah.md`; `docs/minggu-01-Nova-Reskianti.pdf`; serta 13 bukti gambar di `docs/img-yuspa/` (`02-break-1.png` sampai `02-break-8.png`, `02-break-5.1.png`, `controller.png`, `image copy.png`, `route.png`, dan `view.png`).

Commit ini layak dipecah karena mencampur perubahan controller/rute, beberapa view, lockfile, laporan praktikum, dan bukti gambar. Pemecahan atomik yang lebih mudah direview dapat berupa:

1. `feat(courses): add course controller actions and routes`
2. `feat(ui): add course, dashboard, and error views`
3. `build(npm): add dependency lockfile`
4. `docs(week-02): add practicum report and exercise evidence`
5. Commit dokumentasi terpisah untuk menghapus atau mengganti PDF yang tidak lagi dipakai.

Contoh tersebut adalah usulan pemecahan konseptual, bukan perubahan terhadap riwayat.

### 1.6 Refleksi Alur Branch Kelompok

Pada saat audit terdapat 6 branch lokal dan 31 branch remote `origin` (di luar symbolic ref `origin/HEAD`). Branch remote mencakup `main`, beberapa branch integrasi bernama `dev-rifa`, `dev-yuspa`, dan `dev-zaldy`, serta 10 branch berawalan `feat/`. Tidak ada branch remote bernama tepat `dev`.

Riwayat memperlihatkan penggunaan branch fitur dan merge PR ke `main`, tetapi konvensi integrasi tidak seragam: terdapat beberapa branch `dev-*`, branch fitur, dan branch bernama anggota atau modul. Karena itu, pola `feat/* → dev → main` belum diterapkan sebagai satu alur konsisten pada seluruh repo. Cabang `main` adalah cabang utama terbaru, sedangkan `dev-yuspa` dan branch integrasi lain tetap ada dengan cakupan berbeda. Sebelum membuka PR baru, base branch harus dipastikan dari branch remote yang benar-benar tersedia dan aturan tim, bukan diasumsikan bernama `dev`.

## 2. BREAK — Eksperimen Enam Skenario Git

Semua percobaan dilakukan pada clone atau repository sementara di luar working tree proyek. Base clone eksperimen adalah `origin/main` pada SHA `89755558cd671d7a201fa99fcfee6547ed099328`. Tidak ada migrasi yang dijalankan pada repository utama. Berkas `.env` asli tidak dibaca, disalin, atau diubah; satu-satunya `.env` yang pernah dipush berisi nilai latihan palsu sesuai instruksi.

### 2.1 Commit `.env` Latihan dan Pemeriksaan CI

- **Tujuan:** Membuktikan workflow CI menolak `.env` yang masuk tracking, lalu membedakan keadaan HEAD saat ini dari riwayat commit sebelumnya.
- **Lingkungan/branch:** Clone sementara `kampuslms-w13-break-env`, branch `feat/w13-env-guard-test-20261118`, dari base SHA `89755558cd671d7a201fa99fcfee6547ed099328`. Workflow `.github/workflows/ci.yml` menjalankan CI pada push ke `feat/*`.
- **Perintah inti:** Membuat berkas `.env` baru hanya dengan nilai dummy yang ditentukan modul; `git add -f .env`; commit; push; kemudian `git rm --cached .env`; commit kedua; verifikasi dengan `git ls-files --error-unmatch .env` dan `git show <sha-commit-pertama>:.env`.
- **Hasil dan bukti:** Commit pertama `95fc7ade78f0955ea758751d5095a2b9d017b292` memicu [workflow run 131](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126615148). CI gagal pada langkah **Check .env is not committed**, sesuai tujuan uji. Commit kedua `94296d4103dd8fb52b6aff76e00affb726821fb8` menghapus `.env` dari HEAD; verifikasi menunjukkan berkas tidak lagi dilacak, sedangkan commit pertama masih memuat berkas berisi dua baris dummy saja. Berkas lokal latihan tetap berada di clone sementara. [Workflow run 132](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126918135) selesai dengan job `tests` berstatus sukses.
- **Interpretasi dan mitigasi:** Penghapusan dari commit terbaru tidak menghapus isi dari riwayat commit lama. Jika yang terlanjur terekspos adalah secret sungguhan, tindakan utama adalah segera merotasi atau mencabut secret; menghapus berkas pada HEAD saja tidak cukup.
- **Status:** **BERHASIL** — kedua keadaan CI teramati sesuai yang diuji; kegagalan run pertama adalah hasil yang diharapkan.

### 2.2 Mempertahankan Bukti Riwayat Saat `.env` Dihapus dari HEAD

- **Tujuan:** Mengonfirmasi secara terpisah bahwa `git rm --cached` mengubah tracking pada HEAD, tetapi tidak menghapus blob dari commit sebelumnya.
- **Lingkungan/branch:** Clone dan branch eksperimen yang sama dengan Percobaan 2.1.
- **Perintah inti:** `git rm --cached .env`; commit; `git ls-files --error-unmatch .env`; `git show 95fc7ade78f0955ea758751d5095a2b9d017b292:.env`.
- **Hasil dan bukti:** Commit penghapusan `94296d4103dd8fb52b6aff76e00affb726821fb8` berada di HEAD dan tidak melacak `.env`; `git show` pada commit sebelumnya tetap menemukan berkas dummy. Run CI setelah penghapusan berhasil pada [workflow run 132](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126918135).
- **Interpretasi dan mitigasi:** `git rm --cached` menghentikan tracking pada snapshot berikutnya, bukan membersihkan riwayat. Praktik aman adalah mencegah `.env` masuk commit sejak awal dan merotasi secret sungguhan jika pernah terpublikasi.
- **Status:** **BERHASIL**.

### 2.3 Simulasi Merge Conflict Individual

- **Tujuan:** Mengamati konflik saat dua perubahan berbeda menyentuh baris yang sama, lalu menyelesaikannya dengan mempertahankan maksud kedua perubahan.
- **Lingkungan/branch:** Clone sementara terpisah `kampuslms-w13-break-conflict`, dengan dua branch lokal `w13-conflict-a` dan `w13-conflict-b` dari base yang sama. Ini simulasi individual menggunakan dua branch lokal, bukan kolaborasi dua orang.
- **Perintah inti:** Membuat perubahan komentar latihan yang berbeda pada baris yang sama di `routes/web.php`; commit pada masing-masing branch; `git merge w13-conflict-b`; periksa marker konflik; gabungkan maksud kedua sisi; lalu jalankan `git diff --check` dan `php -l routes/web.php`.
- **Hasil dan bukti:** Anchor bersama `b036275250098e7d4dc61428d9d48fc0fb39156e`; commit branch A `46aabbf37386db96dd0afe15ac77507286be519c`; commit branch B `65adefa2536918c29233d7855a8055f8068b12a3`; resolusi merge `109fd13237f59129d422e325698d36ff8cc41c4e`. Konflik muncul pada komentar latihan dan diselesaikan dengan menggabungkan isi komentar tanpa mengubah route fungsional. `git diff --check` dan `php -l routes/web.php` lulus. `php artisan route:list` tidak dijalankan karena dependency `vendor` tidak tersedia pada clone tersebut.
- **Interpretasi dan mitigasi:** Marker konflik perlu ditinjau bersama konteks perubahan, bukan diselesaikan dengan memilih satu sisi secara otomatis. Simulasi ini menunjukkan proses teknis, tetapi tidak menggantikan latihan koordinasi dengan anggota lain.
- **Status:** **BERHASIL** — konflik lokal berhasil dipicu dan diselesaikan; pemeriksaan daftar route tidak dilakukan.

### 2.4 Pemeriksaan Branch Protection dan Uji Direct Push

- **Tujuan:** Memastikan aturan perlindungan `main` sebelum mempertimbangkan pengujian direct push.
- **Lingkungan/branch:** Repository Kelompok 05 saja; tidak ada perubahan konfigurasi atau upaya push ke `main`.
- **Perintah inti:** Memeriksa ketersediaan GitHub CLI dan meminta endpoint branch protection untuk `main` melalui API GitHub.
- **Hasil dan bukti:** `gh` CLI tidak tersedia pada lingkungan. Endpoint perlindungan yang diakses tanpa autentikasi menghasilkan HTTP 401, sehingga kewajiban PR, jumlah approval, required CI check, pencegahan force-push/deletion, dan `enforce_admins` tidak dapat diverifikasi. SHA remote `main` yang diketahui saat pemeriksaan adalah `89755558cd671d7a201fa99fcfee6547ed099328`.
- **Interpretasi dan mitigasi:** Karena perlindungan dan enforcement admin tidak dapat dipastikan, percobaan direct push berisiko mengubah `main` dan tidak dilakukan. Verifikasi perlu dilakukan oleh pemilik repo melalui pengaturan Rulesets/Branch protection atau GitHub CLI yang telah terautentikasi dan berizin.
- **Status:** **BLOCKED** — konfigurasi perlindungan tidak dapat dibaca; tidak ada percobaan push.

### 2.5 Test Gagal yang Disengaja dan Batas Bukti PR

- **Tujuan:** Membuktikan test yang dirancang gagal memang menghasilkan kegagalan lokal dan menilai apakah status check PR dapat diamati.
- **Lingkungan/branch:** Clone sementara `kampuslms-w13-break-failing-ci`, branch lokal `feat/w13-ci-failure-demo`, dari base SHA `89755558cd671d7a201fa99fcfee6547ed099328`. Tidak ada `.env` pada clone; pengujian menggunakan environment sementara dengan nilai dummy, bukan file secret.
- **Perintah inti:** `composer install --working-dir=<clone> --no-interaction --no-progress --prefer-dist`; `php artisan test tests/Feature/W13IntentionalFailureTest.php --no-coverage`; commit lokal test.
- **Hasil dan bukti:** `tests/Feature/W13IntentionalFailureTest.php` sengaja membandingkan dua string yang berbeda. Perintah test menghasilkan **1 failed (1 assertion)** sebagaimana dirancang. Commit lokal `7f568ff8bbf0414013312fa98996e8b9550755fe` pada branch eksperimen. Tidak ada branch yang dipush, PR, URL workflow, atau status check PR.
- **Interpretasi dan mitigasi:** GitHub CLI tidak tersedia dan tool GitHub yang tersedia tidak menyediakan pembuatan PR; hak tulis untuk membuat PR karenanya tidak dapat digunakan. Eksperimen dihentikan setelah bukti kegagalan lokal tersimpan, tanpa mengklaim bahwa branch protection memblokir merge. Perlu akses GitHub berizin untuk mengulang bagian publikasi, membuka PR, lalu memeriksa required check.
- **Status:** **BLOCKED** — test lokal gagal sesuai rencana, tetapi publikasi branch dan pembuktian blokir merge tidak dilakukan.

### 2.6 Simulasi Metadata Author Git

- **Tujuan:** Menunjukkan bahwa metadata author pada commit dapat ditentukan ketika commit dibuat dan tidak membuktikan identitas pembuat secara mandiri.
- **Lingkungan/branch:** Repository Git lokal sementara `kampuslms-w13-break-author`; tidak memiliki remote dan tidak dipush.
- **Perintah inti:** Membuat berkas latihan kecil, lalu `git -c user.name='Experiment Runner' -c user.email='runner@example.invalid' commit --author='Simulasi Penulis <simulasi@example.invalid>' -m 'test(w13): demonstrate configurable Git author metadata'`; memeriksa `git log -1 --format`.
- **Hasil dan bukti:** Commit lokal `0b52b8e637c4331b2d66cd98662a611d3d35f36b` mencatat author `Simulasi Penulis <simulasi@example.invalid>` dan committer `Experiment Runner <runner@example.invalid>`. Tidak ada remote.
- **Interpretasi dan mitigasi:** Nama dan email author adalah metadata yang dapat diatur oleh pembuat commit. Evaluasi kontribusi perlu mempertimbangkan bukti lain seperti konfigurasi, riwayat perubahan, review, dan pemahaman atas kode; metadata saja tidak cukup.
- **Status:** **BERHASIL**.

## 3. BUILD — Standardisasi Repository Kelompok 05

### 3.1 Template Pull Request

Template `.github/pull_request_template.md` sudah tersedia sebelum tahap ini, sehingga tidak dibuat ulang. Isinya disempurnakan dengan bagian perubahan, alasan, cara menguji, daftar periksa, dan pengungkapan bantuan AI. Pemeriksaan otorisasi, N+1, feature test, dan build frontend diberi konteks “bila relevan” agar checklist tetap dapat dipakai untuk jenis perubahan yang berbeda tanpa menghilangkan butir penting modul.

### 3.2 Branch Protection dan CI

Workflow `.github/workflows/ci.yml` bernama **CI KampusLMS**, menjalankan job bernama `tests`, dan mengatur `pull_request` dengan target `main`. Job `tests` pada [workflow run 132](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126918135) berakhir sukses pada push branch eksperimen; ini membuktikan nama job dan keberhasilan run tersebut, bukan bahwa required check sudah dikonfigurasi atau diuji pada PR.

Status perlindungan `main` **belum dapat diverifikasi**. GitHub CLI `gh` tidak tersedia pada lingkungan, dan permintaan endpoint Branch Protection tanpa autentikasi menghasilkan HTTP 401. Tidak ada perubahan pada pengaturan repository. Karena itu, belum dapat dinyatakan bahwa PR wajib, satu approval diwajibkan, job `tests` menjadi required check, force push/deletion dicegah, atau admin terkena enforcement.

Admin repository dapat memeriksa **Settings → Branches → Add branch protection rule** dengan pola `main`, kemudian mengaktifkan PR wajib, minimal satu approval, status checks wajib setelah check `tests` terlihat pada PR dan sukses, serta pencegahan force-push dan deletion. Aktifkan enforcement untuk administrator bila memang menjadi persyaratan tim. Setelah penyimpanan, baca ulang aturan dan uji melalui PR sebelum menyatakan perlindungan aktif.

### 3.3 Evaluasi README dan Pembagian Peran

README telah menyebut lima anggota, NIM, role, dan akun GitHub. Perbandingan dengan `git shortlog -sne --no-merges origin/main` menghasilkan entri author mentah berikut:

| Anggota pada README | Entri shortlog yang dapat dibandingkan | Non-merge commit |
|---|---|---:|
| Muhammad Rifa Al Rizqul Aulia | `rifarizqul-itk <182073356+rifarizqul-itk@users.noreply.github.com>` | 26 |
| Muhammad Rifa Al Rizqul Aulia | `rifarizqul-itk <10241050@student.itk.ac.id>` | 21 |
| Nova Reskianti | `NovaRes06 <10241058@student.itk.ac.id>` | 14 |
| Muhammad Zaldy Syah Firaz | `Muhammad Zaldy Syah Firaz <10241054@student.itk.ac.id>` | 19 |
| Muhammad Zaldy Syah Firaz | `unknown <10241054@student.itk.ac.id>` | 1 |
| Muhammad Yuspa Ardiansyah | `ardiansyah24 <10241052@student.itk.ac.id>` | 21 |
| Muhammad Farin Murtadho Syafiq | `BerserkerGats <farin.raihan@gmail.com>` | 3 |
| Muhammad Farin Murtadho Syafiq | `muhammadfarin18 <yoasobi.soca@gmail.com>` | 2 |

Baris Rifa dengan email GitHub dan email NIM dipisahkan; hal yang sama berlaku bagi `unknown` dengan email NIM Zaldy dan dua alias Farin. Kecocokan email membantu perbandingan, tetapi tidak cukup untuk menggabungkan semua identitas secara otomatis. Tabel peran dipertahankan karena audit jumlah commit tidak cukup untuk menyimpulkan atau mengubah pembagian jobdesk, dan metadata author tidak membuktikan tugas atau kualitas kontribusi.

Informasi branching README diperbaiki agar sesuai dengan branch remote: tersedia branch seperti `dev-yuspa`, tetapi tidak ada branch bernama tepat `dev`. Klaim bahwa `main` telah dilindungi juga diganti dengan keterangan yang menyatakan statusnya perlu diverifikasi. Tautan ke laporan praktikum ini ditambahkan pada bagian dokumentasi.

### 3.4 Validasi Akhir

- **Markdown:** Heading dan checklist PR template, heading dokumen, keseimbangan fenced code block, tautan laporan lokal, newline akhir, serta whitespace pada baris yang ditambahkan diperiksa. Empat baris awal README memakai dua spasi sebagai pemisah baris Markdown dan dibiarkan. Tidak ada konfigurasi atau linter Markdown di repository.
- **`php artisan test --no-coverage`:** **lulus, 70 test dan 303 assertion**. Pengujian dijalankan dengan `APP_KEY` dummy serta SQLite `:memory:`; migrasi hanya berjalan pada database sementara pengujian.
- **`npm run build`:** **lulus** setelah dependency yang sudah tercantum di lockfile dipulihkan dengan `npm ci --no-audit --no-fund`. Build Vite berhasil; `public/build/` diabaikan Git.
- **`git diff --check`:** lulus.
- **Berkas yang berubah:** `.github/pull_request_template.md`, `README.md`, dan laporan ini. Tidak ada perubahan pada `.env` maupun dependency manifest/lockfile. `vendor`, `node_modules`, dan hasil build tidak ditambahkan ke Git; `node_modules` dipulihkan dari lockfile hanya untuk validasi build.
- **Branch protection:** belum dapat diverifikasi atau diubah karena akses GitHub CLI tidak tersedia dan API tanpa autentikasi mengembalikan HTTP 401.

## 4. BUILD — Persiapan dan Pelaksanaan Peer Review

### 4.1 Repository dan PR Target

Repository yang diperiksa adalah [ItsShika24/kampuslms-kelompok-06](https://github.com/ItsShika24/kampuslms-kelompok-06). Daftar PR terbuka terbaru yang diperoleh pada 18 November 2026 memuat:

| PR | Status | Judul | Base ← head | Author | File berubah |
|---|---|---|---|---|---|
| [#50](https://github.com/ItsShika24/kampuslms-kelompok-06/pull/50) | Open | `fix(review): eager load materials and submissions in course destroy t…` | `main` ← `Shabrina-Azzahra-13` | `shabrinakeif` | 5: controller, script, dan dokumentasi |
| [#49](https://github.com/ItsShika24/kampuslms-kelompok-06/pull/49) | Open | `docs(w13): tambah laporan refleksi git dan dokumentasi break test` | `main` ← `Oktavia-Nur-Minggu-13` | `06Victoire` | 1: laporan dokumentasi Minggu 13 |

Selain #50, tidak ditemukan PR terbuka lain yang memuat perubahan source code. PR #49 hanya menambah `docs/Minggu13/laporan-minggu-13-oktavia.md`, sehingga bukan kandidat untuk review kode tiga kategori. Berdasarkan informasi koordinasi praktikum, PR #50 telah menjadi target review Nova Reskianti. Untuk menghindari duplikasi dan karena belum ada penetapan target lain, **status peer review: MENUNGGU KONFIRMASI TARGET**. Tidak ada review yang dikirim.

### 4.2 Pemahaman Perubahan PR

Ringkasan berkas PR #50 diperoleh dari commit head `801a8b58c00e747195b83691fe465ebb5cd3c3bb` dan keadaan branch PR tersebut. Perubahan yang terlihat adalah:

- `app/Http/Controllers/CourseController.php`: lima baris ditambahkan pada `destroy`; setelah sinkronisasi user dan pemeriksaan `Gate::authorize('delete', $course)`, relasi `materials` dan `assignments.submissions` dimuat melalui `loadMissing` sebelum iterasi pembersihan file dan penghapusan course.
- `scripts/test-queue.sh`: skrip shell baru untuk memeriksa beberapa aspek worker antrean, status jobs, restart worker, dan log.
- Dokumentasi yang diubah/ditambah: `docs/Minggu12/laporan-minggu-12-shabrina.md`, `docs/Minggu13/laporan-minggu-13-shabrina.md`, dan `docs/deployment.md`. Isi laporan praktikum peer tidak digunakan sebagai sumber temuan.

Perubahan controller ditujukan untuk memuat relasi terlebih dahulu sehingga akses relasi dalam loop tidak memicu query per item. Ini menjelaskan maksud perubahan, tetapi belum merupakan verifikasi independen bahwa seluruh jalur query, konsumsi memori, dan penghapusan berkas sudah tepat. Pemeriksaan otomatis pada commit head PR menunjukkan check **Laravel Tests, Frontend Build & Migrations** sukses pada [workflow run 44040692839](https://github.com/ItsShika24/kampuslms-kelompok-06/actions/runs/44040692839). Hasil CI bukan pengganti telaah kode dan tidak berarti saya menjalankan aplikasi secara lokal.

### 4.3 Catatan Pemeriksaan Keamanan

Belum ada temuan keamanan yang ditetapkan. Jika #50 dikonfirmasi sebagai target, area yang dapat diperiksa adalah alur `destroy` di [`CourseController.php` pada branch PR](https://github.com/ItsShika24/kampuslms-kelompok-06/blob/801a8b58c00e747195b83691fe465ebb5cd3c3bb/app/Http/Controllers/CourseController.php#L150):

- Apakah `Gate::authorize('delete', $course)` melindungi tindakan untuk setiap role dan kondisi kepemilikan yang relevan?
- Apakah path file yang berasal dari record selalu dibatasi pada disk/lokasi yang semestinya sebelum file dihapus?
- Apakah kegagalan penghapusan file atau penghapusan record ditangani konsisten tanpa mengungkap path/data yang tidak semestinya?

Pertanyaan ini adalah agenda pemeriksaan, bukan klaim bahwa celah tersebut ditemukan.

### 4.4 Catatan Pemeriksaan Performa

Belum ada temuan performa yang ditetapkan. Area pada [`CourseController.php:158`](https://github.com/ItsShika24/kampuslms-kelompok-06/blob/801a8b58c00e747195b83691fe465ebb5cd3c3bb/app/Http/Controllers/CourseController.php#L158) dan loop penghapusan berikutnya yang perlu dipahami:

- Apakah `loadMissing(['materials', 'assignments.submissions'])` benar-benar mencegah query berulang pada relasi yang diakses dalam loop di semua keadaan model?
- Untuk course dengan banyak assignment/submission, apakah memuat seluruh relasi sekaligus memiliki batas konsumsi memori yang dapat diterima?
- Apakah volume query dan jumlah record yang dibaca dapat diukur pada skenario data besar, bukan hanya diasumsikan dari adanya eager loading?

Tidak ada profiling, query counter, atau pengujian skala yang dijalankan untuk laporan ini.

### 4.5 Catatan Pemeriksaan Struktur dan Keterbacaan

Belum ada temuan struktur yang ditetapkan. Pertanyaan untuk peninjauan bersama apabila target dikonfirmasi:

- Apakah tanggung jawab penghapusan file terkait course dan submission sudah cukup jelas ditempatkan di controller, atau lebih mudah diuji bila dipisahkan ke service/action?
- Pada `scripts/test-queue.sh`, apakah prasyarat, kondisi ketika Supervisor/worker tidak tersedia, exit code, dan efek `php artisan queue:restart` dijelaskan secara eksplisit sebelum skrip dipakai?
- Apakah penambahan skrip dan perubahan laporan/deployment dalam satu PR masih sesuai dengan fokus PR, atau lebih mudah direview jika dipisah?

Pertanyaan tersebut tidak menyatakan controller atau skrip memiliki cacat; diperlukan pemeriksaan dan konteks penggunaan sebelum membuat komentar review.

### 4.6 Temuan Final dan Tanggapan Kelompok

- **Temuan final:** belum ada. Tidak ada klaim bug yang dibuat dan tidak ada temuan dari catatan Nova yang disalin.
- **Lokasi-risiko-saran:** belum disusun karena target review belum dikonfirmasi dan saya belum memvalidasi area pemeriksaan sebagai masalah nyata.
- **Status:** **MENUNGGU KONFIRMASI TARGET**.
- **Review GitHub:** belum dilaksanakan dan tidak disubmit. Tidak ada tanggapan kelompok yang dapat dilaporkan.
- **Uji lokal:** belum dijalankan. Belum dilakukan clone, instalasi dependensi, migrasi, atau pengujian; database lokal/produksi tidak disentuh.

## 5. Checkpoint 13.5 — Evaluasi Pembelajaran Minggu 13

### 5.1 Berapa commit atas nama saya?

Audit branch proyek dengan `git shortlog` dan pemisahan nama author menghasilkan **42 commit** pada dua entri yang telah dikonfirmasi sebagai identitas saya: `ardiansyah24` dengan 25 commit dan `Muhammad Yuspa Ardiansyah` dengan 17 commit. Angka ini tidak memasukkan commit dari `refs/agents/*` yang merupakan riwayat checkpoint alat. Jumlah tersebut adalah hitungan metadata author, bukan ukuran kualitas atau usaha.

### 5.2 Mengapa `git rm --cached .env` tidak cukup setelah file ter-push?

Perintah itu mengeluarkan `.env` dari index dan snapshot commit berikutnya, tetapi tidak menghapus commit lama yang sudah menyimpan file tersebut. Eksperimen memperlihatkan file dummy tidak lagi dilacak pada commit kedua, namun masih dapat dibaca dari commit pertama. Jika file yang terungkap berisi kredensial sungguhan, secret harus segera dirotasi atau dicabut; pembersihan riwayat adalah tindakan tambahan, bukan pengganti rotasi.

### 5.3 Tunjukkan satu temuan review yang saya tulis. Jelaskan risikonya.

Belum ada temuan review yang saya tulis. PR kode terbuka yang ditemukan adalah PR #50, tetapi targetnya tumpang tindih dengan target review Nova menurut koordinasi yang tersedia. Saya tidak mengirim review maupun mengklaim area pemeriksaan sebagai bug.

### 5.4 Tunjukkan satu temuan yang masuk ke kelompok saya.

Belum ada temuan peer review yang masuk pada pekerjaan Minggu 13 ini. Perubahan template dan dokumentasi pada branch fitur belum dipublikasikan sebagai PR pada saat laporan difinalisasi, sehingga belum ada tanggapan review terhadapnya.

### 5.5 Mengapa PR sebaiknya kecil dan berfokus pada satu fitur?

PR yang kecil dan terfokus lebih mudah dipahami, diuji, dan ditinjau dalam konteks perubahan yang jelas. Pemisahan pekerjaan mengurangi kemungkinan perubahan yang tidak berkaitan saling menutupi, memudahkan pelacakan sumber regresi, dan membuat diskusi serta tindak lanjut lebih terarah.

### 5.6 Mengapa kontribusi tidak dinilai hanya dari jumlah commit?

Jumlah commit hanya menghitung rekaman perubahan dan sensitif terhadap kebiasaan memecah atau menggabungkan pekerjaan. Angka itu tidak menunjukkan kesulitan teknis, kualitas hasil, dampak, tanggung jawab lintas peran, atau proses review. Karena itu, shortlog perlu dibaca bersama isi diff, konteks tugas, hasil pengujian, dan pemahaman kontributor.

## 6. Kesimpulan dan Status Pemenuhan Minggu 13

| Bagian | Status | Ringkasan |
|---|---|---|
| READ | Selesai | Riwayat branch proyek, identitas author yang dikonfirmasi, pola pesan, commit besar, dan alur branch dianalisis. |
| BREAK 1–2 | Selesai | `.env` dummy dipush pada branch sementara; CI menolak commit pertama dan menerima keadaan setelah file dikeluarkan dari HEAD. |
| BREAK 3 | Selesai | Konflik dipicu dan diselesaikan pada dua branch lokal oleh satu orang; bukan simulasi kolaborasi dua anggota. |
| BREAK 4 | Dilewati / blocked | Proteksi tidak dapat diverifikasi (CLI tidak tersedia, endpoint tanpa autentikasi merespons 401); direct push tidak dicoba demi keamanan `main`. |
| BREAK 5 | Parsial / blocked | Test gagal terbukti secara lokal, tetapi branch tidak dipush dan PR/required-check tidak diuji. |
| BREAK 6 | Selesai | Metadata author Git didemonstrasikan pada repository lokal tanpa remote. |
| BUILD Kelompok 05 | Parsial | Template PR dan README diperbarui serta diuji; Branch Protection belum dapat diverifikasi atau diubah. |
| Peer review Kelompok 06 | Menunggu koordinasi | PR #50 satu-satunya PR terbuka berisi source code, tetapi sebelumnya disebut sebagai target Nova; tidak ada review yang dikirim. |
| FIX repository `kampuslms-broken` | Dilewati | Bagian FIX dilewati sesuai instruksi; tidak ada clone atau perbaikan yang diklaim. |

Eksperimen yang melibatkan publikasi dibatasi pada branch sementara Kelompok 05 dengan nilai `.env` dummy. Branch remote percobaan dan clone temporer telah dibersihkan; `main` tidak berubah. Tidak ada commit historis yang diamend, di-rebase, atau dipaksa push.

### Validasi dan perubahan repository

- **Dokumen utama:** `docs/minggu-13-Muhammad-Yuspa-Ardiansyah.md`.
- **Berkas lain yang berubah:** `.github/pull_request_template.md` dan `README.md`.
- **Branch kerja:** `feat/week-13-yuspa`, berbasis `origin/main` SHA `89755558cd671d7a201fa99fcfee6547ed099328`. Remote tidak memiliki branch bernama tepat `dev`; `main` dipilih sebagai base yang tersedia dan juga target PR dalam workflow CI.
- **Pengujian Laravel:** `php artisan test --no-coverage` lulus dengan 70 test dan 303 assertion, menggunakan APP_KEY dummy dan SQLite in-memory.
- **Build frontend:** `npm run build` lulus setelah dependency lokal dipulihkan dari lockfile dengan `npm ci --no-audit --no-fund`.
- **Markdown dan whitespace:** heading, link lokal, keseimbangan code fence, whitespace pada bagian yang diedit, dan `git diff --check` diperiksa. Konfigurasi markdownlint tidak ditemukan.
- **Berkas sensitif dan hasil build:** `.env` asli tidak dibaca atau diubah; tidak ada `.env`, dependency terpasang, atau hasil build yang ditambahkan ke Git.
- **CI/eksperimen:** [workflow run 131](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126615148) gagal pada penjagaan `.env` sebagaimana diharapkan; [run 132](https://github.com/muhammadzaldysyahfiraz/kampuslms-kelompok-05/actions/runs/44126918135) sukses setelah `.env` dikeluarkan dari HEAD.
- **Pull request fitur Minggu 13:** belum dibuat pada saat catatan ini difinalisasi. Belum ada commit SHA baru atau URL PR untuk perubahan laporan/template/README.
- **Branch Protection:** belum diverifikasi karena `gh` CLI tidak tersedia dan API tanpa autentikasi menghasilkan HTTP 401.
