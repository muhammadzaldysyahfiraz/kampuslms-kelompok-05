<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
});

Route::get('/courses', function () {
    $nama = "<script>alert('XSS')</script>";
    return Illuminate\Support\Facades\Blade::render('<h1>Uji Coba XSS: {!! $nama !!}</h1>', ['nama' => $nama]);
});
