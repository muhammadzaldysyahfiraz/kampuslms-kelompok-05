<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Kredensial Akun Demo Produksi
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini membaca kata sandi khusus akun demo di lingkungan produksi
    | dari environment variable server (misal: Railway Dashboard).
    | Fungsi env() hanya dipanggil di sini agar aman terhadap config:cache.
    |
    */
    'admin_password' => env('DEMO_ADMIN_PASSWORD'),
    'dosen_password' => env('DEMO_DOSEN_PASSWORD'),
    'mahasiswa_password' => env('DEMO_MAHASISWA_PASSWORD'),
];
