# Catatan Minggu 1 Pemrograman Web


Nama: Muhammad Yuspa Ardiansyah
NIM: 10241052
### READ


#### 1. Analisis `public/index.php`


**Hasil Analisis:**


Berkas `public/index.php` merupakan titik awal ketika aplikasi Laravel ini menerima sebuah request. Pertama, berkas ini mencatat waktu dimulainya aplikasi melalui `LARAVEL_START` dan memeriksa apakah terdapat file `maintenance.php` untuk mengetahui apakah aplikasi sedang berada dalam mode pemeliharaan.


Setelah itu, Laravel memuat Composer autoloader melalui `vendor/autoload.php` agar class dan package yang dibutuhkan aplikasi dapat digunakan. Selanjutnya, konfigurasi aplikasi dari `bootstrap/app.php` dimuat untuk membentuk instance aplikasi Laravel.


Pada bagian terakhir, `Request::capture()` digunakan untuk mengambil request yang dikirim oleh browser, kemudian request tersebut diteruskan ke aplikasi melalui `$app->handleRequest()` agar dapat diproses dan menghasilkan response yang nantinya dikirim kembali kepada pengguna.


#### 2. Analisis `bootstrap/app.php`


**Hasil Analisis:**


Berkas `bootstrap/app.php` digunakan untuk melakukan konfigurasi awal dan membentuk aplikasi Laravel. Di dalamnya terdapat beberapa bagian utama yang memiliki fungsi berbeda:


- **Route:** Diatur melalui `->withRouting()`. Bagian ini menentukan lokasi file yang berisi route web dan route untuk perintah console. Selain itu, terdapat `health: '/up'` yang menyediakan endpoint untuk pemeriksaan kondisi aplikasi.


- **Middleware:** Diatur melalui `->withMiddleware()`. Bagian ini menjadi tempat untuk melakukan konfigurasi middleware, yaitu lapisan yang dapat digunakan untuk memproses request sebelum diteruskan ke bagian aplikasi berikutnya.


- **Exception:** Diatur melalui `->withExceptions()`. Bagian ini digunakan untuk melakukan konfigurasi terhadap penanganan exception atau kesalahan yang terjadi selama aplikasi berjalan.


#### 3. Analisis `routes/web.php`


**Hasil Percobaan:**


Pada file `routes/web.php`, terdapat route dengan alamat `/` yang digunakan sebagai halaman utama aplikasi. Route tersebut menggunakan method `GET` dan ketika diakses akan menjalankan fungsi yang mengembalikan view `welcome`.


Setelah mengikuti instruksi praktikum, isi fungsi pada route tersebut diubah sehingga halaman utama tidak lagi menampilkan halaman `welcome`, tetapi menampilkan teks yang telah ditentukan yaitu **Halo ini Yuspa**.


#### 4. Analisis `php artisan route:list`


**Hasil Percobaan:**


Setelah menjalankan perintah `php artisan route:list` melalui terminal, Laravel menampilkan daftar route yang aktif pada aplikasi. Hasil tersebut kemudian dibandingkan dengan route yang terdapat pada `routes/web.php` dan konfigurasi di `bootstrap/app.php`.


- **`GET|HEAD /`** merupakan route halaman utama yang berasal dari `routes/web.php`. Route tersebut menggunakan alamat `/` dan menjalankan fungsi yang telah dimodifikasi pada percobaan sebelumnya.


- **`GET|HEAD up`** berasal dari konfigurasi `health: '/up'` pada method `withRouting()` di `bootstrap/app.php`. Route ini digunakan sebagai jalur pemeriksaan kondisi aplikasi.


- **`GET|HEAD storage/{path}`** dan **`PUT storage/{path}`** merupakan route yang tersedia dari mekanisme internal Laravel untuk menangani kebutuhan terkait penyimpanan file.


Dari hasil tersebut dapat dilihat bahwa perintah `php artisan route:list` menampilkan route yang terdaftar dalam aplikasi, baik route yang dibuat secara langsung pada `routes/web.php` maupun route yang berasal dari konfigurasi dan komponen Laravel.


