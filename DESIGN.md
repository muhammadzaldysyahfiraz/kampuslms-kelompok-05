---
name: KampusLMS
description: Sistem Pembelajaran Terpadu Perguruan Tinggi — Swiss Academic Index (Restrained Minimalism)
---

# Design System: KampusLMS

## Overview

**Creative North Star: "The Swiss Academic Index"**

KampusLMS memadukan ketenangan minimalisme monokrom dengan ketertiban struktural ala katalog silabus akademik Swiss (*exposed hairline ledger*, penomoran modul `01 · 02 · 03` dalam `JetBrains Mono`, dan kontras permukaan *Two-Tone*). Sistem ini menerapkan prinsip **"90% Quiet Slate & Ink, 10% Warm Signal"**: menolak dekorasi klise AI seperti garis warna-warni di tepi kartu (*left border stripe*) maupun tombol warna-warni mencolok yang merusak fokus belajar.

Setiap permukaan kerja dirancang untuk memberikan orientasi instan (*one-glance wayfinding*): metrik disajikan dalam kisi *ledger* bergaris rambut (`1px solid #E2E8F0`), kartu mata kuliah memisahkan isi utama (`#FFFFFF`) dari bar metadata bawah (`#F8FAFC`), dan urutan materi serta tugas dipandu oleh indeks silabus bernomor (`01`, `02`, `03`).

**Key Characteristics:**
- **Two-Tone Swiss Index Cards (Zero Left-Border Stripes)**: Kartu konten menggunakan kontras permukaan murni (`#FFFFFF` body di atas `#F8FAFC` footer) yang dipisahkan oleh garis rambut `1px solid #E2E8F0`—tanpa *left border spine* warna-warni.
- **Tactile Command Ink & Coral Micro-Signal**: Tombol aksi utama tetap menggunakan **Command Ink (`#111111`)** dengan *inner top-bevel* halus (`inset 0 1px 0 rgba(255,255,255,0.16)`), sedangkan **Radiant Coral (`#FE774C`)** digunakan secara sangat hemat sebagai sinyal mikro (garis aktif `2px` di Top Navbar, titik aksen logo, dan sorotan panah/indeks saat *hover*).
- **Exposed KPI Ledger Grid**: Ringkasan metrik disajikan dalam sel kisi berbatas garis vertikal-horizontal (`divide-x` / `divide-y` `#E2E8F0`) dengan penomoran sudut `01`–`04` (`font-mono`) dan angka `tabular-nums` tegas.
- **Numbered Syllabus Track (`01`, `02`, `03`)**: Daftar materi pembelajaran dan penugasan pada halaman detail mata kuliah diawali indeks dua digit `JetBrains Mono` yang presisi.

## Colors

Palet menjaga 90% permukaan tetap tenang (putih, kertas `#F8FAFC`, dan tinta Slate/Ink), dengan aksen fungsional yang terkendali untuk status semantik dan penanda aktif.

### Primary
- **Command Ink** (`#111111` / hover `#252530` / border `#27272A`): Digunakan pada tombol CTA utama (`.btn-primary`), kotak logo brand, dan elemen kontras tinggi.
- **Radiant Coral Signal** (`#FE774C` / deep `#C84B24` / tint `#FFF3EE`): Digunakan secara hemat hanya sebagai indikator aktif `2px` di Top Navbar, titik aksen brand, dan sorotan mikro saat *hover*.

### Secondary
- **Academic Sky** (`#0284C7` / ink `#17537D` / tint `#E1F3FE`): Digunakan pada tautan unduhan materi, indikator fokus form, dan badge peran **Dosen Pengampu**.

### Tertiary
- **Emerald Mint** (`#245228` / tint `#EDF3EC`): Digunakan untuk status aktif/selesai, pengumpulan tepat waktu, dan badge peran **Mahasiswa**.
- **Warm Amber** (`#7E4B00` / tint `#FBF3DB`): Digunakan untuk penanda tenggat terdekat, status *draft*, dan indikator jumlah tugas.
- **Crimson Rose** (`#8C2220` / tint `#FCECEC`): Digunakan untuk peringatan tenggat terlewat, aksi destruktif, dan badge **Administrator**.

### Neutral
- **Paper Canvas** (`#F8FAFC`): Latar belakang utama halaman konten dan bagian *footer two-tone* pada kartu.
- **Crisp Surface White** (`#FFFFFF`): Permukaan utama kartu (`.lms-card`), tabel data, dan panel formulir.
- **Structural Hairline** (`#E2E8F0` / hover `#CBD5E1` / subtle `#F1F5F9`): Garis pembatas kisi *ledger*, tepi kartu, dan pemisah baris.
- **Slate Ink Hierarchy**: Teks utama `#0F172A` (Slate 900), teks isi `#334155` (Slate 700), dan teks metadata `#64748B` (Slate 500).

### Named Rules
**The Zero-Stripe Card Rule.** Dilarang menggunakan garis warna di sisi kiri/atas kartu (`border-left: 3px solid ...`). Pembeda struktur kartu wajib mengandalkan tipografi kode (`font-mono`), garis rambut `1px solid #E2E8F0`, dan pemisahan latar *Two-Tone* (`#FFFFFF` vs `#F8FAFC`).

**The 90/10 Signal Rule.** Warna Coral (`#FE774C`) tidak boleh membanjiri tombol atau latar kartu; gunakan hanya sebagai penanda fokus/aktif mikro sehingga ruang kerja tetap tenang.

## Typography

**Display & UI Font:** `Plus Jakarta Sans` (dengan fallback `Inter, system-ui, sans-serif`)
**Label & Data Mono Font:** `JetBrains Mono` (dengan `font-variant-numeric: tabular-nums`)

**Character:** Perpaduan antara keterbacaan humanis-geometris `Plus Jakarta Sans` untuk judul dan narasi akademik dengan ketajaman teknis `JetBrains Mono` pada seluruh kode mata kuliah (`SI2514024`), nomor indeks (`01`, `02`), NIM/NIP, SKS, skor nilai, dan batas waktu.

### Hierarchy
- **Display / Page Title** (`700–800`, `1.5rem–1.875rem` / `24px–30px`, `line-height: 1.25`, `letter-spacing: -0.02em`): Judul utama halaman dan tajuk detail mata kuliah.
- **Headline / KPI Metric** (`800` `JetBrains Mono`, `1.5rem` / `24px`, `line-height: 1.2`, `tabular-nums`): Angka metrik utama di dalam kisi *Swiss KPI Ledger*.
- **Title / Card & Section Header** (`700`, `0.875rem–1.125rem` / `14px–18px`, `line-height: 1.35`, `letter-spacing: -0.01em`): Judul kartu mata kuliah dan kepala modul seksi.
- **Body** (`400–500`, `0.75rem–0.875rem` / `12px–14px`, `line-height: 1.6`, `60–75ch`): Deskripsi silabus mata kuliah, instruksi pengerjaan tugas, dan umpan balik evaluasi dosen.
- **Label / Index & Metadata** (`600–700` `JetBrains Mono`, `0.6875rem–0.75rem` / `11px–12px`): Nomor indeks silabus (`01`, `02`), badge kode MK, SKS, dan penanda waktu.

### Named Rules
**The Tabular Index Rule.** Daftar materi pembelajaran, daftar tugas, dan kisi metrik KPI menggunakan indeks numerik dua digit (`01`, `02`, `03`) dalam `JetBrains Mono` (`tabular-nums`).

## Layout

- **Container & Grid**: Kontainer utama halaman menggunakan lebar maksimum `max-w-7xl` (`1280px`) dengan padding horizontal responsif (`px-4 sm:px-6 lg:px-8`).
- **Workspace Topology**:
  - **Dashboard (`resources/views/dashboard.blade.php`)**: Tata letak 2-panel asimetris pada layar lebar (`flex-1` area kerja utama ~70% + `xl:w-72` panel konteks jadwal mendatang ~30%).
  - **Course & Assignment Detail (`courses/show.blade.php`, `assignments/show.blade.php`)**: Grid 3 kolom (`lg:col-span-2` untuk alur materi/tugas + 1 kolom panel informasi kelas/peserta/metadata).
  - **Swiss KPI Ledger**: Di dalam kartu ringkasan Dashboard, metrik dibagi menjadi 4–5 kolom bersebelahan dengan garis pemisah penuh (`divide-x divide-slate-200`) sehingga membentuk sel-sel indeks yang rapi.

## Elevation & Depth

Sistem ini menggunakan pendekatan **Crisp Hairline + Tactile Bevel**. Permukaan kartu tenang di atas kanvas `#F8FAFC` dengan batas `1px solid #E2E8F0` dan bayangan mikro (`0 1px 2px rgba(0,0,0,0.02)`). Kedalaman hadir melalui kontras *Two-Tone Surface* (`#FFFFFF` di atas `#F8FAFC`), *inner top-highlight* pada tombol utama (`inset 0 1px 0 rgba(255,255,255,0.16)`), serta elevasi halus (`translateY(-1px)` dan `0 4px 12px rgba(0,0,0,0.04)`) saat kartu interaktif di-*hover*.

### Named Rules
**The Two-Tone Card Rule.** Kartu mata kuliah memisahkan area konten utama (`#FFFFFF`) dan bar metadata/aksi bawah (`#F8FAFC`) dengan garis rambut `1px solid #E2E8F0`.

## Shapes

- **Cards & Containers**: Sudut membulat presisi `0.875rem` (`14px` / `--radius-lg`) dengan `overflow-hidden` agar *Two-Tone Footer* menyatu rapi mengikuti lengkungan kartu.
- **Buttons & Form Controls**: Sudut `0.5rem` (`8px` / `--radius-sm`) untuk tombol, input pencarian, dan *dropdown*.
- **Index Badges & Code Pills**: Sudut tegas `0.375rem` (`6px` / `--radius-xs`) untuk pill kode mata kuliah dan indeks silabus.

## Components

### Buttons
- **Primary (`.btn-primary`)**: Latar Command Ink `#111111` (hover `#252530`), border `1px solid #111111`, *inner top-bevel* `inset 0 1px 0 rgba(255,255,255,0.16)`, radius `8px`, transisi `140ms cubic-bezier(0.23, 1, 0.32, 1)`, dan efek tekan `scale(0.97)`.
- **Secondary (`.btn-secondary`)**: Permukaan `#F8FAFC` dengan border `#E2E8F0` (hover `#F1F5F9` dan border `#CBD5E1`).

### Navigation (`components/layout.blade.php`)
- **Top Bar**: Sticky header `h-14` berlatar putih `#FFFFFF` dengan `border-b border-slate-200`.
- **Active State**: Tautan navigasi aktif menampilkan indikator garis bawah tegas `2px solid var(--ink, #2F3437)` dan teks `#0F172A` tebal.
- **Semantic Role Pill**: Indikator peran di sisi kanan header menyesuaikan warna semantik lembut sesuai `$activeRole`.

### Notion Aesthetic & Open Doodles Diorama System
- **Sole Illustration Source**: Open Doodles by Pablo Stanley (CC0 1.0 Universal).
- **Core Color Tokens**: Canvas `#FAF8F5`, Primary Ink `#2F3437`, Illustration Ink `#3F454A` (softened so CTA buttons always lead), Sage Accent `#8FA895`, Muted Blue `#94A8B8`.
- **Surface Geometry**: Exact 6px radius (`--radius-sm`) for inputs and buttons; 10px radius (`--radius-md` / `--radius-lg`) for cards.
- **Precision Grounding**: All diorama figures rest directly on the ground baseline (`--ground: 32px`) with a flat contact ellipse shadow (`radial-gradient`), eliminating float and blurry halos.
- **Organic Asymmetry & Canopy**: Delicate corner leaf canopies (`h-[110px]` left, `h-[95px]` right) with rhythmic falling leaves.
- **Multi-Scene Diorama Mapping**:
  - `auth`: `sitting-reading` + `reading-side` (Reading Lounge)
  - `dashboard`: `coffee` + `plant` (Morning Academic Routine)
  - `courses`: `sitting` + `strolling` (Academic Courtyard)
  - `assignments`: `running` + `unboxing` (Sprint & Submissions)
  - `materials`: `meditating` + `laying` (Deep Focus & Garden Archive)
  - `users`: `selfie` + `petting` (Campus Community)
  - `tentang`: `dancing` + `loving` (Team Spirit & Celebration)
  - `error`: Ambient ground with in-card heroes (`zombieing` 404, `clumsy` 403)

### Course Cards (`dashboard.blade.php` & `courses/index.blade.php`)
- **Structure**: *Two-Tone Card* (`#FFFFFF` body + `#F8FAFC` footer) tanpa garis warna di samping kiri.
- **Header & Body**: Badge kode MK (`font-mono` di atas pill netral tegas `#F1F5F9`), SKS di kanan atas, judul mata kuliah (`font-bold`), dan deskripsi ringkas.
- **Footer**: Latar `#F8FAFC` (`border-t border-slate-200/80`) berisi informasi dosen/statistik serta indikator panah `→` yang bergeser (`group-hover:translate-x-0.5 group-hover:text-[#FE774C]`).

### Numbered Syllabus Track (`courses/show.blade.php`)
- **Structure**: Setiap baris materi pembelajaran dan tugas diawali indeks `01`, `02`, `03` ber-font `JetBrains Mono` yang berubah warna menjadi aksen tegas saat baris di-*hover*.

## Do's and Don'ts

### Do:
- **Do** pisahkan bagian *body* (`#FFFFFF`) dan *footer* (`#F8FAFC`) pada kartu mata kuliah (*Two-Tone Card*).
- **Do** tampilkan nomor indeks dua digit (`01`, `02`, `03`) menggunakan `JetBrains Mono` pada daftar materi, daftar tugas, dan kisi KPI Dashboard.
- **Do** gunakan named routes Laravel (`route(...)`) serta `@csrf` dan `@method('DELETE')` / `@method('PUT')` untuk seluruh mutasi data.
- **Do** pertahankan fitur aksesibilitas WCAG 2.2 AA (`#main-content` skip link, `:focus-visible`, pintasan `⌘K` / `/`, dan `prefers-reduced-motion`).

### Don't:
- **Don't** menggunakan *left border stripe* (`border-left: 3px solid ...`) atau warna pelangi acak/hash pada kartu (pola klise *AI slop*).
- **Don't** menambahkan tautan, menu, atau elemen UI fiktif yang tidak ada di rute aplikasi nyata.
- **Don't** menggunakan tag `<a>` untuk aksi penghapusan atau perubahan data.
