<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

// Menampilkan halaman Dashboard.
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Menampilkan daftar mata kuliah.
// Menggunakan GET karena halaman ini hanya mengambil/menampilkan data.
Route::get('/courses', [CourseController::class, 'index'])
    ->name('courses.index');

// Menampilkan halaman tambah mata kuliah.
// Route ini harus berada sebelum /courses/{course}.
Route::get('/courses/create', [CourseController::class, 'create'])
    ->name('courses.create');

// Menampilkan detail satu mata kuliah.
// {course} digunakan sebagai parameter untuk menentukan mata kuliah yang dipilih.
Route::get('/courses/{course}', [CourseController::class, 'show'])
    ->name('courses.show');