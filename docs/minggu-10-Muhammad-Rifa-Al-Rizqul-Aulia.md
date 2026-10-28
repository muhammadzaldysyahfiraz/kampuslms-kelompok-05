# Catatan Praktikum Minggu 10 Pemrograman Web

---

**Nama Mahasiswa:** Muhammad Rifa Al Rizqul Aulia  
**NIM:** 10241050  
**Kelompok:** 05   

---

# READ: Arsitektur Pemrosesan Asinkron, Event-Driven Architecture, Queue, dan Notifikasi Terpadu di Laravel 12

Pada Minggu ke-10 ini, pengembangan KampusLMS berfokus pada transisi dari sistem monolitik sinkron (*request-response blocking*) menuju **Pemrosesan Asinkron Berbasis Antrean (*Queue-driven Asynchronous Processing*)**, **Event-Driven Architecture (EDA)**, **Pengiriman Notifikasi Multikanal Database**, serta **Antarmuka Realtime Polling yang Selaras dengan Swiss Academic Design System**.

---

### 1. Masalah Performa: Latensi Proses I/O Menyandera Pengguna (*Request Blocking*)

Pada aplikasi web akademik berskala institusi, sebuah aksi dosen dapat memicu efek domino yang masif. Contoh klasik:
* Seorang dosen mempublikasikan tugas baru pada mata kuliah yang diikuti oleh **40 hingga 100 mahasiswa**.
* Jika pengiriman notifikasi dijalankan secara sinkron di dalam siklus HTTP request dosen, server harus mengeksekusi 40–100 kali operasi `INSERT` ke database (atau puluhan koneksi socket/SMTP jika menggunakan email).
* **Akibatnya:** Browser dosen akan berputar (*loading*) selama beberapa detik hingga seluruh notifikasi selesai diproses. Jika terjadi gangguan jaringan, request dosen bisa mengalami **HTTP 504 Gateway Timeout**, padahal penugasan itu sendiri sebenarnya sudah berhasil tersimpan di database.

```
ALUR SINKRON (BURUK):
Dosen Klik Simpan ──> Simpan Tugas ──> Loop 40x Buat Notif (Browser Freeze) ──> Redirect Dosen (Lambat)

ALUR ASINKRON DENGAN EVENT & QUEUE (OPTIMAL):
Dosen Klik Simpan ──> Simpan Tugas ──> Dispatch Event ──> Catat ke Tabel jobs (< 50ms) ──> Redirect Dosen (Instan!)
                                                                 │
                                                    Worker di Latar Belakang
                                                                 │
                                                                 ▼
                                                Eksekusi SendAssignmentNotification
                                                                 │
                                                                 ▼
                                            Insert 40 Baris ke Tabel notifications
```

---

### 2. Anatomi Event, Listener, dan Kontrak `ShouldQueue`

Laravel memfasilitasi pemisahan tanggung jawab (*Decoupling*) melalui pola Event-Listener:

1. **Event (`app/Events/AssignmentPublished.php` & `GradeReleased.php`):**  
   Hanya bertindak sebagai pembawa data (*data transport*) murni. Event tidak memproses logika pengiriman apa pun, melainkan hanya menyimpan objek model (`$assignment` atau `$grade`).
   ```php
   class AssignmentPublished
   {
       use Dispatchable, InteractsWithSockets, SerializesModels;

       public function __construct(public Assignment $assignment) {}
   }
   ```
2. **Listener (`app/Listeners/SendAssignmentNotification.php` & `SendGradeNotification.php`):**  
   Mengeksekusi logika bisnis saat event terjadi. Kunci dari pemrosesan asinkron terletak pada deklarasi antarmuka **`implements ShouldQueue`**:
   ```php
   class SendAssignmentNotification implements ShouldQueue
   {
       use InteractsWithQueue;

       public int $tries = 3;
       public int $backoff = 10;

       public function handle(AssignmentPublished $event): void
       {
           $assignment = $event->assignment;
           $assignment->loadMissing('course');

           $students = $assignment->course->students()
               ->where('role', 'mahasiswa')
               ->get();

           Notification::send($students, new NewAssignmentNotification($assignment));
       }
   }
   ```
   * **Peran `ShouldQueue`:** Ketika Laravel mendeteksi kontrak ini, alih-alih menjalankan method `handle()` secara langsung di thread web server, Laravel akan menyerialisasi objek event dan memasukkannya sebagai pekerjaan (*job*) baru ke antrean database.

---

### 3. Perbedaan Driver Antrean: `sync` vs `database`

Konfigurasi antrean diatur melalui file `.env` pada variabel `QUEUE_CONNECTION`:

| Parameter | `QUEUE_CONNECTION=sync` | `QUEUE_CONNECTION=database` |
|---|---|---|
| **Mekanisme Eksekusi** | Mengeksekusi listener secara langsung pada memori request HTTP yang sama. | Menyimpan pekerjaan ke tabel `jobs`, membebaskan request HTTP, dan dieksekusi oleh daemon worker terpisah. |
| **Dampak Peramban Pengguna** | **Blocking:** Browser menunggu hingga seluruh pekerjaan listener selesai. | **Non-blocking:** Browser langsung menerima respons instan (< 100 ms). |
| **Kebutuhan Worker Latar Belakang** | Tidak membutuhkan proses worker. | Wajib menjalankan daemon `php artisan queue:work`. |
| **Lingkungan Penggunaan** | Unit testing otomatis di memori (`phpunit`/`pest`) dan debugging lokal sederhana. | Staging dan Produksi nyata (mengawal kestabilan server dari lonjakan beban). |

---

### 4. Daur Hidup Antrean (*Queue Lifecycle*) & Ketahanan Sistem

Pada driver `database`, tabel basis data memainkan peran penting dalam memastikan keandalan:
1. **Tabel `jobs`:**  
   Menyimpan antrean yang belum atau sedang diproses. Memiliki kolom `payload` (data biner serialisasi), `attempts` (jumlah percobaan eksekusi), dan `reserved_at`.
2. **Penanganan Kegagalan Otomatis (*Retries & Backoff*):**  
   Properti `$tries = 3` dan `$backoff = 10` memastikan jika terjadi kegagalan jaringan atau deadlock basis data sesaat, worker tidak langsung membuang pekerjaan tersebut, melainkan mencoba ulang hingga 3 kali dengan jeda 10 detik.
3. **Tabel `failed_jobs` & Logging:**  
   Jika batas percobaan habis, pekerjaan dipindahkan secara permanen ke tabel `failed_jobs`, dan method callback `failed()` dieksekusi untuk mencatat log error pada sistem audit:
   ```php
   public function failed(AssignmentPublished $event, Throwable $exception): void
   {
       Log::error('Notifikasi tugas baru gagal dikirim.', [
           'assignment_id' => $event->assignment->id,
           'exception' => $exception->getMessage(),
       ]);
   }
   ```

---

### 5. Keamanan & Privasi Data: Prinsip Minimisasi Payload Notifikasi

Salah satu prinsip keamanan arsitektural yang paling penting pada notifikasi akademik adalah **Minimisasi Data (*Data Minimization*)**:

```php
// app/Notifications/GradeReleasedNotification.php
public function toDatabase(object $notifiable): array
{
    $this->grade->loadMissing('submission.assignment');
    $assignment = $this->grade->submission->assignment;

    return [
        'grade_id' => $this->grade->id,
        'assignment_id' => $assignment->id,
        'assignment_title' => $assignment->title,
    ];
}
```

* **Mengapa Nilai Mahasiswa DILARANG Masuk Payload Notifikasi?**  
  Tabel `notifications` menyimpan kolom `data` dalam bentuk teks mentah JSON yang sering terekspos pada log server, websocket broadcast, integrasi webhook pihak ketiga, atau intipan layar (*shoulder surfing* pada preview lonceng).  
  Nilai akademik (`score`) dan umpan balik dosen (`feedback`) bersifat rahasia dan berstatus privat. Oleh karena itu, notifikasi **hanya boleh membawa referensi ID dan metadata umum**. Skor nilai resmi harus selalu diakses melalui endpoint yang dilindungi oleh otorisasi `GradePolicy` dan `SubmissionPolicy`.

---

### 6. Mekanisme Notifikasi Antarmuka: Polling Berkala vs WebSockets

Untuk menyajikan status notifikasi yang responsif di sisi klien, terdapat dua pendekatan arsitektur:
1. **Full WebSockets (Pusher / Laravel Echo / Reverb):**  
   Menyediakan koneksi dupleks penuh (*real-time push*). Sangat baik untuk aplikasi obrolan (*chatting*), namun membutuhkan konfigurasi server socket terpisah, konsumsi port daemon tambahan, dan pengelolaan dependensi yang rumit.
2. **Vanilla JS Polling Berkala (Pilihan Terukur KampusLMS):**  
   Peramban melakukan *request* ringan secara berkala (setiap 30 detik) ke endpoint JSON internal `/notifications`.  
   * **Keunggulan:** Ringan, 100% menggunakan infrastruktur HTTP standar tanpa biaya server tambahan, bebas konfigurasi API key pihak ketiga, dan sangat hemat sumber daya untuk skenario LMS universitas.

---

# BREAK: Delapan Uji Kerusakan Terencana (*Deliberate Failure Testing*)

Sesuai metodologi *Break-Fix-Build*, dilakukan 8 skenario pengujian kegagalan terencana untuk membuktikan keandalan sistem antrean dan keamanan notifikasi KampusLMS:

| # | Skenario Kerusakan Terencana | Prediksi Sebelum Menguji | Hasil & Dampak Sebenarnya |
|:--:|:---|:---|:---|
| **1** | Mematikan queue worker (`queue:work` off), lalu dosen menerbitkan tugas baru di antarmuka web. | Request dosen tetap berhasil, data tugas tidak hilang, antrean tersimpan aman di tabel database `jobs`. | **Sistem Antrean Bekerja Sempurna:** Web server langsung merespons sukses (< 80 ms). Di HeidiSQL, tabel `jobs` bertambah 1 baris pending. Mahasiswa belum menerima notifikasi di lonceng sampai worker diaktifkan kembali. Terbukti tidak ada request blocking. |
| **2** | Menghapus deklarasi `implements ShouldQueue` pada file `SendAssignmentNotification`. | Pengiriman notifikasi kembali berjalan secara sinkron di dalam siklus HTTP request dosen. | **Latensi Ekstrem Muncul:** Saat dosen menyimpan tugas di kelas berisi banyak mahasiswa, browser dosen mengalami *freezing* beberapa detik karena harus menunggu 40 kali eksekusi `INSERT` ke tabel `notifications`. |
| **3** | Mengubah konfigurasi `.env` menjadi `QUEUE_CONNECTION=sync` lalu menerbitkan tugas saat worker mati. | Notifikasi langsung masuk ke mahasiswa tanpa melewati antrean tabel `jobs`. | **Bypass Worker:** Driver `sync` langsung mengeksekusi listener seketika di thread request HTTP. Tabel `jobs` tetap kosong (0 baris). Sangat membuktikan bahwa `sync` meniadakan fungsi antrean asinkron. |
| **4** | Menyusupkan skor nilai angka (`score => 95`) dan feedback dosen ke dalam payload JSON `toDatabase()` pada `GradeReleasedNotification`. | Nilai mahasiswa terekspos di tabel `notifications` dan respon API publik. | **Pelanggaran Privasi Akademik:** Nilai rahasia mahasiswa tersimpan dalam teks mentah JSON di database. Melanggar prinsip minimisasi data dan menyebabkan potensi kebocoran nilai saat notifikasi di-query via API ringkas. |
| **5** | Mahasiswa A mencoba menandai notifikasi milik Mahasiswa B sebagai sudah dibaca melalui `PATCH /notifications/{uuid}/read`. | Sistem harus menolak aksi tersebut dan melindungi integritas kepemilikan notifikasi. | **Penyamaran IDOR Berhasil:** Controller memanggil `ensureOwner()` yang melakukan scoping kepemilikan dan melempar status **`404 Not Found`** (bukan 403). Penyerang tidak mengetahui apakah ID notifikasi tersebut valid milik orang lain atau tidak. |
| **6** | Mensimulasikan kegagalan fatal pada listener (melempar `Exception` buatan) saat worker memproses antrean. | Worker mencoba ulang sesuai batas `$tries`, lalu mencatat ke `failed_jobs`. | **Ketahanan Kegagalan Terverifikasi:** Worker melakukan *retry* sebanyak 3 kali dengan jeda 10 detik (`$backoff = 10`). Setelah batas habis, baris pekerjaan dipindahkan ke tabel `failed_jobs` dan tercatat di file `storage/logs/laravel.log`. |
| **7** | Menghapus eager loading `$assignment->loadMissing('course')` pada listener pengiriman notifikasi. | Terjadi masalah performa *N+1 Query* saat mengambil data relasi mahasiswa. | **Ledakan Query Terjadi:** Saat sistem mengevaluasi mahasiswa untuk puluhan tugas, database mengeksekusi query relasi berulang-ulang untuk tiap iterasi. Penggunaan `loadMissing` terbukti mutlak diperlukan. |
| **8** | Mengakses endpoint `/notifications` tanpa header `Accept: application/json` dari script JavaScript polling. | Endpoint mengembalikan halaman penuh HTML alih-alih data JSON ringkas. | **Beban Trafik Tinggi:** Skrip frontend membebani parser peramban karena menerima HTML lengkap 50 KB. Controller wajib memisahkan respons menggunakan pengecekan `$request->wantsJson() \|\| $request->ajax()`. |

---

# FIX: Rekayasa Arsitektur & Perbaikan Keamanan Sistem

Berdasarkan hasil pengujian di atas, dilakukan serangkaian rekayasa dan penguatan arsitektur:

### 1. Pendelegasian Penuh Siklus Asinkron (*Decoupled Queue Flow*)
* Menetapkan kontrak `ShouldQueue` dan trait `InteractsWithQueue` pada seluruh listener notifikasi.
* Mengatur batas percobaan `$tries = 3` dan penundaan `$backoff = 10` untuk mengatasi kegagalan transien pada server basis data.
* Menerapkan pencatatan log pada method `failed()` untuk kemudahan audit tim operasional.

### 2. Pencegahan IDOR dengan Penyamaran 404 (*Disguise 404 Pattern*)
Pada `NotificationController.php`, seluruh aksi yang memodifikasi atau membaca satu notifikasi (`show`, `read`, `destroy`) diproteksi oleh method guard:

```php
private function ensureOwner(Request $request, DatabaseNotification $notification): void
{
    $user = $request->user();
    abort_unless($user, 401);
    
    // 404 menyamarkan keberadaan notifikasi milik pengguna lain (IDOR Prevention)
    abort_unless(
        $user->notifications()->whereKey($notification->getKey())->exists(),
        404
    );
}
```
*Dengan mengembalikan 404 Not Found (bukan 403 Forbidden), sistem tidak membocorkan informasi keberadaan entitas (*Resource Enumeration Leak*).*

### 3. Sinkronisasi Status Tugas & Penegakan Integritas Antrean
Sebelumnya, form penugasan di antarmuka belum menyediakan input status sehingga tugas selalu tersimpan sebagai `draft`. Controller memiliki aturan bahwa event `AssignmentPublished` hanya di-dispatch jika `$assignment->status === 'published'`.

Dilakukan perbaikan:
* Menambahkan pilihan status publikasi pada formulir pembuatan dan pengeditan tugas.
* Menambahkan aksi instan *"Terbitkan Tugas"* pada halaman penugasan draf sehingga dosen dapat mempublikasikan tugas dan memicu antrean notifikasi hanya dengan satu klik.

### 4. Eliminasi Kode Duplikat Flash Alert
Membersihkan pemanggilan lokal `@if (session('success'))` dan `@if (session('error'))` pada file Blade tampilan detail yang sebelumnya menyebabkan alert muncul ganda di antarmuka. Seluruh notifikasi pesan kini terpusat pada komponen layout.blade.php.

---

# BUILD: Antarmuka Notifikasi Realtime & Desain Swiss Academic

Sebagai bagian dari peran Frontend Developer, berikut adalah komponen antarmuka dan interaktivitas yang dibangun pada Minggu 10:

### 1. Komponen Lonceng Notifikasi di Header Navigasi (`components/layout.blade.php`)

* **Ikon Lonceng Interaktif & Badge Counter Unread:**
  * Menampilkan ikon lonceng SVG dengan indikator badge angka notifikasi yang belum dibaca.
  * Menggunakan tipografi tabular-nums `JetBrains Mono` dengan warna aksen oranye terang khas Swiss Academic (`#FE774C`) yang kontras dan mudah dilihat:
    ```blade
    <span id="notif-badge" class="{{ $navUnreadCount > 0 ? 'inline-flex' : 'hidden' }} absolute -top-0.5 -right-0.5 items-center justify-center min-w-4 h-4 px-1 rounded-full text-[10px] font-bold font-mono bg-[#FE774C] text-white ring-2 ring-white">
        {{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}
    </span>
    ```
* **Dropdown Popover Ringkas (Maksimal 5 Notifikasi Terkini):**
  * Menampilkan pratinjau 5 notifikasi terbaru saat lonceng diklik tanpa perlu berpindah halaman.
  * Dilengkapi tombol cepat *"Tandai semua dibaca"* yang langsung memperbarui status seluruh notifikasi.
  * Tautan di bagian bawah popover yang mengarahkan pengguna ke Pusat Pemberitahuan lengkap.
* **Aksesibilitas & Interaksi Peramban (WCAG 2.2 AA):**
  * Mengimplementasikan atribut ARIA (`aria-expanded`, `aria-controls`, `aria-label`).
  * Menutup dropdown secara otomatis ketika pengguna mengklik area luar dokumen (*click outside listener*) atau menekan tombol `Escape`.

---

### 2. Polling Asinkron Realtime Berbasis Vanilla JavaScript

Untuk memperbarui status lonceng secara otomatis tanpa me-reload halaman atau memasang server WebSocket pihak ketiga yang berat:

```javascript
@auth
// Polling status notifikasi berkala setiap 30 detik
setInterval(() => {
    fetch('{{ route('notifications.index') }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.ok ? res.json() : null)
    .then(data => {
        if (data && typeof data.unread_count !== 'undefined') {
            const badge = document.getElementById('notif-badge');
            const countText = document.getElementById('notif-count-text');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                    badge.classList.remove('hidden');
                    badge.classList.add('inline-flex');
                } else {
                    badge.classList.add('hidden');
                    badge.classList.remove('inline-flex');
                }
            }
            if (countText) {
                countText.textContent = `${data.unread_count} baru`;
            }
        }
    })
    .catch(() => {});
}, 30000);
@endauth
```

* **Efisiensi Trafik:** Skrip hanya meminta payload JSON kecil berukuran < 1 KB berisi angka `unread_count` dan array notifikasi terkini, sehingga tidak membebani memori peramban klien maupun kapasitas jaringan server.

---

### 3. Pusat Pemberitahuan Terpadu (`notifications/index.blade.php`)

Halaman utama untuk mengelola seluruh arsip notifikasi pengguna:

* **Sistem Tab & Filter Status:**
  * Tab *"Semua Pemberitahuan"* (menampilkan seluruh riwayat).
  * Tab *"Belum Dibaca"* (memfilter notifikasi aktif yang memerlukan tindak lanjut).
  * Indikator status polling aktif dengan animasi *pulsing dot* hijau di sisi kanan atas.
* **Desain Kartu Notifikasi Adaptif (*Adaptive State Card*):**
  * **Notifikasi Belum Dibaca:** Memiliki border samping oranye tegas (`border-l-4 border-l-[#FE774C]`), latar belakang bernuansa pastel hangat (`#FFFDFB`), dan lencana kecil *"Baru"*.
  * **Notifikasi Sudah Dibaca:** Latar belakang putih bersih dengan tipografi tenang.
  * **Ikonografi Berdasarkan Jenis Event:** Menggunakan ikon dokumen untuk penugasan tugas baru (*Assignment*), ikon medali untuk pengumuman nilai (*Grade*), dan ikon lonceng untuk pengumuman umum.
* **Penanganan Aksi Per Item:**
  * Mengklik notifikasi akan mengarahkan pengguna ke halaman target terkait (tugas atau detail nilai) sekaligus menandainya sebagai sudah dibaca secara otomatis.
  * Menyediakan tombol aksi individual: *"Tandai Dibaca"* dan tombol *"Hapus"* dengan metode `DELETE`.
* **Kondisi Kosong (*Empty State*) yang Informatif:**
  * Menampilkan ilustrasi lonceng yang elegan dengan pesan deskriptif ketika kotak masuk kosong, serta tombol pintas untuk melihat kembali riwayat notifikasi lama.

---

### 4. Formulir Penugasan & Tombol Cepat Terbitkan Tugas

* **Dropdown Status Publikasi pada Form Tugas:**
  * Ditambahkan ke `assignments/create.blade.php` dan `assignments/edit.blade.php`. Dosen dapat secara tegas menentukan apakah tugas langsung diterbitkan (*Published*) untuk memicu antrean notifikasi ke seluruh mahasiswa, atau disimpan sebagai draf (*Draft*).
* **Tombol Cepat *"Terbitkan Tugas"*:**
  * Pada `assignments/show.blade.php`, jika tugas masih berstatus draf, disediakan tombol primer *"Terbitkan Tugas"* bersanding dengan tombol edit. Tombol ini mengirimkan request `PUT` untuk mengubah status menjadi `published`, yang seketika mendispatch event `AssignmentPublished` ke tabel antrean `jobs`.

---

# Ringkasan Capaian & Verifikasi Pengujian

Seluruh rangkaian fitur asinkron, keamanan payload, dan antarmuka notifikasi telah diuji menggunakan rangkaian pengujian otomatis Laravel:

```powershell
php artisan test --filter=WeekTenNotificationTest
```

**Hasil Pengujian:**
* `PASS` published assignment event queues its listener
* `PASS` publishing assignment from web dispatches event once
* `PASS` publishing assignment from api dispatches event
* `PASS` assignment listener notifies only enrolled students
* `PASS` grade event queues listener and payload does not include grade details
* `PASS` saving grade from web dispatches grade event
* `PASS` saving grade from api dispatches grade event
* `PASS` student cannot mark another students notification as read (IDOR 404)
* `PASS` notification page shows own messages and can mark all read
* `PASS` notification poll endpoint returns unread count
* `PASS` lecturer can open gradebook and submission grade form
* `PASS` student can view grade and feedback in web transcript

**Total Pengujian:** **62 tests, 266 assertions** dinyatakan **100% Lolos (PASS)** tanpa kendala.
