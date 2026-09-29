<?php // Menandai bahwa file menggunakan PHP.

use Illuminate\Foundation\Application; // Mengimpor class utama Application Laravel.
use Illuminate\Foundation\Configuration\Exceptions; // Mengimpor konfigurasi exception Laravel.
use Illuminate\Foundation\Configuration\Middleware; // Mengimpor konfigurasi middleware Laravel.
use App\Http\Middleware\EnsureUserHasRole; // Mengimpor middleware role yang kita buat.

return Application::configure(basePath: dirname(__DIR__)) // Membuat konfigurasi utama aplikasi Laravel.
    ->withRouting( // Mengatur sumber route aplikasi.
        web: __DIR__.'/../routes/web.php', // Menentukan bahwa route web berada di routes/web.php.
        commands: __DIR__.'/../routes/console.php', // Menentukan route untuk command Artisan.
        health: '/up', // Menentukan endpoint health check aplikasi.
    )
    ->withMiddleware(function (Middleware $middleware) { // Membuka konfigurasi middleware aplikasi.
        $middleware->alias([ // Mendaftarkan alias middleware agar bisa dipanggil dengan nama singkat.
            'role' => EnsureUserHasRole::class, // Membuat alias role yang menunjuk ke EnsureUserHasRole.
        ]); // Menutup daftar alias middleware.
    })
    ->withExceptions(function (Exceptions $exceptions) { // Membuka konfigurasi exception.
        // Belum ada konfigurasi exception tambahan.
    })
    ->create(); // Menyelesaikan dan membuat instance aplikasi Laravel.