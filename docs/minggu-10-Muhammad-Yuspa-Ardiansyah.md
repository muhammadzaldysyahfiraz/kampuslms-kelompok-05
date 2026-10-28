# Catatan Pemrograman Web Minggu 10

Muhammad Yuspa Ardiansyah

10241052

# READ
## 1. Konsep inti

### 1.1 Event dan Listener

Event menyatakan bahwa sesuatu telah terjadi, sedangkan Listener menjalankan pekerjaan sebagai respons terhadap event tersebut. Pemisahan ini mengurangi ketergantungan antara proses utama dan efek samping seperti pengiriman notifikasi.

```text
Dosen menyimpan nilai
        ↓
GradeReleased::dispatch($grade)
        ↓
SendGradeNotification menerima event
        ↓
Notifikasi database dikirim ke mahasiswa terkait
```

Di KampusLMS, event `GradeReleased` membawa model nilai. Listener `SendGradeNotification` mencari mahasiswa pemilik submission dan mengirim notifikasi kepadanya.

### 1.2 Queue dan `ShouldQueue`

Listener yang mengimplementasikan `ShouldQueue` dijalankan melalui queue Laravel. Dengan koneksi `database`, pekerjaan ditulis ke tabel `jobs` dan diproses oleh worker yang dijalankan terpisah.

Jika `ShouldQueue` dihapus, listener tidak lagi diperlakukan sebagai pekerjaan antrean: `handle()` dijalankan langsung sebagai bagian dari proses request yang memicu event. Pengiriman notifikasi dapat menambah waktu tunggu request.

### 1.3 Perbedaan `sync` dan `database`

| Konfigurasi | Perilaku | Kapan digunakan |
|---|---|---|
| `QUEUE_CONNECTION=sync` | Menjalankan pekerjaan langsung dalam request yang sama; tidak perlu worker. | Pengembangan sederhana atau pengujian yang memang ingin menjalankan pekerjaan langsung. |
| `QUEUE_CONNECTION=database` | Menyimpan pekerjaan ke tabel `jobs`; worker memprosesnya di luar request. | Staging/produksi bila worker dan pemantauan queue disiapkan. |

`config/queue.php` di proyek memakai `database` sebagai nilai default bila `QUEUE_CONNECTION` tidak ditentukan. Namun, `.env` lokal dapat mengganti nilai itu. Periksa konfigurasi runtime sebelum menyimpulkan perilaku server.

```bash
php artisan queue:work
```

Perintah tersebut menjalankan worker. Pada produksi, worker perlu dikelola sebagai proses layanan agar dipantau dan dimulai kembali bila berhenti.

### 1.4 Retry, kegagalan, dan pemantauan

Listener notifikasi memiliki batas percobaan dan jeda antarpercobaan. Jika pekerjaan tetap gagal, Laravel dapat menandainya sebagai gagal dan memanggil method `failed()`. `SendGradeNotification::failed()` menulis pesan error beserta ID nilai dan informasi exception ke log.

Kegagalan pekerjaan asinkron tidak selalu muncul sebagai pesan error pada halaman dosen karena request awal dapat selesai sebelum worker memproses notifikasi. Di server, pantau status worker, log aplikasi, tabel pekerjaan gagal, serta panjang antrean. Pemantauan perlu memberi peringatan kepada operator, bukan hanya mengandalkan mahasiswa melaporkan notifikasi yang tidak muncul.

### 1.5 Minimisasi payload dan privasi nilai

`GradeReleasedNotification::toDatabase()` hanya memasukkan `grade_id`, `assignment_id`, dan `assignment_title`. Skor dan feedback tidak dimasukkan ke payload notifikasi. Payload disimpan sebagai data notifikasi dan dapat dikirim kembali melalui API; karena itu, notifikasi sebaiknya hanya berisi informasi minimum untuk memberi tahu pengguna dan membawanya ke halaman yang tepat.

Mahasiswa mengambil nilai dari halaman/endpoint nilai yang dilindungi otorisasi, bukan dari isi notifikasi. `GradePolicy` membatasi mahasiswa pada nilai miliknya.

### 1.6 Kepemilikan notifikasi

Daftar notifikasi diambil melalui relasi pengguna yang sedang login. Untuk aksi terhadap satu notifikasi, `NotificationController::ensureOwner()` memastikan notifikasi tersebut tercatat pada pengguna itu. Jika bukan miliknya, controller mengembalikan `404` agar keberadaan notifikasi pengguna lain tidak dibocorkan.

Pemeriksaan ini digunakan sebelum menampilkan, menandai dibaca, atau menghapus notifikasi. Route notifikasi juga berada di dalam middleware `auth`.

### 1.7 Tampilan dan respons JSON

Halaman notifikasi menampilkan filter semua/belum dibaca serta aksi tandai dibaca, tandai semua dibaca, dan hapus. `NotificationController::index()` mengembalikan JSON ketika request menginginkannya melalui `wantsJson()` atau `ajax()`.

Header saat ini menampilkan jumlah notifikasi belum dibaca untuk pengguna yang login. Endpoint JSON dapat mendukung klien yang memerlukan data notifikasi, tetapi keberadaan endpoint tersebut saja tidak membuktikan bahwa polling berkala sudah dijalankan.

---

## 2. READ

### Menelusuri alur notifikasi nilai

1. `GradeController::store()` dan `GradeController::update()` memicu `GradeReleased` saat nilai baru dibuat atau nilai/feedback berubah.
2. `SendGradeNotification` mengimplementasikan `ShouldQueue`, membatasi penerima ke mahasiswa pemilik submission, dan memiliki konfigurasi percobaan ulang.
3. `GradeReleasedNotification` menggunakan kanal `database`; payload hanya berisi identitas/referensi nilai dan tugas serta judul tugas.
4. `NotificationController` membatasi notifikasi berdasarkan pengguna yang login dan memeriksa kepemilikan untuk operasi per notifikasi.
5. `GradePolicy::view()` memastikan mahasiswa hanya dapat melihat nilai pada submission miliknya.
6. `WeekTenNotificationTest` mencakup queue listener, payload tanpa skor/feedback, dan percobaan mengakses notifikasi pengguna lain.

Perintah pemeriksaan yang membantu:

```bash
php artisan route:list --path=notifications
php artisan test --filter=WeekTenNotificationTest
```

---

## 3. BREAK

| # | Kerusakan/skenario | Dampak yang diharapkan | Kondisi yang benar |
|---:|---|---|---|
| 1 | Worker `database` dihentikan saat ada pekerjaan antrean | Request yang sudah menaruh pekerjaan dapat tetap berhasil, tetapi notifikasi tertunda sampai worker berjalan lagi. | Pantau worker dan panjang antrean; mulai ulang worker sesuai prosedur operasional. |
| 2 | Mengubah queue ke `sync` | Pekerjaan diproses pada request yang sama; tidak ada worker terpisah dan request dapat lebih lambat. | Gunakan hanya sesuai kebutuhan lingkungan dan pahami dampak latensinya. |
| 3 | Menghapus `ShouldQueue` dari Listener | `handle()` berjalan langsung ketika event dipicu sehingga request dapat menunggu pengiriman notifikasi. | Pertahankan `ShouldQueue` untuk pekerjaan yang memang harus diproses melalui queue. |
| 4 | Tidak menjalankan worker pada koneksi `database` | Pekerjaan dapat menumpuk di tabel `jobs`; notifikasi tidak segera diproses. | Jalankan dan awasi worker di lingkungan yang menggunakan database queue. |
| 5 | Memasukkan skor atau feedback ke payload | Data akademik privat tersimpan pada data notifikasi dan berpotensi ikut terekspos melalui response atau pencatatan. | Kirim metadata minimum; akses nilai melalui endpoint yang berotorisasi. |
| 6 | Mengubah ID notifikasi pada request pengguna | Tanpa pemeriksaan kepemilikan, pengguna dapat mencoba membaca atau mengubah notifikasi pengguna lain (IDOR). | Query kepemilikan melalui relasi notifikasi milik pengguna login; tolak akses yang bukan haknya. |
| 7 | Mengabaikan kegagalan setelah retry habis | Pekerjaan gagal dapat tidak diketahui operator dan notifikasi tidak terkirim. | Gunakan `failed()`, log, failed-jobs storage, dan monitoring/alert produksi. |

---

## 4. FIX

### Pengaman yang sudah ada di KampusLMS

- Listener notifikasi nilai menerapkan `ShouldQueue`, memiliki batas percobaan dan jeda percobaan ulang.
- Method `failed()` pada listener nilai mencatat error ke log.
- Notifikasi nilai menggunakan kanal database dan tidak mengandung skor maupun feedback.
- Penerima notifikasi nilai adalah mahasiswa pemilik submission, bukan seluruh pengguna.
- Notifikasi hanya dapat dibuka dan dimodifikasi oleh pemiliknya; akses ke notifikasi lain berakhir dengan `404`.
- Daftar nilai mahasiswa difilter ke submission milik mahasiswa, dan `GradePolicy` memeriksa kepemilikan nilai.
- Konfigurasi queue default proyek adalah `database`, kecuali ditimpa oleh environment.

### Tindakan operasional yang tetap diperlukan

1. Pastikan `QUEUE_CONNECTION` pada server sesuai dengan desain deployment.
2. Jika memakai `database`, jalankan worker sebagai layanan yang diawasi dan pastikan tabel queue tersedia.
3. Pantau log aplikasi dan pekerjaan gagal; `failed()` mencatat error, tetapi operator tetap memerlukan mekanisme alert.
4. Uji jalur gagal dan pemulihan di lingkungan non-produksi, termasuk memastikan pekerjaan yang tertunda diproses setelah worker aktif kembali.
5. Jangan memasukkan skor, feedback privat, token, atau data sensitif lain ke payload notifikasi.

---

## 5. BUILD

- [x] Event nilai dipicu setelah nilai baru dibuat atau nilai/feedback berubah.
- [x] Listener nilai dijalankan melalui queue dan mengirim notifikasi kepada mahasiswa pemilik submission.
- [x] Payload notifikasi hanya memuat `grade_id`, `assignment_id`, dan `assignment_title`.
- [x] Aksi terhadap notifikasi memeriksa kepemilikan pengguna.
- [x] Halaman nilai mahasiswa dibatasi berdasarkan pemilik submission dan `GradePolicy`.
- [ ] Worker queue produksi dijalankan dan dipantau sebagai layanan.
- [ ] Log dan pekerjaan gagal dipantau dengan alert operasional yang sesuai.
- [ ] Jika polling berkala akan digunakan, implementasikan dan uji interval, respons error, dan penghentian polling dengan jelas.
- [ ] Pastikan perilaku `sync` dan `database` diuji pada konfigurasi lingkungan yang sesuai.

---

## 6. Checkpoint Minggu 10

### 1. Apa yang terjadi kalau queue worker mati saat notifikasi dikirim?

Dengan `QUEUE_CONNECTION=database`, pekerjaan yang belum diproses dapat tertahan di antrean dan notifikasi terlambat sampai worker aktif kembali. Request penyimpanan nilai dapat tetap berhasil karena pengiriman berada di pekerjaan terpisah. Jika koneksi memakai `sync`, tidak ada worker terpisah; listener dijalankan langsung di request.

### 2. Kenapa kegagalan itu tidak selalu menghasilkan pesan error di halaman? Bagaimana mengetahuinya di produksi?

Karena pekerjaan asinkron diproses setelah request awal selesai. Kegagalan worker bukan otomatis menjadi pesan pada halaman dosen. Operator perlu memantau worker, log aplikasi, antrean `jobs`, pekerjaan gagal, serta menyiapkan alert dan prosedur retry.

### 3. Apa beda `QUEUE_CONNECTION=sync` dan `database`?

`sync` menjalankan listener langsung pada request yang sama dan tidak memerlukan worker. `database` menyimpan pekerjaan di tabel `jobs` untuk diproses worker terpisah. `sync` praktis untuk pengembangan/pengujian sederhana; `database` lebih sesuai ketika server menyiapkan worker dan monitoring.

### 4. Tunjukkan Listener. Apa akibatnya kalau `ShouldQueue` dihapus?

Tunjukkan `app/Listeners/SendGradeNotification.php`, khususnya deklarasi `implements ShouldQueue` dan method `handle()`. Jika kontrak itu dihapus, Laravel menjalankan listener secara langsung ketika event dipicu. Akibatnya request dapat menunggu proses notifikasi dan menjadi lebih lambat.

### 5. Kenapa nilai mahasiswa tidak boleh masuk payload notifikasi?

Payload disimpan di database notifikasi dan dapat ikut dikirim sebagai data API. Skor dan feedback adalah informasi privat. Notifikasi cukup membawa referensi seperti `grade_id`, `assignment_id`, dan judul tugas; nilai sebenarnya dibuka dari halaman yang memeriksa otorisasi mahasiswa.

### 6. Apa yang mencegah mahasiswa menandai notifikasi orang lain sebagai sudah dibaca?

Sebelum mengubah status, `NotificationController::ensureOwner()` memeriksa bahwa ID notifikasi ada di relasi `notifications()` milik pengguna yang sedang login. Jika tidak cocok, request ditolak dengan `404`.

### 7. Tunjukkan satu bagian yang dibantu AI. Apa yang diubah dan kenapa?

Bagian payload `GradeReleasedNotification`. Saya memeriksa ulang agar hanya referensi tugas/nilai dan judul yang dikirim, tanpa skor atau feedback, lalu memastikan mahasiswa membuka nilai melalui halaman yang dilindungi otorisasi itu.

---
