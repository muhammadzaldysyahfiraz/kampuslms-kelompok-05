<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');

// Menampilkan halaman Dashboard dengan data statistik agregat
Route::get('/dashboard', function () {
    $courses = \App\Models\Course::with('lecturer')->latest()->take(3)->get();
    $totalCourses = \App\Models\Course::count();
    $totalLecturers = \App\Models\User::where('role', 'dosen')->count();
    $totalStudents = \App\Models\User::where('role', 'mahasiswa')->count();
    $totalAssignments = \App\Models\Assignment::count();

    return view('dashboard', compact(
        'courses',
        'totalCourses',
        'totalLecturers',
        'totalStudents',
        'totalAssignments'
    ));
})->name('dashboard');

// CRUD Resourceful Mata Kuliah (index, create, store, show, edit, update, destroy)
Route::resource('courses', CourseController::class);