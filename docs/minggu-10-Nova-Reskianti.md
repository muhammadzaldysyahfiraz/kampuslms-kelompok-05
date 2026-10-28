# Catatan Praktikum Minggu 10: Realtime, Event, Queue, dan Notifikasi

**Mata Kuliah:** Pemrograman Web
**Nama:** Nova Reskianti
**NIM:** 10241058

---

## Catatan awal: keadaan KampusLMS sebelum percobaan

Saya periksa dulu isi aplikasi sebelum menjalankan apa pun:

```bash
ls app/Events app/Listeners app/Notifications
# ls: cannot access 'app/Events': No such file or directory   (dua lainnya sama)
grep -rn "ShouldQueue|Notification::|->notify(|dispatch(" app routes tests
# (tidak ada satu pun hasil)
```

Artinya: **KampusLMS belum punya Event, Listener, maupun Notification sama sekali.** Yang sudah ada hanya *sisi pembaca* notifikasi (`NotificationController` + lonceng di navbar) dan tabel `notifications`. Tabel `notifications` berisi **0 baris** sebelum percobaan.

Karena READ dan BREAK modul menuntut "setelah Event dan Listener pertama jadi", saya **memasang rangka sementara** (3 berkas: `app/Events/AssignmentPublished.php`, `app/Listeners/SendAssignmentNotification.php`, `app/Notifications/NewAssignmentNotification.php`) hanya untuk percobaan, menjalankan seluruh percobaan di bawah, lalu **menghapus ketiganya kembali**. Aplikasi dikembalikan persis seperti semula.

**Lingkungan percobaan:** PHP 8.4.24 (Herd), SQLite, `.env` → `QUEUE_CONNECTION=database`, server `php artisan serve` di `127.0.0.1:8000`. Rangka percobaan memakai pola yang sama persis dengan modul: `AssignmentPublished` → `SendAssignmentNotification implements ShouldQueue` → `NewAssignmentNotification implements ShouldQueue` (via `database`), `$tries = 3`, `$backoff = 10`.

> ⚠️ **Halangan teknis yang saya temui lebih dulu:** `php` di PATH saya versi **8.1.10**, sedangkan `vendor/composer/platform_check.php` mensyaratkan **≥ 8.4.1**, sehingga semua perintah `artisan` gagal dengan `Composer detected issues in your platform`. Solusinya memakai binary PHP 8.4 milik Herd (`~/.config/herd/bin/php84/php.exe`). Ini bukan error aplikasi — jangan dibuang waktu mencari bug Laravel untuk ini.

---

## READ: Ikuti Satu Job

### 1. Matikan worker, buat satu tugas baru — apa isi tabel `jobs`?

Worker dipastikan tidak berjalan (`tasklist` → tidak ada `php.exe`), lalu event di-dispatch:

```text
--- BREAK #1: QUEUE_CONNECTION=database, worker mati, buat tugas ---
dispatch selesai dalam 39.7 ms — TIDAK ADA ERROR
sebelum: jobs=0 notifikasi=15
sesudah: jobs=1 notifikasi=15
QUEUE_CONNECTION=database
```

**Isi tabel `jobs`: 1 baris.** Poin paling penting: tidak ada pesan error apa pun, aplikasi terlihat sepenuhnya normal, tetapi **nol notifikasi terkirim**. Inilah "kegagalan senyap" yang diingatkan modul.

### 2. Baca kolom `payload` — bisakah mengenali Listener dan ID tugasnya?

```text
id=1 queue=default attempts=0 reserved_at=NULL
{"uuid":"4654735c-...","displayName":"App\\Listeners\\SendAssignmentNotification",
 "job":"Illuminate\\Queue\\CallQueuedHandler@call","maxTries":3,"backoff":"10",
 "data":{"commandName":"Illuminate\\Events\\CallQueuedListener",
 "command":"O:36:\"Illuminate\\Events\\CallQueuedListener\":26:{s:5:\"class\";
 s:40:\"App\\Listeners\\SendAssignmentNotification\";s:6:\"method\";s:6:\"handle\";
 ...s:10:\"assignment\";O:45:\"Illuminate\\Contracts\\Database\ModelIdentifier\":5:
 {s:5:\"class\";s:21:\"App\\Models\\Assignment\";s:2:\"id\";i:1;...
```

Bisa. Terbaca jelas: nama Listener (`App\Listeners\SendAssignmentNotification`), method yang dipanggil (`handle`), **ID tugas = 1**, `maxTries=3`, `backoff=10`. Event-nya tidak disimpan utuh, hanya `ModelIdentifier` (ID assignment) — jadi saat worker bangun, model diambil ulang dari database.

### 3. Jalankan `php artisan queue:work`, amati keluarannya

```text
13:33:48 App\Listeners\SendAssignmentNotification 1 database default ..... RUNNING
13:33:49 App\Listeners\SendAssignmentNotification 1 database default ..... 933.45ms DONE
13:33:49 App\Notifications\NewAssignmentNotification 2 database default ... RUNNING
13:33:50 App\Notifications\NewAssignmentNotification 2 database default ... 476.76ms DONE
... (job notifikasi 3 sampai 16, masing-masing 10–30 ms) ...
=== jobs: 0 ===
```

**Tabel `jobs` kembali kosong.** Perhatikan jumlahnya: **16 job** untuk 15 mahasiswa = 1 job listener + 15 job notifikasi. Ini persis yang diperingatkan modul (40 mahasiswa → 41 job, bukan 1) — **bukan bug**, dan justru bagus karena satu notifikasi yang gagal tidak menjatuhkan 39 lainnya.

### 4. Tabel `notifications` — berapa baris? Cocok dengan jumlah mahasiswa?

```text
mahasiswa course 1 = 15
notifikasi (type=App\Notifications\NewAssignmentNotification) = 15
distinct notifiable_id = 15
```

**Cocok: 15 baris untuk 15 mahasiswa** mata kuliah Basis Data (course 1). Tidak ada mahasiswa yang kelebihan atau kekurangan notifikasi.

### 5. Baca kolom `data` — ada yang tidak seharusnya tersimpan?

```text
data = {"assignment_id":1,"course_name":"Basis Data","title":"Tugas Pertemuan 1",
        "due_at":"2026-09-29T23:53:03+00:00",
        "url":"http:\/\/kampuslms.test\/assignments\/1"}
```

Isinya hanya ID, judul, nama mata kuliah, tenggat, dan tautan — **tidak ada nilai, tidak ada feedback**. ✅ Aman.

**Temuan kecil (bukan bagian modul):** `url` memakai `APP_URL=http://kampuslms.test`, padahal server yang saya pakai `http://127.0.0.1:8000`. Kalau lonceng diklik, tautannya tidak akan terbuka di lokal. Nilai `APP_URL` perlu disesuaikan per lingkungan.

---

## BREAK: Delapan Kerusakan

### 1. `QUEUE_CONNECTION=database`, jangan jalankan worker, buat tugas baru
✅ Dibuktikan — **ini pelajaran inti minggu ini**

Hasilnya ada di READ #1: dispatch selesai **39,7 ms tanpa satu pun pesan error**, `jobs` bertambah 1, `notifications` **tetap 15**. Tidak ada yang memberi tahu pengguna bahwa 15 notifikasi belum terkirim.

Bukti tambahan bahwa di aplikasi kita ini bahkan lebih "senyap" lagi:

```bash
grep -rn "AssignmentPublished|dispatch(" app/Http app/Models routes
# (tidak ada hasil) — kode aplikasi tidak pernah mengirim event apa pun
```

Jadi saat ini notifikasi tidak pernah terkirim **baik worker jalan maupun mati**.

### 2. Hapus `implements ShouldQueue` dari Listener, buat tugas untuk 15 mahasiswa
✅ Dibuktikan dengan pengukuran (3 kali jalan)

| Konfigurasi | Waktu dispatch (15 mahasiswa) | `jobs` | `notifications` |
|---|---:|---:|---:|
| Queue database + `ShouldQueue` (worker mati) | **17,4 / 4,1 / 4,0 ms** | menumpuk | tidak berubah |
| Listener **tanpa** `ShouldQueue` | **114,8 / 63,9 / 70,7 ms** | 0 | langsung +45 |

Semua pekerjaan penerimaan kembali ke dalam request. Selain lebih lambat, yang lebih berbahaya adalah **kegagalan menular ke pemanggil**. Saya sisipkan satu exception di `via()`:

```text
EXCEPTION MENYEBAR KE PEMANGGIL: RuntimeException: Simulasi kegagalan pengiriman notifikasi
waktu: 15.9 ms
```

Kalau pemanggilnya controller pembuatan tugas, maka **tugas yang baru saja dibuat bisa ikut gagal tersimpan** — persis seperti yang ditulis modul. Dengan `ShouldQueue`, exception ditahan di dalam job.

> **Kejujuran angka:** saluran kita `database`, bukan SMTP, jadi selisihnya masih ratusan milidetik. Ratusan milidetik × 40 mahasiswa, ditambah koneksi SMTP sungguhan, adalah detik sampai puluhan detik. Yang tidak berubah berapa pun salurannya: **exception tetap menyebar ke request di mode sinkron.**

### 3. Ganti `QUEUE_CONNECTION=sync`, ulangi nomor 2
✅ Dibuktikan

```text
KONFIGURASI: QUEUE_CONNECTION=sync (ShouldQueue tetap ada di listener & notification)
dispatch assignment 6 (15 mahasiswa): 292.4 ms
dispatch assignment 7 (15 mahasiswa): 123.4 ms
dispatch assignment 8 (15 mahasiswa): 108.4 ms
jobs=0 notifikasi=105
```

Perbandingan ketiganya:

- **database + worker mati** → 4–17 ms, job menumpuk, notifikasi **tidak terkirim** (gagal senyap).
- **database + worker jalan** → 4–17 ms di sisi request, notifikasi terkirim oleh proses terpisah. ✅
- **sync** → 108–292 ms di dalam request, `jobs` **tidak pernah terisi** (untuk apa ada tabel `jobs` kalau tidak dipakai?), kegagalan menyebar ke request.

`sync` berguna saat mengembangkan karena tidak perlu menyalakan worker — tapi seluruh manfaat queue hilang.

### 4. Buat Notification melempar exception, jalankan worker — amati `tries` dan `failed_jobs`
✅ Dibuktikan, **plus satu temuan yang tidak ada di modul**

Listener dibuat `throw` di `handle()`, `$tries = 3`, `$backoff = 1`:

```text
--- run 1 --- App\Listeners\SendAssignmentNotification 24 ... FAIL   failed_jobs=0
--- run 2 --- App\Listeners\SendAssignmentNotification 25 ... FAIL   failed_jobs=0
--- run 3 --- App\Listeners\SendAssignmentNotification 26 ... FAIL   failed_jobs=1

$ php artisan queue:failed
  2026-10-27 13:40:33 646e54c0-07bb-4191-8f35-23be082a7a64 . database@default
  App\Listeners\SendAssignmentNotification

failed_jobs=1  →  RuntimeException: Simulasi kegagalan listener in ...SendAssignmentNotification.php:20
jobs sisa=0
```

Tiga percobaan habis → masuk `failed_jobs` tanpa perintah tambahan, `queue:failed` bisa membacanya. Tabel `failed_jobs` memang sudah dibuat migrasi bawaan (`0001_01_01_000002_create_jobs_table.php`), jadi **jangan** menjalankan `make:queue-failed-table`.

**Temuan 1 — tanda tangan `failed()` pada queued listener.** Versi pertama saya menulis (seperti biasa ditulis AI):

```php
public function failed(\Throwable $e): void { Log::error('...'); }   // ❌
```

Hasilnya ada di log, dan log kegagalan **tidak pernah tertulis**:

```text
local.ERROR: App\Listeners\SendAssignmentNotification::failed(): Argument #1 ($e) must be
of type Throwable, App\Events\AssignmentPublished given, called in .../CallQueuedListener.php on line 218
```

Kode framework-nya sendiri:

```php
// vendor/.../Illuminate/Events/CallQueuedListener.php:218
$parameters = array_merge(array_values($this->data), [$e]);   // event dulu, exception kemudian
$handler->failed(...$parameters);
```

Jadi signature yang benar untuk listener adalah **`failed(AssignmentPublished $event, \Throwable $e)`**. Setelah diperbaiki:

```text
local.ERROR: SendAssignmentNotification gagal untuk tugas 13: Simulasi kegagalan listener
```

Ini jawaban kandidat kuat untuk pertanyaan interview: *tulis satu bagian yang dibantu AI — apa yang Anda ubah?*

**Temuan 2 — `--stop-when-empty` berhenti di tengah retry.** Setelah gagal, job di-*release* dengan jeda `backoff`; worker yang memakai `--stop-when-empty` mengira antrian kosong dan keluar, sehingga percobaan ke-2 dan ke-3 baru jalan saat worker dijalankan ulang. Di produksi worker berjalan terus (Supervisor), jadi ini tidak terjadi — tapi menjelaskan kenapa hasil percobaan terlihat "berhenti di 2 kali gagal".

### 5. Hentikan worker di tengah pemrosesan job — kembali ke queue atau hilang?
✅ Dibuktikan (job sengaja dibuat lambat 5 detik agar jendelanya teramati)

```text
PID worker = 9560  → Stop-Process -Force
sisa=[] (sudah mati)

--- isi jobs SETELAH worker dibunuh di tengah pemrosesan ---
jobs=59 attempts=1 reserved_at=1793108591  (unix_now=1793108595)

--- jalankan worker SEGERA (retry_after=90 dtk belum lewat) ---
jobs sesudah: 1                      ← job masih reserved, tidak diproses

--- tunggu 95 detik ---
App\Listeners\SendAssignmentNotification ... DONE
App\Notifications\NewAssignmentNotification 73 ... DONE
... 15 job notifikasi DONE ...
jobs: 0   notif: 135 → 150
```

**Kesimpulan: job tidak hilang.** Ia tertinggal di tabel `jobs` dengan `reserved_at` terisi, dan baru diambil lagi setelah `retry_after` (90 detik, `config/queue.php`) lewat — lalu diproses ulang sampai selesai. Yang perlu ditangkap: **selama 90 detik itu notifikasi tertunda tanpa pesan apa pun.**

**Temuan 3 (kebetulan, tapi nyata):** saat dua worker berjalan bersamaan pada SQLite, muncul job gagal:

```text
PDOException: SQLSTATE[HY000]: General error: 5 database is locked
```

Satu job masuk `failed_jobs` hanya karena ada dua worker. Di MySQL/PostgreSQL kasusnya berbeda, tapi pelajarannya: **driver database + banyak worker punya batasnya sendiri** — dan kegagalannya tetap tercatat rapi di `failed_jobs`.

### 6. Masukkan nilai mahasiswa ke payload notifikasi, lalu buka tabel `notifications`
✅ Dibuktikan — dan ternyata lebih luas dari yang saya kira

Payload yang dimodifikasi:

```json
{"assignment_id":15,"course_name":"Sistem Informasi","title":"Tugas Akhir",
 "due_at":"2026-11-05T23:53:03+00:00","url":"http://kampuslms.test/assignments/15",
 "score":95,"feedback":"Implementasi sangat baik, kerja rapi."}
```

Kebocorannya di dua tempat:

1. **Tabel `notifications`** — kolom `data` tidak terenkripsi, tersimpan permanen, dan tetap ada meski nilai diubah/dihapus. Siapa pun yang bisa membaca tabel itu (admin, backup yang bocor, query salah tulis) langsung melihat nilai dan feedback.
2. **Endpoint lonceng** — saya tarik `GET /notifications` dengan session login, dan nilai itu ikut terkirim ke browser:

```text
"data":{"assignment_id":15,...,"score":95,"feedback":"Implementasi sangat baik, kerja rapi."}
```

Payload yang benar (tanpa `score`/`feedback`) hanya: `assignment_id`, `course_name`, `title`, `due_at`, `url`.

### 7. Panggil endpoint "tandai dibaca" dengan ID notifikasi orang lain
✅ Dibuktikan — **aplikasi kita menahannya**

Dua mahasiswa (user 5 = `mahasiswa@kampuslms.test`, user 6), masing-masing punya notifikasi:

```text
--- (1) user5 menandai notifikasi MILIKNYA sendiri ---   HTTP 200
--- (2) user5 mencoba menandai notifikasi MILIK user6 --- HTTP 404
{"message":"No query results for model [Illuminate\Notifications\DatabaseNotification] 8a10eda0-..."}
--- (4) cek DB: own read_at=2026-10-27 13:47:05 | other read_at=(kosong)   ← tidak berubah
```

Jalur web (session login + CSRF) juga saya uji, bukan hanya API:

```text
GET  /notifications              → 200 (halaman sendiri)
PATCH /notifications/{milik u6}/read → 404
GET  /notifications/{milik u6}      → 404
DELETE /notifications/{milik u6}    → 404
```

Pelindungnya satu method, dipakai oleh semua endpoint tulis/hapus:

```php
// app/Http/Controllers/NotificationController.php
private function ensureOwner(Request $request, DatabaseNotification $notification): void
{
    $user = $request->user();
    abort_unless($user, 401);
    // 404 menyamarkan keberadaan notifikasi milik pengguna lain (IDOR Prevention).
    abort_unless($user->notifications()->whereKey($notification->getKey())->exists(), 404);
}
```

Kuncinya: notifikasi dicari **lewat relasi `$user->notifications()`**, bukan dari seluruh tabel. Versi API (`Api\NotificationController::read`) memakai pola yang sama (`$request->user()->notifications()->findOrFail($id)`).

### 8. Ganti `PrivateChannel` menjadi `Channel` (Reverb) — penyadapan realtime
⚠️ **Tidak berlaku di KampusLMS saat ini** — ini temuan, bukan percobaan

```bash
grep -in "reverb|pusher|broadcast" composer.json config/*.php resources/js/app.js
# (tidak ada hasil)
ls config/broadcasting.php
# No such file or directory
```

Reverb/broadcasting tidak terpasang sama sekali, jadi tidak ada channel yang bisa dipindai. Yang ada baru **polling** (`setInterval` 30 detik di `components/layout.blade.php`) — dan polling pun sudah memenuhi Sub-CPMK minggu ini.

Yang wajib saya catat untuk saat broadcasting dipasang nanti: channel `users.{id}` **wajib `PrivateChannel`**. Dengan `Channel` (publik) siapa pun cukup berlangganan `users.5` dan membaca notifikasi orang lain — IDOR versi WebSocket, tanpa perlu login.

---

## FIX: Repo Cacat Branch `w10` (7 Masalah) dan Pemetaannya ke KampusLMS

> Repo `kampuslms-broken` tidak tersedia di workspace saya (sama seperti catatan minggu 6–9). Seperti minggu lalu, saya petakan tiap masalah ke kode KampusLMS kami dan buktikan statusnya sendiri.

### 1. Listener tanpa `ShouldQueue`
❌ **Belum ada sama sekali**

Bukan "rusak", tetapi belum dibuat: `app/Listeners` tidak ada, `grep -rn "ShouldQueue" app` → 0 hasil. Rangka percobaan saya yang dipasang sementara sudah memakai `ShouldQueue`, dan terbukti di BREAK #2 apa akibatnya kalau dihapus (dispatch 114 ms + exception menyebar ke request).

### 2. Notifikasi dikirim dalam loop satu per satu (N+1)
❌ **Belum ada** — tapi saya ukur dulu pola mana yang benar-benar boros

| Pola pengiriman | Query DB | Job | Waktu |
|---|---:|---:|---:|
| V1 `Notification::send($collection, $n)` | 15 | 15 | 101,3 ms |
| V2 loop `foreach … Notification::send($s, $n)` | 15 | 15 | 51,1 ms |
| V3 loop **+ query penerima di dalam loop** | **30** | 15 | 61,6 ms |

Hasilnya mengejutkan dan perlu saya catat: **bentuk loop-nya bukan biayanya.** V1 dan V2 sama persis (15 query = 15 insert job). N+1 muncul di V3, yaitu saat **relasi/penerima diambil ulang di dalam loop** (+15 query, persis dua kali lipat). Jadi saat implementasi nanti: `Notification::send($mahasiswaCollection, …)` dengan daftar penerima yang sudah di-eager-load, bukan query ulang per penerima.

### 3. Payload memuat nilai mahasiswa
⚠️ **Belum ada kebocoran nyata, tapi risikonya sudah saya peragakan di BREAK #6**

Aplikasi tidak mengirim notifikasi apa pun, jadi tabel `notifications` masih bersih. Yang sudah terbukti: begitu `score`/`feedback` dimasukkan ke `toArray()`, nilai itu langsung **permanen di tabel** dan **ikut dikirim ke browser** lewat endpoint lonceng. Aturan untuk implementasi nanti: hanya `assignment_id`, judul, mata kuliah, tenggat, URL.

### 4. Endpoint tandai-dibaca tanpa pemeriksaan kepemilikan
✅ **Sudah benar dan sudah teruji**

Bukti BREAK #7: API → 200 untuk milik sendiri, **404** untuk milik orang lain; web (PATCH/GET/DELETE) → **404**. Kodenya `ensureOwner()` yang berjalan sebelum `markAsRead()`/`delete()`, plus `Api\NotificationController::read` dengan `findOrFail` lewat relasi pengguna.

### 5. Tidak ada `failed_jobs`
✅ **Sudah ada, dari kerangka bawaan**

```text
database/migrations/0001_01_01_000002_create_jobs_table.php
  Schema::create('jobs', ...)        line 14
  Schema::create('job_batches', ...) line 24
  Schema::create('failed_jobs', ...) line 37  (+ uuid unique, failed_at)
```

Terbukti berfungsi di BREAK #4 (`queue:failed` menampilkan job gagal, tabel berisi 1 baris). `QUEUE_CONNECTION=database` juga sudah ada di `.env` **dan** `.env.example` (baris 38). Jangan jalankan `make:queue-table` / `make:queue-failed-table`.

### 6. Job tanpa `tries`
⚠️ **Aplikasi belum punya job produksi**; pada rangka percobaan saya `public int $tries = 3; public int $backoff = 10;` terbukti: 3 percobaan → `failed_jobs`. Saat implementasi, nilai ini harus ditulis di Listener/Notification, bukan hanya mengandalkan `--tries` CLI (Supervisor bisa saja dijalankan tanpa flag itu).

### 7. Channel broadcast publik
⚠️ **Belum ada broadcasting** (lihat BREAK #8). Tidak ada kode yang bisa diperbaiki hari ini; risikonya baru muncul kalau Reverb dipasang tanpa `PrivateChannel`.

**Ringkasan FIX:** 2 sudah benar (4, 5), 1 sudah ada tapi belum dipakai (6), 4 sisanya **belum dikerjakan** karena fiturnya memang belum ada (1, 2, 3, 7).

---

## BUILD: Milestone M3 — Status Jujur (27 Okt 2026)

| # | Deliverable | Status | Bukti |
|---|---|---|---|
| 1 | Event + Listener + Notification untuk **dua** peristiwa (tugas baru, nilai diberikan) | ❌ | `app/Events`, `app/Listeners`, `app/Notifications` tidak ada; tidak ada `dispatch(`/`notify(` di `app/`, `routes/` |
| 2 | Queue database aktif, **worker terdokumentasi di README** | ⚠️ setengah | `QUEUE_CONNECTION=database` ✅; `grep -in "queue\|worker" README.md` → **tidak ada satu pun** (kata "notifikasi" hanya muncul di kalimat promosi deskripsi) |
| 3 | `failed_jobs` terpasang, job punya `tries`/`backoff`, `failed()` mencatat log | ⚠️ | Tabel ✅ (migrasi bawaan); kelas job belum ada; `failed()` sudah saya buktikan bekerja **dan** jebakannya di BREAK #4 |
| 4 | Lonceng notifikasi + jumlah belum dibaca, polling (minimal) | ✅ | `components/layout.blade.php:93` `unreadNotifications()->count()`, badge, dan `setInterval(..., 30000)` yang memanggil `GET /notifications` lalu meng-update `unread_count` |
| 5 | Halaman daftar notifikasi, bisa ditandai dibaca, dengan pemeriksaan kepemilikan | ✅ | `notifications/index.blade.php`, 5 route web + 2 route API; teruji di BREAK #7 (404 untuk ID orang lain) |
| 6 | Alur penilaian lengkap: dosen lihat daftar → nilai + feedback → mahasiswa terima notifikasi dan lihat nilai | ⚠️ | Penilaian ✅ (`GradeController::store` dengan upsert, dosen pengampu saja), tampilan nilai ✅, **notifikasi ❌** (bagian akhir rantainya belum ada) |
| 7 | Rekap nilai per mata kuliah (dosen) dan per mahasiswa (mahasiswa) | ✅ | `GradeController::indexForCourse` (matriks + rata-rata per mahasiswa/tugas) dan `indexMine` (rekap per mata kuliah); view `grades/course.blade.php` & `grades/mine.blade.php` |
| 8 | Payload notifikasi tidak memuat nilai/feedback | ➖ belum berlaku | Tidak ada payload dikirim; aturan + risikonya sudah diperagakan di BREAK #6 |

**Kesehatan aplikasi setelah semua percobaan dan pembersihan:**

```text
$ php artisan test
  Tests:  50 passed (225 assertions)
```

Termasuk 7 test `WeekTenGradebookTest` (rekap nilai dosen & mahasiswa, 403 lintas dosen) dan `user can view and read own notifications`.

---