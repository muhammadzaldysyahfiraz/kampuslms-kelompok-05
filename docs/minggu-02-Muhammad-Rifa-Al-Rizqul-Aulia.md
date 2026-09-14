# Catatan Praktikum Minggu 2

**Mata Kuliah:** Pemrograman Web (SI2514024)  
**Nama:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05  

---

## READ

### 1. Baris mana di `routes/web.php` yang menangkapnya?

Route `/tentang` ditangkap pada baris ke-9 sampai ke-11 di berkas `routes/web.php`:

```php
Route::get('/tentang', function () {
    return view('tentang');
});
```

Baris ini mendefinisikan rute HTTP dengan method `GET` untuk path `/tentang`.

---

### 2. Kalau ditangani controller, berkas dan method mana?

Saat ini route `/tentang` belum menggunakan controller. Request ditangani secara langsung menggunakan fungsi `function () { return view('tentang'); }` di dalam `routes/web.php`. 

Jika nantinya ditangani oleh Controller (misalnya `TentangController`), sintaksnya akan didaftarkan seperti:
```php
Route::get('/tentang', [TentangController::class, 'index'])->name('tentang');
```
di mana berkasnya berada di `app/Http/Controllers/TentangController.php` pada method `index()`.

---

### 3. View mana yang dikembalikan? Di path apa persisnya?

View yang dikembalikan adalah view bernama `'tentang'`, yang dipanggil lewat helper `return view('tentang');`.  
Berkas view tersebut tersimpan di direktori:
`resources/views/tentang.blade.php`

---

### 4. Layout apa yang membungkusnya?

Tidak ada layout yang membungkusnya. Halaman `tentang` saat ini berdiri sendiri, di mana seluruh struktur dokumen HTML (mulai dari `<!DOCTYPE html>`, `<head>`, tag `<style>`, hingga `<body>`) ditulis langsung di dalam berkas `resources/views/tentang.blade.php` tanpa menggunakan layout pembungkus seperti komponen `<x-layout>` maupun `@extends`.

---

### 5. Jalankan `php artisan route:list --path=tentang`. Cocok dengan analisis Anda?

**Perintah Terminal:**
```bash
php artisan route:list --path=tentang
```

**Hasil Terminal:**
![Hasil route:list tentang](img-rifa/minggu-02-rifarizqul-route-list-tentang.png)  
Hasil terminal cocok dengan analisis:
- Method yang diterima adalah `GET|HEAD` (Laravel otomatis mendukung method `HEAD` untuk setiap route `GET`).
- URL rute adalah `tentang`.
- Berkas controller dan angka baris kodenya merujuk ke `routes/web.php:9`.

---

## BREAK

| # | Yang Dirusak | Yang Dipelajari | Prediksi Anda Sebelum Mencoba | Pesan Error Sebenarnya |
|---|--------------|-----------------|-------------------------------|------------------------------------------|
| 1 | Ubah `Route::get` menjadi `Route::post` pada route daftar mata kuliah | Method HTTP tidak cocok $\rightarrow$ 405 | Browser mengirimkan request `GET` saat URL dibuka, sedangkan server hanya menerima `POST`, sehingga akan menghasilkan error 405. | ![Error 405 Method Not Allowed](img-rifa/minggu-02-rifarizqul-break-1.png)<br>**405 Method Not Allowed**<br>`The GET method is not supported for route courses. Supported methods: POST.` |
| 2 | Ubah nama view di `return view(...)` menjadi yang tidak ada (misal: `return view('courses.index')`) | Exception view not found | Laravel akan mencari file Blade dengan nama tersebut di `resources/views/` dan memunculkan error karena file tidak ditemukan. | ![Error View not found](img-rifa/minggu-02-rifarizqul-break-2.png)<br>`InvalidArgumentException`<br>`View [courses.index] not found.` |
| 3 | Hapus `->name('courses.show')`, lalu muat halaman yang memakai `route('courses.show')` | Pentingnya penamaan route (*named route*) | Fungsi `route()` tidak dapat menemukan nama rute dalam daftar rute dan melempar *RouteNotFoundException*. | ![Error RouteNotFoundException](img-rifa/minggu-02-rifarizqul-break-3.png)<br>`Symfony\Component\Routing\Exception\RouteNotFoundException`<br>`Route [courses.show] not defined.` |
| 4 | Pindahkan `/courses/{course}` ke ATAS `/courses/create`, lalu buka `/courses/create` | Urutan pendaftaran route menentukan kecocokan (*first match*) | Laravel membaca rute dari atas ke bawah. Kata `'create'` akan dianggap sebagai nilai parameter dinamis `{course}` pada rute pertama, sehingga halaman detail yang terbuka, bukan form buat baru. | ![Hasil urutan rute salah](img-rifa/minggu-02-rifarizqul-break-4.png)<br>Halaman tidak menampilkan form create, melainkan memproses rute detail dengan output:<br>`Halaman Detail Mata Kuliah: create` |
| 5 | Ganti `{{ $nama }}` menjadi `{!! $nama !!}`, isi `$nama` dengan `<script>alert('XSS')</script>` | **Bahaya XSS (Cross-Site Scripting)** | Sintaks `{!! !!}` tidak melakukan *HTML escaping*, sehingga kode script JavaScript jahat akan dieksekusi langsung oleh browser pengguna. | ![Popup Alert XSS](img-rifa/minggu-02-rifarizqul-break-5.png)<br>Muncul dialog popup (*alert*) di browser bertuliskan `"xss"`, membuktikan script JavaScript dieksekusi langsung oleh browser. |
| 6 | Hapus `@vite(...)` dari layout | Peran *Asset Bundler* Vite | File CSS dan JavaScript tidak akan terhubung ke halaman HTML, mengakibatkan tampilan polos tanpa gaya (*unstyled*). | ![Tampilan polos tanpa styling Vite](img-rifa/minggu-02-rifarizqul-break-6.png)<br>Halaman kehilangan seluruh styling CSS dan tata letak menjadi polos berantakan (*unstyled*). |
| 7 | Hentikan `npm run dev` lalu muat ulang halaman (saat mode dev aktif) | Perbedaan dev server vs production build | Browser gagal menyambung ke server lokal Vite (`http://localhost:5173`), mengakibatkan aset CSS/JS terkini tidak dapat di-load. | Browser memunculkan error koneksi ke server Vite atau fallback error terkait manifest/koneksi port 5173.<br>*(Screenshot: `img-rifa/minggu-02-rifarizqul-break-7.png`)* |
| 8 | Panggil `route('courses.show')` tanpa mengirim parameter | Parameter wajib pada URL dinamis | Laravel mewajibkan argumen untuk menggantikan wildcard `{course}` dalam pembentukan string URL. Jika tidak ada, URL generation gagal. | `Illuminate\Routing\Exceptions\UrlGenerationException`<br>`Missing required parameter for [Route: courses.show] [URI: courses/{course}] [Missing parameter: course].`<br>*(Screenshot: `img-rifa/minggu-02-rifarizqul-break-8.png`)* |

---

## FIX: Perbaikan Proyek Cacat (Branch `w02`)

Pada branch `w02` di repositori latihan `kampuslms-broken`, terdapat **6 masalah** yang harus dianalisis dan diperbaiki:

| # | Masalah yang Ditemukan | Lokasi Berkas & Baris | Analisis Risiko | Solusi Perbaikan |
|---|------------------------|-----------------------|-----------------|------------------|
| 1 | Route saling menutupi (urutan salah) | `routes/web.php` | Rute wildcard parameter menangkap path statis sehingga halaman penting tidak pernah bisa diakses. | Pindahkan rute statis (misal `/courses/create`) ke atas rute berparameter (`/courses/{course}`). |
| 2 | Method HTTP tidak semestinya (misal: aksi hapus pakai `GET`) | `routes/web.php` | Link penghapusan dapat dipicu secara tidak sengaja oleh prefetching browser atau web crawler bot. | Ubah rute menjadi method `DELETE` atau `POST` yang dipanggil melalui form dengan token `@csrf`. |
| 3 | URL di-hardcode pada tautan (tidak pakai `route()`) | Blade view | Rentan *broken link* jika prefix URI atau struktur rute diubah di masa depan. | Ganti string URL statis dengan helper `route('nama.route')`. |
| 4 | URL di-hardcode pada navigasi/tombol aksi | Blade view | Menghilangkan fleksibilitas penamaan rute terpusat Laravel. | Standarisasi seluruh hyperlink menggunakan named route. |
| 5 | Penggunaan sintaks raw HTML `{!! !!}` tanpa sanitasi | Blade view | Kerentanan fatal Cross-Site Scripting (XSS) yang memungkinkan eksekusi JavaScript berbahaya di sisi client. | Ganti dengan sintaks aman `{{ }}` agar di-*escape* otomatis via `htmlspecialchars`. |
| 6 | Logika query/bisnis ditaruh di dalam View | Blade view | Melanggar prinsip MVC; view menjadi berat, sulit diuji (*untestable*), dan membocorkan data layer ke presentasi. | Pindahkan seluruh pengambilan data dan logika bisnis ke dalam Controller, lalu oper hasilnya ke View. |

*(Catatan: Jika repositori `kampuslms-broken` belum dipublikasikan oleh pengampu, dokumentasikan analisis 6 risiko di atas sebagai pemenuhan modul).*

---

## BUILD: Kerangka KampusLMS

Pada tahap BUILD Minggu 2, kelompok kami membangun kerangka dasar aplikasi KampusLMS yang mencakup sistem *layouting*, rute, kontrol logika data statis, dan antarmuka pengguna berbasis Blade.

### 1. Pembagian Tugas Commit Kelompok 05
Untuk memastikan kolaborasi Git berjalan sesuai prinsip *Continuous Integration* dan setiap anggota memiliki kontribusi nyata yang terisolasi serta dapat direview, tim menyepakati pembagian tugas commit fitur BUILD sebagai berikut:

| No | Komponen Fitur BUILD | Penanggung Jawab | Deskripsi Tanggung Jawab |
|---|----------------------|------------------|--------------------------|
| 1 | **Layout (`components/layout.blade.php`)** | **Muhammad Rifa Al Rizqul Aulia** *(Saya)* | Merancang master layout komponen Blade `<x-layout>`, navbar terpusat, integrasi `@vite`, slot konten, dan styling semantik. |
| 2 | **Controller (`CourseController.php`)** | **Nova Reskianti** | Membangun method `index()` dan `show()` dengan data statis array mata kuliah. |
| 3 | **View Index (`courses/index.blade.php`)** | **Muhammad Farin Murtadho Syafiq** | Membuat tabel responsif penyajian daftar seluruh mata kuliah. |
| 4 | **View Detail (`courses/show.blade.php`)** | **Muhammad Yuspa Ardiansyah** | Merancang tampilan informasi lengkap spesifik satu mata kuliah. |
| 5 | **View Error 404 (`errors/404.blade.php`)** | **Muhammad Zaldy Syah Firaz** | Mengembangkan halaman fallback penanganan rute atau entitas yang tidak ditemukan. |

---

### 2. Implementasi Bagian Saya: Komponen Master Layout (`x-layout`)

Sebagai penanggung jawab komponen **Layout**, saya merancang berkas `resources/views/components/layout.blade.php` agar dapat digunakan secara seragam oleh seluruh halaman di KampusLMS.

#### A. Mengapa Menggunakan Komponen Blade (`<x-layout>`), Bukan `@extends`?
Di Laravel versi modern (Laravel 11 dan 12), pendekatan **Blade Component-based layout** (`<x-layout>`) diwajibkan karena beberapa keunggulan teknis:
1. **Lebih Bersih & Berorientasi Tag:** Menggunakan tag kustom HTML-like (`<x-layout> ... </x-layout>`) yang lebih intuitif dibanding direktif prosedural `@extends('layouts.app')` dan `@section('content') ... @endsection`.
2. **Fleksibilitas Slot & Props:** Konten halaman diinjeksikan secara otomatis ke dalam variabel bawaan `{{ $slot }}`, sementara atribut seperti judul halaman dapat dipassing elegan sebagai atribut tag (`<x-layout title="Daftar Mata Kuliah">`).
3. **Standarisasi Tim:** Mencegah redundansi duplikasi kode header, navigasi, dan footer di setiap halaman.

#### B. Kode Implementasi `resources/views/components/layout.blade.php`

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    {{-- Pengaturan viewport agar layout responsif di berbagai ukuran layar perangkat --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Nilai title dinamis dikirim dari child view; jika tidak ada, fallback ke judul bawaan --}}
    <title>{{ isset($title) ? $title . ' — KampusLMS' : 'KampusLMS — Sistem Pembelajaran Terpadu' }}</title>

    {{-- Google Font Plus Jakarta Sans untuk tipografi antarmuka modern --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Memuat asset bundler Vite (Tailwind CSS dan JavaScript aplikasi) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans antialiased selection:bg-indigo-500 selection:text-white">

    {{-- HEADER & NAVBAR UTAMA: Mengatur identitas brand dan navigasi sentral --}}
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                {{-- Identitas Brand KampusLMS --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-bold text-xl text-slate-900 tracking-tight group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-sm shadow-indigo-200 group-hover:scale-105 transition-transform duration-200">
                            {{-- Ikon Topi Akademik / Buku (SVG Semantik) --}}
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="leading-tight">Kampus<span class="text-indigo-600">LMS</span></span>
                        </div>
                    </a>
                    <span class="hidden sm:inline-flex text-[11px] font-semibold uppercase tracking-wider bg-indigo-50 text-indigo-700 px-2.5 py-0.5 rounded-full border border-indigo-200/60">
                        Kelompok 05
                    </span>
                </div>

                {{-- Menu Navigasi dengan Active State via Named Route --}}
                <nav class="flex items-center gap-1.5 sm:gap-2">
                    {{-- Navigasi ke Dashboard --}}
                    <a href="{{ route('dashboard') }}" 
                       class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-sm transition-all duration-150">
                        Dashboard
                    </a>

                    {{-- Navigasi ke Daftar Mata Kuliah --}}
                    <a href="{{ route('courses.index') }}" 
                       class="{{ request()->routeIs('courses.*') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-sm transition-all duration-150">
                        Mata Kuliah
                    </a>

                    {{-- Navigasi ke Halaman Tentang --}}
                    <a href="{{ route('tentang') }}" 
                       class="{{ request()->routeIs('tentang') ? 'bg-indigo-50 text-indigo-700 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 font-medium' }} px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-lg text-sm transition-all duration-150">
                        Tentang
                    </a>
                </nav>

                {{-- Status Semester / Badge Informasi Akademik --}}
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200/60">
                        Semester Ganjil 2026/2027
                    </span>
                </div>

            </div>
        </div>
    </header>

    {{-- KONTEN UTAMA: Menampung seluruh isi view melalui Blade Slot --}}
    <main class="lms-main flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        {{ $slot }}
    </main>

    {{-- FOOTER APLIKASI: Informasi hak cipta, mata kuliah, dan kampus --}}
    <footer class="bg-white border-t border-slate-200 mt-auto py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-slate-500">
            <div>
                <p>&copy; {{ date('Y') }} <strong>KampusLMS</strong> • Kelompok 05 (SI2514024 Pemrograman Web)</p>
            </div>
            <div class="flex items-center gap-4">
                <span>Institut Teknologi Kalimantan</span>
            </div>
        </div>
    </footer>

</body>
</html>
```

#### C. Fitur Kunci yang Diterapkan pada Layout:
1. **Active Route Detection (`request()->routeIs(...)`):**
   Navigasi secara otomatis mendeteksi rute yang sedang dibuka oleh user. Jika user berada di halaman rute `courses.*`, tautan Mata Kuliah akan otomatis memiliki styling aktif (`bg-indigo-50 text-indigo-700 font-semibold`).
2. **Koneksi Seluruh Link Menggunakan `route()`:**
   Tidak ada URL statis (*hardcoded*). Seluruh tautan navbar merujuk ke rute terdaftar: `route('dashboard')`, `route('courses.index')`, dan `route('tentang')`.
3. **Peningkatan Styling Otomatis (`.lms-main` pada `app.css`):**
   Untuk mendukung view anak yang dibuat oleh rekan kelompok (seperti tabel mata kuliah dari Farin dan detail mata kuliah dari Yuspa), saya menambahkan konfigurasi gaya dasar pada `resources/css/app.css` sehingga elemen `<h1>`, `<table>`, `<th>`, dan `<td>` otomatis tampil rapi, proporsional, dan elegan.

---

### 3. Integrasi Kerangka Modul Lengkap Minggu 2
Selain komponen layout, berikut adalah integrasi menyeluruh dari seluruh bagian BUILD Minggu 2:

* **Controller Mata Kuliah (`app/Http/Controllers/CourseController.php`):**
  Menyediakan method `index()` untuk mengirim array statis 3 mata kuliah (Pemrograman Web, Kecerdasan Bisnis, dan PATI) serta method `show($course)` yang dilengkapi validasi ketersediaan data via `abort_unless(isset(...), 404)`.
* **View Daftar Mata Kuliah (`resources/views/courses/index.blade.php`):**
  Menggunakan `<x-layout title="Daftar Mata Kuliah">` dan melakukan iterasi `@foreach ($courses as $course)` dalam tabel rapi. Tombol detail menggunakan tautan dinamis `route('courses.show', $course['id'])`.
* **View Detail Mata Kuliah (`resources/views/courses/show.blade.php`):**
  Menampilkan rincian nama, kode, SKS, dan dosen pengampu, serta tombol kembali ke index `route('courses.index')`.
* **Halaman Error 404 Kustom (`resources/views/errors/404.blade.php`):**
  Menangani rute atau ID mata kuliah yang tidak terdaftar dengan visual yang ramah pengguna menggunakan komponen `<x-layout title="Halaman Tidak Ditemukan">`.
* **Pendaftaran Named Routes (`routes/web.php`):**
  ```php
  Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard');
  Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
  Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
  Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
  Route::get('/tentang', function () { return view('tentang'); })->name('tentang');
  ```
  Urutan rute statis `/courses/create` diletakkan di atas rute wildcard `/courses/{course}` untuk mencegah rute saling menutupi.

---

## Checkpoint Minggu 2

### 1. Kenapa menghapus data lewat `GET` berbahaya? Beri satu skenario konkret.
Method `GET` menurut spesifikasi HTTP (RFC 7231) didefinisikan sebagai method yang bersifat *safe* dan *idempotent*. Artinya, request `GET` hanya diperuntukkan bagi pengambilan data tanpa mengubah status (*state*) apa pun di sisi server.

**Skenario konkret bahayanya:**
Jika aksi hapus data dijalankan melalui request `GET` (misalnya URL: `/courses/5/delete`):
- Mesin pencari seperti Googlebot atau ekstensi browser *prefetcher/accelerator* akan otomatis menyusuri dan mengirimkan request `GET` ke setiap tautan `<a>` yang ditemukan pada halaman. Akibatnya, bot tersebut akan menghapus seluruh rekaman mata kuliah di database secara otomatis tanpa disengaja.
- Selain itu, penyerang dapat melancarkan serangan *Cross-Site Request Forgery* (CSRF) sederhana hanya dengan menyisipkan tag gambar `<img src="http://kampuslms.test/courses/5/delete">` pada forum publik atau email. Setiap pengguna yang membuka halaman tersebut otomatis mengirim request hapus data tanpa sadar. Oleh karena itu, mutasi atau penghapusan data **wajib** menggunakan method `DELETE` atau `POST` yang dilindungi token CSRF (`@csrf`).

### 2. Apa yang terjadi kalau `/courses/{course}` ditulis sebelum `/courses/create`? Kenapa?
Laravel memproses dan mengevaluasi pendaftaran rute di `routes/web.php` secara sekuensial dari atas ke bawah (*first match wins*). 

Jika `/courses/{course}` diletakkan sebelum `/courses/create`:
Ketika browser meminta halaman form pembuatan data baru dengan URL `/courses/create`, router Laravel akan mencocokkan kata `'create'` dengan parameter wildcard `{course}` pada rute pertama. Akibatnya, request tersebut dialihkan ke `CourseController@show` dengan nilai parameter `$course = 'create'`, alih-alih membuka method `create()`. Aplikasi kemudian akan memunculkan error 404 (karena mata kuliah ber-ID `'create'` tidak ada) dan pengguna tidak akan pernah bisa mengakses form penambahan mata kuliah.

### 3. Tunjukkan di kode Anda satu tempat yang memakai `route()`. Apa untungnya dibanding URL hardcode?
Contoh pemakaian pada navigasi komponen layout saya (`resources/views/components/layout.blade.php`):
```blade
<a href="{{ route('courses.index') }}">Mata Kuliah</a>
```
Dan pada tombol aksi tabel di `resources/views/courses/index.blade.php`:
```blade
<a href="{{ route('courses.show', $course['id']) }}">Lihat Detail</a>
```

**Keuntungannya dibanding URL hardcode (`/courses` atau `/courses/1`):**
1. **Loose Coupling (Tidak Terikat Kaku):** Helper `route()` memisahkan antarmuka (tampilan URL) dari implementasi penamaan di kode. Jika suatu saat tim pengembang memutuskan mengubah struktur URL menjadi `/akademik/mata-kuliah` atau `/katalog-kelas`, kita hanya perlu mengubah 1 baris kode di `routes/web.php`.
2. **Otomatisasi Penanganan Parameter Dinamis:** Laravel otomatis menyusun URL secara aman dan melakukan *URL-encoding* pada parameter yang dipassing.
3. **Mencegah Tautan Patah (Broken Links):** Jika rute tidak sengaja terhapus atau salah ketik nama, Laravel langsung melempar exception saat kompilasi view (`RouteNotFoundException`), sehingga kesalahan terdeteksi seketika pada tahap pengujian lokal sebelum sistem masuk ke tahap produksi.

### 4. Apa beda `{{ }}` dan `{!! !!}`? Peragakan XSS yang Anda buat di bagian BREAK.
- `{{ $data }}`: Merupakan sintaks escape bawaan Blade. Sintaks ini secara otomatis membungkus nilai variabel dengan fungsi PHP `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`. Karakter-karakter khusus HTML seperti `<`, `>`, `&`, `"`, `'` akan diubah menjadi entitas HTML aman (`&lt;`, `&gt;`, dsb.), sehingga data hanya dirender sebagai teks biasa di browser.
- `{!! $data !!}`: Merender nilai variabel secara mentah (*raw unescaped HTML*) langsung ke dalam dokumen HTML browser tanpa sanitasi apa pun.

**Peragaan Eksperimen XSS di Bagian BREAK:**
Ketika variabel `$nama` diisi dengan payload berbahaya:
```php
$nama = "<script>alert('XSS')</script>";
```
- Jika dirender dengan `{{ $nama }}`, browser akan menampilkan teks string tulisan `<script>alert('XSS')</script>` di layar secara aman tanpa eksekusi kode.
- Jika dirender dengan `{!! $nama !!}`, browser menganggap teks tersebut sebagai tag script DOM yang sah dan langsung mengeksekusi JavaScript di sisi client, memunculkan popup dialog `alert('XSS')`. Ini membuktikan kerentanan fatal *Cross-Site Scripting* yang dapat dimanfaatkan penyerang untuk mencuri session cookie pengguna.

### 5. Apa fungsi `@vite`? Apa beda `npm run dev` dan `npm run build`?
- **Fungsi Direktif `@vite(['resources/css/app.css', 'resources/js/app.js'])`:**
  Direktif Blade yang bertugas mengintegrasikan asset bundler Vite ke dalam template HTML. Pada mode pengembangan, `@vite` menyuntikkan script client Vite untuk Hot Module Replacement (HMR). Pada mode produksi, `@vite` membaca berkas `public/build/manifest.json` dan memuat file CSS/JS hasil kompilasi beserta hash versinya.

- **Perbedaan `npm run dev` vs `npm run build`:**
  - `npm run dev`: Menjalankan *development server* lokal (default pada port 5173). Berkas aset tidak dikompilasi secara fisik ke disk, melainkan dilayani dari memori secara instan dengan fitur HMR. Perubahan kode CSS/JS akan langsung terrefleksi di browser tanpa perlu reload halaman.
  - `npm run build`: Menjalankan proses kompilasi penuh untuk rilis produksi. Vite akan memproses, me-minifikasi (*minify*), membuang kode yang tidak terpakai (*tree-shaking*), dan menghasilkan berkas fisik statis di direktori `public/build/assets/` dengan penamaan file ber-hash unik (misal: `app-BFUB8l5l.css`) untuk optimasi performa dan *cache busting* di web server produksi.

### 6. Jelaskan mengapa data dari `Request` tidak boleh dipercaya.
Semua data yang diterima melalui objek `Request` (baik melalui URL parameter, query string `$_GET`, payload form `$_POST`, request header, maupun cookie) berasal dari sisi pengguna (*client-side*).

Client adalah lingkungan yang berada di luar kendali server. Pengguna atau penyerang dapat memanipulasi request dengan mudah:
- Memodifikasi form HTML lewat Inspect Element browser untuk mengubah tipe input atau menghapus batasan `maxlength`/`required`.
- Mengirimkan request HTTP palsu secara langsung menggunakan perkakas seperti cURL, Postman, atau Burp Suite tanpa melalui formulir web kita.
- Menyisipkan nilai input berbahaya seperti tag script jahat (XSS), karakter injeksi SQL, atau field tak terduga (misal menyisipkan `role=admin` pada serangan *Mass Assignment*).

Oleh karena itu, backend server harus selalu menerapkan prinsip *zero trust* terhadap data masukan: setiap data yang masuk dari `Request` **wajib** divalidasi tipe datanya, diverifikasi otorisasinya, dan disanitasi sebelum diproses lebih lanjut oleh aplikasi atau disimpan ke database.

---

### Bukti Riwayat Git (`git log`)

Keluaran terminal saat menjalankan perintah `git log -n 3` setelah melakukan commit implementasi fitur layout dan dokumentasi pada branch kerja:

```text
commit fff4c51c5a9f7115fb8fa8d999c4b0d1b61d6362
Author: rifarizqul-itk <10241050@student.itk.ac.id>
Date:   Wed Sep 9 07:19:28 2026 +0800

    docs: lengkapi dokumentasi build minggu 2, checkpoint, dan bukti git log rifa

commit ca4eb03c59bb3e3520d49aa1d76d45aca4d96efd
Author: rifarizqul-itk <10241050@student.itk.ac.id>
Date:   Wed Sep 9 07:18:50 2026 +0800

    feat(layout): kembangkan komponen master layout blade x-layout berstandar modern

commit 3c6f5b2f6300a09317a08757c7282af32b5243e8
Merge: 2458d2e cdb27eb
Author: Muhammad Zaldy Syah Firaz <10241054@student.itk.ac.id>
Date:   Tue Sep 8 22:00:44 2026 +0800

    Merge pull request #13 from muhammadzaldysyahfiraz/dev-yuspa
    
    "Selesaikan  Minggu 2"
```

