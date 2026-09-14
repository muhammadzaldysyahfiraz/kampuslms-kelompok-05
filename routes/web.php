<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

// Menampilkan halaman Dashboard dengan data statistik agregat & widget lengkap (DESIGN.md Section 5.1 & image_013.jpg)
Route::get('/dashboard', function () {
    $courses = \App\Models\Course::with('lecturer')->latest()->take(3)->get();
    $allCourses = \App\Models\Course::all();
    $totalCourses = \App\Models\Course::count();
    $totalLecturers = \App\Models\User::where('role', 'dosen')->count();
    $totalStudents = \App\Models\User::where('role', 'mahasiswa')->count();
    $totalAssignments = \App\Models\Assignment::count();
    $students = \App\Models\User::where('role', 'mahasiswa')->take(5)->get();
    $homeworks = \App\Models\Assignment::with('course')->latest()->take(3)->get();

    return view('dashboard', compact(
        'courses',
        'allCourses',
        'totalCourses',
        'totalLecturers',
        'totalStudents',
        'totalAssignments',
        'students',
        'homeworks'
    ));
})->name('dashboard');

// CRUD Resourceful Mata Kuliah (index, create, store, show, edit, update, destroy)
Route::resource('courses', CourseController::class);

// CRUD Resourceful Manajemen Pengguna (F2 & M1 Deliverable)
Route::resource('users', UserController::class);