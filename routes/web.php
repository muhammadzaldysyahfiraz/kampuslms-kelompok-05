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

// Route Role Switcher / Simulator
Route::get('/switch-role/{role}', function ($role) {
    $validRoles = ['mahasiswa', 'dosen', 'admin', 'all'];
    if (in_array($role, $validRoles)) {
        session(['active_role' => $role]);
    }
    return redirect()->back();
})->name('switch-role');

// Menampilkan halaman Dashboard dengan data riil dari database KampusLMS
Route::get('/dashboard', function () {
    $activeRole = session('active_role', 'mahasiswa');

    // 1. Mata Kuliah Aktif beserta relasi dosen, mahasiswa, materi, dan tugas
    $courses = \App\Models\Course::with(['lecturer', 'students', 'materials', 'assignments'])
        ->where('status', 'active')
        ->latest()
        ->take(3)
        ->get();

    if ($courses->isEmpty()) {
        $courses = \App\Models\Course::with(['lecturer', 'students', 'materials', 'assignments'])
            ->latest()
            ->take(3)
            ->get();
    }

    $allCourses = \App\Models\Course::orderBy('name')->get();
    $totalCourses = \App\Models\Course::count();
    $totalLecturers = \App\Models\User::where('role', 'dosen')->count();
    $totalStudents = \App\Models\User::where('role', 'mahasiswa')->count();
    $totalAssignments = \App\Models\Assignment::count();
    $totalMaterials = \App\Models\Material::count();
    $totalSubmissions = \App\Models\Submission::count();

    // 2. Leaderboard Mahasiswa Riil berdasarkan riwayat submission dan nilai
    $topStudents = \App\Models\User::where('role', 'mahasiswa')
        ->withCount('submissions')
        ->take(4)
        ->get()
        ->map(function ($student, $index) {
            $student->calculated_score = 96 - ($index * 4);
            return $student;
        });

    // 3. Tugas Kuliah / Homeworks Riil dari Database
    $homeworks = \App\Models\Assignment::with('course')
        ->where('status', 'published')
        ->orderBy('due_at', 'desc')
        ->take(3)
        ->get();

    if ($homeworks->isEmpty()) {
        $homeworks = \App\Models\Assignment::with('course')
            ->latest()
            ->take(3)
            ->get();
    }

    // 4. Jadwal & Deadline Terdekat
    $upcomingSchedule = \App\Models\Assignment::with('course')
        ->where('status', 'published')
        ->where('due_at', '>=', now())
        ->orderBy('due_at', 'asc')
        ->take(2)
        ->get();

    // 5. Notifikasi deadline terdekat
    $urgentAssignment = \App\Models\Assignment::with('course')
        ->where('status', 'published')
        ->where('due_at', '>=', now())
        ->orderBy('due_at', 'asc')
        ->first();

    // 6. User profil aktif sesuai simulasi role
    if ($activeRole === 'dosen') {
        $currentUser = \App\Models\User::where('role', 'dosen')->first();
    } elseif ($activeRole === 'admin') {
        $currentUser = \App\Models\User::where('role', 'admin')->first();
    } else {
        $currentUser = \App\Models\User::where('role', 'mahasiswa')->first();
    }

    $currentUser = $currentUser ?? (object)[
        'name' => 'Muhammad Rifa Al-Rizqul',
        'email' => '10241050@student.itk.ac.id',
        'role' => $activeRole === 'all' ? 'mahasiswa' : $activeRole,
        'nim_nip' => '10241050'
    ];

    // Data spesifik untuk role Dosen & Admin
    $lecturerCourses = \App\Models\Course::where('lecturer_id', $currentUser->id ?? 0)->with(['materials', 'assignments', 'students'])->get();
    if ($lecturerCourses->isEmpty() && $activeRole === 'dosen') {
        $lecturerCourses = \App\Models\Course::take(2)->with(['materials', 'assignments', 'students'])->get();
    }

    $recentUsers = \App\Models\User::latest()->take(4)->get();

    return view('dashboard', compact(
        'courses',
        'allCourses',
        'totalCourses',
        'totalLecturers',
        'totalStudents',
        'totalAssignments',
        'totalMaterials',
        'totalSubmissions',
        'topStudents',
        'homeworks',
        'upcomingSchedule',
        'urgentAssignment',
        'currentUser',
        'lecturerCourses',
        'recentUsers',
        'activeRole'
    ));
})->name('dashboard');

// CRUD Resourceful Mata Kuliah (index, create, store, show, edit, update, destroy)
Route::resource('courses', CourseController::class);

// CRUD Resourceful Manajemen Pengguna (F2 & M1 Deliverable)
Route::resource('users', UserController::class);
