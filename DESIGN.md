# KampusLMS Design System Specification (`DESIGN.md`)

> **Source of Truth**: Disintesis langsung secara presisi dari referensi resmi UI/UX Case Study **`DESIGN_REFERENCE.pdf`** (*"Academy — Learning Management System for IT-University"*) beserta aset gambar `image_001` – `image_018`.

---

## 1. Filosofi Desain & Atmosfer Visual

### 1.1 Visi Produk
KampusLMS dirancang sebagai platform pembelajaran terpadu perguruan tinggi teknik yang memadukan **fungsionalitas akademik yang terstruktur** dengan **pengalaman belajar digital yang energik, ramah (*approachable*), dan modern**.

### 1.2 Karakter & Mood Visual
Mengacu pada halaman 1–3 di `DESIGN_REFERENCE.pdf`:
* **Modern & Clean**: Tata letak bento/grid modular yang rapi tanpa polusi visual.
* **Energik & Ekspresif**: Menggunakan *multi-accent color system* (5 warna aksen khas) untuk membedakan kategori mata kuliah, tingkat urgensi penugasan, dan status kemajuan.
* **Friendly Tech / Human-centric**: Menggunakan sudut kartu yang membulat halus (*generous rounded corners*), pill badges, dan ikon aksen semantik bintang/kilau (`✦`).
* **Dual Palette Strategy**:
  * **Dark Canvas / Command Center**: Sisi navigasi (Sidebar) menggunakan tema gelap pekat (`#1E1E26`) untuk fokus navigasi yang stabil.
  * **Light Content Surface**: Area kerja utama (*content area*) menggunakan latar bersih (`#F8FAFC`) dengan kartu putih (`#FFFFFF`) berkontras tinggi demi kenyamanan membaca teks akademik.

---

## 2. Palet Warna & Arsitektur Token (*Color Palette & Roles*)

Berdasarkan **Halaman 11 (Colors Guide)** dan palet UI dari `DESIGN_REFERENCE.pdf`, berikut pemetaan warna ke dalam token desain:

### 2.1 Multi-Accent Colors (Warna Aksen Utama)

| Token Name | Hex Code | RGB | Karakter Visual | Peran Fungsional dalam KampusLMS |
|---|---|---|---|---|
| `accent-amber` (Accent #1) | `#FFC152` | `255, 193, 82` | Warm Sunflower Yellow | Sorotan hero, badge penilaian, kartu kategori Desain/Kreatif, maskot, rating bintang. |
| `accent-mint` (Accent #2) | `#5DD299` | `93, 210, 153` | Fresh Emerald Mint | Indikator *Course Completed*, badge status aktif, progress bar lulus, tombol sukses. |
| `accent-sky` (Accent #3) | `#66A7F2` | `102, 167, 242` | Soft Vibrant Azure | Tautan interaktif, status webinar/live class, badge mata kuliah pemrograman, link detail. |
| `accent-lilac` (Accent #4) | `#F1D2F1` | `241, 210, 241` | Soft Pastel Lavender | Background chip/tag kategori sekunder, badge materi tambahan, counter lampiran. |
| `accent-coral` (Accent #5) | `#FE774C` | `254, 119, 76` | Radiant Tangerine / Coral | Tombol CTA prioritas tinggi, status tugas mendekati tenggat (*due soon*), peringatan hapus. |

### 2.2 Neutral & Surface Colors (Latar, Kartu, & Border)

| Token Name | Hex Code | Peruntukan & Hirarki |
|---|---|---|
| `canvas-dark` | `#1E1E26` | Latar belakang sidebar navigasi utama dan dark mode canvas. |
| `surface-dark` | `#252530` | Kontainer elemen aktif pada sidebar, card dark mode. |
| `border-dark` | `#363644` | Garis pembatas halus pada sidebar dan header gelap. |
| `canvas-light` | `#F8FAFC` | Latar belakang halaman konten utama (Slate 50). |
| `surface-white` | `#FFFFFF` | Latar belakang modul kartu (*cards*), tabel data, dan popup modal. |
| `border-light` | `#E2E8F0` | Border pemisah kartu, tabel, dan form input (Slate 200). |
| `border-subtle` | `#F1F5F9` | Divider tipis antar baris data (Slate 100). |

### 2.3 Text Hierarchy Colors (Warna Teks)

| Token Name | Hex Code | Kontras & Penggunaan |
|---|---|---|
| `text-primary` | `#0F172A` | Judul utama, teks heading, isi data tabel (Slate 900). |
| `text-body` | `#334155` | Paragraf deskripsi mata kuliah, petunjuk formulir (Slate 700). |
| `text-muted` | `#64748B` | Label sekunder, tanggal rilis, nama dosen kecil (Slate 500). |
| `text-light-primary` | `#FFFFFF` | Teks putih di atas canvas gelap / tombol berwarna kontras. |
| `text-light-muted` | `#94A3B8` | Label navigasi sidebar yang tidak aktif (Slate 400). |

---

## 3. Standar Tipografi (*Typography Style Guide*)

Mengacu secara mutlak pada **Halaman 10 (Style Guide)** di `DESIGN_REFERENCE.pdf`:

### 3.1 Font Family
* **Primer**: `Plus Jakarta Sans`, `Inter`, atau sans-serif modern setara.
* **Data & Kode**: Monospace berfitur `tabular-nums` untuk kode mata kuliah, angka SKS, skor nilai, dan durasi waktu.

### 3.2 Matriks Hirarki Tipografi

| Level / Style | Weight | Font Size | Line Height | Tracking | Aturan Penggunaan (*Rules*) |
|---|---|---|---|---|---|
| **Header 1** | ExtraBold (800) | `30px` (`1.875rem`) | `41px` | `-0.02em` | Judul besar selamat datang (*welcome screen*) dan hero banner. |
| **Header 2** | ExtraBold (800) | `28px` (`1.75rem`) | `26px` | `-0.015em` | Judul halaman utama (*main page titles* seperti "Daftar Mata Kuliah"). |
| **Header 3** | ExtraBold (800) | `20px` (`1.25rem`) | `26px` | `-0.01em` | Judul form penambahan/edit data, judul kartu (*cards*), dan header widget. |
| **Button Text** | Bold (700) | `14px` (`0.875rem`) | `22px` | `0` | Teks di dalam seluruh tombol aksi interaktif (*primary, secondary, danger*). |
| **Body Bold** | SemiBold (600) | `14px` (`0.875rem`) | `22px` | `0` | Sorotan nilai penting, nama dosen pengampu, label field form, status tabel. |
| **Body Regular** | Regular (400) | `14px` (`0.875rem`) | `22px` | `0` | Teks isi deskripsi mata kuliah, paragraf penjelasan, isi tabel reguler. |
| **Badge / Caption** | SemiBold (600) | `11px` - `12px` | `16px` | `+0.04em` | Pill badge kategori, kode mata kuliah, semester chip (uppercase). |

---

## 4. Bentuk, Geometri, & Elevasi (*Shape & Elevation*)

Mengacu pada halaman 9, 12, dan 13 di `DESIGN_REFERENCE.pdf`:

### 4.1 Border Radius (Kelengkungan Sudut)
* **Pill (`rounded-full`)**: Digunakan untuk badge status (`Aktif`, `Selesai`), tombol filter kategori, avatar tag, dan indikator persentase.
* **Cards & Containers (`rounded-2xl` / 16px - 20px)**: Kartu mata kuliah (*Course Card*), bento widget dashboard, dan kontainer tabel data.
* **Inputs & Standard Buttons (`rounded-xl` / 10px - 12px)**: Field form input text, textarea, select dropdown, serta tombol aksi form.
* **Small Elements (`rounded-lg` / 8px)**: Thumbnail materi, pagination item, dan badge ikon kecil.

### 4.2 Bayangan & Kedalaman (*Elevation & Shadows*)
* **Flat + Border (Default)**: Kartu menggunakan `border border-slate-200/80` yang bersih dengan elevasi halus `shadow-sm`.
* **Card Hover State**: Transisi mulus saat kursor melayang ke kartu: `hover:-translate-y-1 hover:shadow-md transition-all duration-200`.
* **Elevated Overlays (Modals & Dropdowns)**: Menggunakan `shadow-xl shadow-slate-900/10 border border-slate-200`.

---

## 5. Arsitektur Tata Letak (*Layout Architecture*)

### 5.1 Tata Letak Utama — Dashboard (3-Panel Layout)

Berdasarkan `image_013.jpg` (Halaman 12 referensi):

```
+------+------------------------------------------+--------------------+
| SIDE |   Main Content Area                      |  Right Panel       |
| BAR  |                                          |  (Profile/Stats)   |
|      |  Dashboard                [Search...]    |  [Avatar + Name]   |
| Nav  |  +---------+ +---------+ +---------+    |  [Flame Streak]    |
| Items|  |My Course| |My Course| |My Course|    |  [Calendar May 22] |
|      |  |Cinema4D | |Front-End| |Grph.Des.|    |  [Course Progress] |
|      |  |70%      | |20%      | |10%      |    |  [Schedule]        |
|      |  +---------+ +---------+ +---------+    |  [Webinar/HW]      |
|      |  [Rating Widget] [My Scores Graph]       |                    |
|      |  [Homeworks Section]                     |                    |
+------+------------------------------------------+--------------------+
```

**Detail komponen panel kanan (Right Panel):**
- **Avatar + Nama + Email** pengguna yang sedang login.
- **Flame/Streak Counter** — menampilkan jumlah hari belajar berturut-turut.
- **Mini Calendar** — tampilan bulan berjalan dengan tanggal-tanggal yang ditandai.
- **Courses Progress Widget**: Statistik dalam 4 kotak grid:
  - `75/110` Visited lectures | `15/25` Completed HW
  - `300/1000` Bonuses | `8/10` Certificates
- **Schedule Widget**: Daftar jadwal terdekat (Webinar, Homework).
- **Notification Banner**: Pop-up bawah yang menampilkan webinar yang akan segera dimulai.

**Detail widget area konten utama:**
- **My Courses Carousel**: Course card horizontal, masing-masing menampilkan nama, tanggal mulai, avatar dosen, progress bar, dan persentase.
- **Rating Widget**: Menampilkan peringkat pengguna, poin, dan tabel leaderboard dengan kolom Student & HW/Score. Dilengkapi dropdown filter per mata kuliah.
- **My Scores Graph**: Grafik garis area untuk skor dari waktu ke waktu. Dapat difilter per mata kuliah.
- **Homeworks Section**: Daftar tugas terbaru dengan label kursus, level kesulitan, dan warna aksen per kategori.

### 5.2 Tata Letak Auth — Split Panel (Login, Forgot, Change Password)

Berdasarkan `image_012.jpg` dan `image_013.jpg`:

```
+---------------------------+---------------------------+
|  LEFT PANEL               |  RIGHT PANEL              |
|  (Ilustrasi + Branding)   |  (Form)                   |
|                           |                           |
|  Ilustrasi karakter 2D    |  Logo "academy" kiri atas |
|  (background hijau/teal)  |                           |
|  figur manusia dan benda- |  H3: "Login"              |
|  benda ikon di sekitarnya |  Sub: instruksi singkat   |
|                           |                           |
|  Background:              |  [Email field]            |
|  Hijau Teal (untuk Login) |  [Password field]         |
|  atau Purple/Lilac        |  [Forgot password? link]  |
|  (untuk Forgot Password)  |  [Primary CTA Button]     |
|                           |                           |
+---------------------------+---------------------------+
```

- Lebar panel: 50%/50% pada layar desktop.
- Panel kiri bersifat dekoratif — tidak ada teks interaktif.
- Panel kanan selalu berlatar `#FFFFFF` (surface-white).

### 5.3 Tata Letak Halaman Ubah Password (*Change Password Page*)

Berdasarkan `image_013.jpg` (atas):
- Menggunakan layout split-panel yang sama (ilustrasi kiri, form kanan).
- **Judul**: "Change password"
- **Subjudul**: Petunjuk singkat untuk membuat password baru.
- **Field 1**: "New password" (input dengan ikon mata/tampilkan)
- **Field 2**: "Confirm password"
- **Validasi Password — 5 Requirement Checklist**:
  Ditampilkan sebagai daftar dengan indikator warna (merah jika belum terpenuhi, hijau jika sudah):
  1. Minimum of 8 characters
  2. At least one lowercase letter
  3. At least one uppercase letter
  4. At least one number
  5. At least one special character
- **Tombol CTA**: "Reset and Login" — tombol hitam/gelap penuh lebar.
- **Link**: "FAQ" di sudut kanan bawah form.

### 5.4 Tata Letak Module Walkthrough Page

Berdasarkan `image_017.jpg` (Halaman 13 referensi):

> Setiap kursus terdiri dari **modul -> pelajaran (lessons) -> langkah (steps)**.

```
+--------+------------------------------------------+
|  SIDE  |  CONTENT AREA (Main)                     |
|  BAR   |  Breadcrumb: Courses / Cinema 4D / Mod 3 |
| (Dark) |  Module tabs: [01][02][03]...[07]        |
|        |                                          |
| Module |  H2: "1.2 Work with lighting"            |
| List   |  [Share] [Bookmark] icons (top right)    |
|        |                                          |
| Left   |  +------------------------------------+  |
| Sidebar|  |  VIDEO PLAYER                      |  |
| Panel: |  |  (Full width, 16:9 ratio)          |  |
|        |  |  [Play] [1x] [Timer] [Settings]   |  |
| Module |  +------------------------------------+  |
| 3. Dive|                                          |
| into   |  Tab bar: [About][Add. materials]        |
| dynamic|            [Resources][Discussions]      |
|        |            [Transcripts]                 |
| 02/07  |                                          |
| lessons|  "Timeline outline plan"                 |
| 35%    |  00:00-00:20  Parturient Venenatis       |
|        |  00:20-01:30  Inceptos                   |
| 20/100 |  ... (time-coded lesson outline)         |
| points |                                          |
| 20%    |  Description paragraph text              |
|        +------------------------------------------+
| Lesson |
| List:  |
| L1.Intro|
| [steps]|
| L2.Fund|
| [steps]|
| ...    |
+--------+
```

**Detail Left Sidebar (Module Panel):**
- Header modul: Nama modul aktif (ExtraBold), nomor pelajaran, dan poin kemajuan.
- Progress bar ganda: satu untuk lessons, satu untuk points (warna Amber `#FFC152`).
- Daftar lesson yang bisa di-expand/collapse, tiap lesson memiliki badge step berwarna (Mint = selesai, Amber = aktif, abu = terkunci).

**Detail Content Area:**
- **Breadcrumb navigasi** di bagian atas.
- **Module number tabs** (pill badge per nomor modul, yang aktif highlighted).
- **Video Player** dengan kontrol playback lengkap, tombol fullscreen, dan tombol screenshot/bookmark.
- **Tab Bar konten** bawah video: About, Additional materials, Resources, Discussions, Transcripts.
- **Timeline Outline Plan**: Daftar waktu + topik untuk pelajaran video yang sedang aktif.
- **Dua tombol aksi icon** di samping kanan judul: kalender (jadwalkan) & share.

### 5.5 Tata Letak Halaman Webinar (*Webinar Page*)

Berdasarkan `image_018.jpg` (Halaman 14 referensi):

```
+--------+----------------------------------+------------------+
| SIDE   |  MAIN VIDEO AREA                 |  CHAT ROOM       |
| BAR    |  Breadcrumb: Webinars / Title    |  Panel           |
| (Dark) |                                  |                  |
|        |  H2: Webinar Title               |  [Tab: Group]    |
|        |  Speaker info: Avatar + Name      |  [Tab: Private]  |
|        |  + Role                          |                  |
|        |  Audience avatars (+23)          |  Chat bubbles:   |
|        |  [Share][Bookmark] icons          |  - You (Amber)   |
|        |                                  |  - Teacher (bg)  |
|        |  +----------------------------+  |  - Others        |
|        |  |  VIDEO / SCREEN SHARE      |  |                  |
|        |  |  [Drawing tools sidebar]   |  |  [Type message]  |
|        |  |  (Pen, eraser, arrow, T,   |  |  [Send button]   |
|        |  |   shapes icons)            |  |                  |
|        |  +----------------------------+  +------------------+
|        |  Participant thumbnails row        |
|        |  [Nata Josten][Sam][Olga] +23     |
|        |                                   |
|        |  Tabs: [About][Resources]          |
|        |        [Transcripts]               |
|        |                                   |
|        |  Links section (Resources)         |
+--------+-----------------------------------+
```

**Detail Chat Room Panel:**
- Lebar panel kanan: ~30% dari area konten.
- Dua tab toggle: **Group** (chat grup semua peserta) dan **Private** (chat personal ke pengajar).
- Bubble chat:
  - **Pesan "You"**: Bubble berwarna Amber (`#FFC152`), right-aligned.
  - **Pesan lain**: Bubble putih/abu, left-aligned.
  - Setiap pesan menampilkan nama pengirim dan timestamp.
- Input bar bawah: ikon attachment, text field, ikon emoji, tombol Send (bulat berwarna aksen).

**Detail Area Video Webinar:**
- Bagian atas video: info speaker (foto, nama, peran/jabatan).
- **Drawing tools sidebar** (vertical, sisi kanan video): Pen, eraser, lasso, arrow, text tool, shape tools — memungkinkan anotasi langsung di atas tampilan layar yang dibagikan.
- **Participant thumbnails**: Baris foto peserta aktif di bawah video, dengan counter `+23` untuk peserta tambahan.
- Rekaman timer (`00:38:21`) di sudut kiri atas video (status live recording).

---

## 6. Spesifikasi Komponen Inti (*Core Components*)

### 6.1 Tombol (*Buttons*)
* **Primary Button (Coral CTA - Halaman 10 & 11)**:
  * Latar: `#FE774C` (Hover: `#E66236`)
  * Teks: Putih, Font Size 14px, Weight Bold (700).
  * Padding: `px-5 py-2.5`, Radius: `rounded-xl`.
* **Dark CTA Button** (digunakan pada Change Password):
  * Latar: `#1E1E26` (dark/hitam), full-width dalam konteks form auth.
  * Teks: Putih, Font Size 14px, Weight Bold (700).
* **Secondary / Action Button (Sky Blue)**:
  * Latar: `#66A7F2` (Hover: `#4F92E0`)
  * Teks: Putih, Font Size 14px, Weight Bold (700).
* **Destructive Button (Hapus Data)**:
  * Bentuk: Form dengan method spoofing `@method('DELETE')`.
  * Tipe 1 (Soft Danger): Latar `bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200`.
  * Tipe 2 (Solid Danger): Latar `bg-rose-600 text-white hover:bg-rose-700`.
* **Ghost / Back Button**:
  * Latar: Transparan, Border `border-slate-200`, Teks `text-slate-700 hover:bg-slate-100`.

### 6.2 Kartu Mata Kuliah (*Course Card*)
Berdasarkan tampilan di Dashboard (`image_013.jpg`) dan List of Courses:

* **Header Aksen Kategori**: Bagian atas kartu memiliki strip warna atau badge aksen (`#FFC152`, `#5DD299`, `#66A7F2`, atau `#FE774C`).
* **Tanggal Mulai**: Label kecil "Started DD.MM.YYYY" di bawah nama kursus.
* **Avatar Peserta**: Deretan foto profil peserta kursus (tumpukan/overlapping avatars).
* **Bookmark Icon**: Ikon di sudut kanan atas kartu untuk menambahkan ke favorit.
* **Informasi Utama**: Kode Mata Kuliah (badge mono), Nama Mata Kuliah (Header 3, Bold 20px), Nama Dosen (Body Bold).
* **Indikator Kemajuan (*Progress Bar*)**:
  * Background rel: `#F1F5F9` (Slate 100), tinggi 6px, `rounded-full`.
  * Bar terisi: `#5DD299` (Mint Green) atau warna aksen kategori, dengan label persentase (misal: `70%`).
* **Label Modul**: Teks kecil di bawah progress bar, misal `07/10 modules`.

### 6.3 Tabel Data (*Courses Table Index*)
* **Container**: `bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm`.
* **Header Tabel (`<thead>`)**:
  * Background: `bg-slate-50/80 border-b border-slate-200`.
  * Teks: `text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-4`.
* **Baris Data (`<tbody>`)**:
  * Teks: 14px Regular, Dosen 14px SemiBold, Kode Mata Kuliah dengan font monospace.
  * Hover state: `hover:bg-slate-50/60 transition-colors`.
  * Kolom Aksi: Group tombol navigasi rapi (`Lihat`, `Edit`, `Hapus`).

### 6.4 Form Input (*Form Styling*)
* **Field Input**:
  * `w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-[#66A7F2] focus:border-transparent transition-all`.
* **Label Field**: `block text-sm font-semibold text-slate-700 mb-1.5`.
* **Pesan Error / Validasi**: `text-xs text-rose-500 mt-1 font-medium`.
* **Password Requirement Checklist** (Change Password Page):
  * Setiap requirement ditampilkan sebagai baris dengan ikon indikator warna:
    * Merah/abu = belum terpenuhi
    * Hijau (`#5DD299`) = sudah terpenuhi
  * Text: 12px Regular.

### 6.5 Widget Navigasi Sidebar

Berdasarkan `image_013.jpg` dan `image_017.jpg`:

**Sidebar Navigasi Utama (semua halaman):**
```
[*] academy          <- Logo + nama brand
---
[D] Dashboard
[C] Courses          <- dengan sub-chevron/expand
[W] Webinars
[F] Favorites
[P] Portfolio
[M] Chat
[N] Notification
[S] Settings
---
[<] Log out
[?] FAQ
```

* Lebar sidebar: fixed ~220px pada desktop.
* Item aktif: background highlight `#252530` dengan aksen warna sisi kiri (left border accent).
* Item tidak aktif: text `#94A3B8`.
* Logo "academy" di atas dengan ikon titik oranye/coral sebagai bullet branding.

### 6.6 Favorites Widget

Berdasarkan `image_016.jpg`:
* Halaman **Favorites** dapat diakses dari sidebar navigasi.
* Fungsi: menampilkan **quick view** kursus yang telah di-bookmark.
* Cara menambah: klik ikon bookmark pada course card di manapun (Dashboard, List of Courses, dll.).
* Tampilan: Grid card yang sama dengan List of Courses.

### 6.7 Rating & Leaderboard Widget

Berdasarkan `image_013.jpg` (Dashboard — Rating section):
* **Judul**: "Rating" + dropdown filter per kursus.
* **Tombol "See all"** di pojok kanan.
* **Statistik pengguna saat ini**: Peringkat (misal: `14 naik`), Poin total (misal: `200`), Homework selesai (misal: `02/07`).
* **Tabel Leaderboard**:
  * Kolom: Rank, Student (nama + avatar kecil), HW/Score.
  * Baris yang menyorot pengguna aktif dengan background berbeda.

### 6.8 Score Graph Widget

Berdasarkan `image_013.jpg` (Dashboard — My Scores section):
* **Dropdown filter** per kursus di pojok kanan.
* **Grafik garis area** (area chart): sumbu X = tanggal/waktu, sumbu Y = skor.
* Warna area fill: Amber `#FFC152` atau aksen kursus yang dipilih.
* Tooltip saat hover menampilkan nilai skor.

### 6.9 Homework / Assignment Card

Berdasarkan `image_013.jpg` (Dashboard — Homeworks section):
* **Tombol "See all"** di pojok kanan.
* Tiap kartu tugas menampilkan:
  * Badge kategori (Graphic Design, Front-End, dll.) berwarna aksen.
  * Judul tugas (misal: "B. Design a brochure for a restaurant").
  * Level kesulitan + label kursus.
  * Indikator warna berdasarkan aksen kursus terkait.

---

## 7. Spesifikasi Komponen Khusus Halaman

### 7.1 Module Player — Tab Bar Konten

Berdasarkan `image_017.jpg`:

| Tab | Konten |
|---|---|
| **About** | Deskripsi pelajaran, timeline outline plan (waktu + topik). |
| **Additional materials** | File dan link tambahan terkait pelajaran. |
| **Resources** | Referensi dan sumber belajar eksternal. |
| **Discussions** | Forum diskusi antar peserta untuk pelajaran ini. |
| **Transcripts** | Transkrip teks otomatis dari video. |

### 7.2 Webinar Player — Tab Bar Konten

Berdasarkan `image_018.jpg`:

| Tab | Konten |
|---|---|
| **About** | Deskripsi webinar, speaker info. |
| **Resources** | File unduhan dan link terkait webinar. |
| **Transcripts** | Transkrip teks dari sesi webinar. |

### 7.3 Module Sidebar — Status Step/Lesson

| Indikator | Warna | Makna |
|---|---|---|
| Badge bulat Mint `#5DD299` | Hijau | Step/lesson selesai (*completed*). |
| Badge bulat Amber `#FFC152` | Kuning | Step/lesson sedang aktif (*in progress*). |
| Badge bulat abu/muted | Abu-abu | Step/lesson terkunci (*locked*). |

---

## 8. Petunjuk Implementasi Teknis (Tailwind & Laravel Blade)

### 8.1 Konfigurasi Token di `tailwind.config.js` atau `resources/css/app.css`

Tambahkan ekstensi warna resmi pada konfigurasi Tailwind:

```javascript
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        brand: {
          dark: '#1E1E26',
          surface: '#252530',
          border: '#363644',
        },
        lms: {
          amber: '#FFC152',
          mint: '#5DD299',
          sky: '#66A7F2',
          lilac: '#F1D2F1',
          coral: '#FE774C',
        }
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
      },
      borderRadius: {
        '2xl': '1rem',
        '3xl': '1.5rem',
      }
    },
  },
}
```

### 8.2 Utility CSS Semantik di `resources/css/app.css`

```css
@layer components {
  /* Tombol Utama (Coral) */
  .btn-primary {
    @apply inline-flex items-center justify-center px-5 py-2.5 bg-[#FE774C] hover:bg-[#e66236] text-white text-sm font-bold rounded-xl shadow-xs transition-all duration-150 active:scale-95;
  }

  /* Tombol Utama Gelap (untuk form auth) */
  .btn-dark {
    @apply inline-flex items-center justify-center w-full px-5 py-2.5 bg-[#1E1E26] hover:bg-[#2d2d3a] text-white text-sm font-bold rounded-xl shadow-xs transition-all duration-150 active:scale-95;
  }

  /* Tombol Sekunder (Sky Blue) */
  .btn-secondary {
    @apply inline-flex items-center justify-center px-4 py-2 bg-[#66A7F2] hover:bg-[#4f92e0] text-white text-sm font-bold rounded-xl shadow-xs transition-all duration-150;
  }

  /* Kartu LMS Kontainer */
  .lms-card {
    @apply bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow duration-200 p-6;
  }

  /* Badge Pill */
  .badge-mint {
    @apply inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#5DD299]/15 text-[#0F6841] border border-[#5DD299]/30;
  }

  /* Progress Bar */
  .progress-bar {
    @apply w-full h-1.5 bg-slate-100 rounded-full overflow-hidden;
  }
  .progress-bar-fill {
    @apply h-full bg-[#5DD299] rounded-full transition-all duration-300;
  }
}
```

---

## 9. Panduan Etika Desain (*Do's & Don'ts*)

* LAKUKAN: Gunakan named routes Laravel `route('courses.index')`, bukan URL hardcoded string.
* LAKUKAN: Selalu gunakan method `DELETE` dengan `@csrf` dan `@method('DELETE')` untuk tombol hapus data (sesuai modul Minggu 02 & Checkpoint 1).
* LAKUKAN: Terapkan salah satu dari 5 warna aksen khas (`amber`, `mint`, `sky`, `lilac`, `coral`) untuk memberikan identitas visual per mata kuliah atau status.
* LAKUKAN: Gunakan layout **split-panel 50/50** untuk semua halaman autentikasi (Login, Forgot Password, Change Password) — panel kiri ilustrasi, panel kanan form.
* LAKUKAN: Tampilkan **5 password requirement checklist** dengan indikator warna pada halaman Change Password.
* LAKUKAN: Gunakan sidebar fixed-width ~220px dengan latar `#1E1E26` yang konsisten di seluruh halaman yang memerlukan navigasi.
* LAKUKAN: Pada Module Walkthrough page, pisahkan navigasi modul (sidebar kiri) dengan konten video player + tab bar (area kanan).
* JANGAN: Menggunakan warna acak di luar token di atas.
* JANGAN: Menggunakan tag `<a>` untuk mutasi data (hapus/update).
* JANGAN: Mengabaikan hierarki ukuran font (selalu ikuti skala Header 1 = 30px, Header 2 = 28px, Header 3 = 20px, Button/Body = 14px).
* JANGAN: Membuat halaman autentikasi full-width tanpa panel ilustrasi — selalu gunakan split-panel.
* JANGAN: Mengabaikan validasi visual real-time pada form password (requirement checklist harus berubah warna dinamis via JavaScript).
