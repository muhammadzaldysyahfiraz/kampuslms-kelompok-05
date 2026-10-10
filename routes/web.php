<?php // Menandai awal file PHP.

use Illuminate\Support\Facades\Route; // Mengimpor facade Route Laravel.
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController; // Mengimpor CourseController.
use App\Http\Controllers\UserController; // Mengimpor UserController.
use App\Http\Controllers\SubmissionController; // Mengimpor SubmissionController untuk route submission.
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () { // Membuat route halaman utama.
    return redirect()->route('dashboard'); // Mengarahkan halaman utama ke dashboard.
})->name('home'); // Memberikan nama home pada route utama.

Route::get('/tentang', function () { // Membuat route halaman tentang.
    return view('tentang'); // Mengembalikan view tentang.
})->name('tentang'); // Memberikan nama tentang pada route.

// Autentikasi Pengguna Sesi Web (Pekan 07)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Simulasi login cepat lokal untuk praktikum (lingkungan dev).
Route::get('/switch-role/{role}', function (string $role) {
    abort_unless(app()->environment('local'), 404);

    $validRoles = ['mahasiswa', 'dosen', 'admin'];
    abort_unless(in_array($role, $validRoles, true), 404);

    $user = \App\Models\User::where('role', $role)->first();
    abort_unless($user, 404, 'Akun untuk role tersebut belum tersedia di database.');

    \Illuminate\Support\Facades\Auth::login($user);
    request()->session()->regenerate();
    session(['active_role' => $role]);

    return redirect()->route('dashboard');
})->name('switch-role');

Route::get('/dashboard', function () { // Membuat route dashboard.
    $activeRole = session('active_role', 'mahasiswa'); // Mengambil role aktif dari session dengan default mahasiswa.

    $courses = \App\Models\Course::with(['lecturer', 'students', 'materials', 'assignments']) // Mengambil course beserta relasi dashboard.
        ->where('status', 'active') // Membatasi course aktif.
        ->latest() // Mengurutkan course terbaru lebih dulu.
        ->take(3) // Membatasi tiga course.
        ->get(); // Menjalankan query.

    if ($courses->isEmpty()) { // Memeriksa apakah tidak ada course aktif.
        $courses = \App\Models\Course::with(['lecturer', 'students', 'materials', 'assignments']) // Mengambil course beserta relasi lagi.
            ->latest() // Mengurutkan dari terbaru.
            ->take(3) // Membatasi tiga course.
            ->get(); // Menjalankan query.
    } // Mengakhiri kondisi course kosong.

    $allCourses = \App\Models\Course::orderBy('name')->get(); // Mengambil semua course berdasarkan nama.
    $totalCourses = \App\Models\Course::count(); // Menghitung jumlah seluruh course.
    $totalLecturers = \App\Models\User::where('role', 'dosen')->count(); // Menghitung jumlah dosen.
    $totalStudents = \App\Models\User::where('role', 'mahasiswa')->count(); // Menghitung jumlah mahasiswa.
    $totalAssignments = \App\Models\Assignment::count(); // Menghitung jumlah assignment.
    $totalMaterials = \App\Models\Material::count(); // Menghitung jumlah material.
    $totalSubmissions = \App\Models\Submission::count(); // Menghitung jumlah submission.

    $topStudents = \App\Models\User::where('role', 'mahasiswa') // Mengambil mahasiswa.
        ->withCount('submissions') // Menghitung submission mahasiswa.
        ->take(4) // Membatasi empat mahasiswa.
        ->get() // Menjalankan query.
        ->map(function ($student, $index) { // Memproses hasil mahasiswa.
            $student->calculated_score = 96 - ($index * 4); // Membuat skor tampilan berdasarkan urutan.
            return $student; // Mengembalikan data mahasiswa.
        }); // Mengakhiri map.

    $homeworks = \App\Models\Assignment::with('course') // Mengambil assignment beserta course.
        ->where('status', 'published') // Membatasi assignment published.
        ->orderBy('due_at', 'desc') // Mengurutkan berdasarkan deadline.
        ->take(3) // Membatasi tiga assignment.
        ->get(); // Menjalankan query.

    if ($homeworks->isEmpty()) { // Memeriksa apakah tidak ada homework published.
        $homeworks = \App\Models\Assignment::with('course') // Mengambil assignment beserta course lagi.
            ->latest() // Mengurutkan terbaru.
            ->take(3) // Membatasi tiga data.
            ->get(); // Menjalankan query.
    } // Mengakhiri kondisi homework kosong.

    $upcomingSchedule = \App\Models\Assignment::with('course') // Mengambil assignment beserta course untuk jadwal.
        ->where('status', 'published') // Membatasi assignment published.
        ->where('due_at', '>=', now()) // Mengambil deadline yang belum lewat.
        ->orderBy('due_at', 'asc') // Mengurutkan dari deadline terdekat.
        ->take(2) // Membatasi dua jadwal.
        ->get(); // Menjalankan query.

    $urgentAssignment = \App\Models\Assignment::with('course') // Mengambil assignment untuk notifikasi.
        ->where('status', 'published') // Membatasi assignment published.
        ->where('due_at', '>=', now()) // Mengambil deadline yang belum lewat.
        ->orderBy('due_at', 'asc') // Mengurutkan deadline terdekat.
        ->first(); // Mengambil assignment pertama.

    if ($activeRole === 'dosen') { // Memeriksa role dosen.
        $currentUser = \App\Models\User::where('role', 'dosen')->first(); // Mengambil user dosen pertama.
    } elseif ($activeRole === 'admin') { // Memeriksa role admin.
        $currentUser = \App\Models\User::where('role', 'admin')->first(); // Mengambil user admin pertama.
    } else { // Menangani mahasiswa atau role lain.
        $currentUser = \App\Models\User::where('role', 'mahasiswa')->first(); // Mengambil mahasiswa pertama.
    } // Mengakhiri percabangan role.

    $currentUser = $currentUser ?? (object)[ // Menyediakan fallback user jika data tidak ditemukan.
        'name' => 'Muhammad Rifa Al-Rizqul', // Nama fallback.
        'email' => '10241050@student.itk.ac.id', // Email fallback.
        'role' => $activeRole, // Menentukan role fallback.
        'nim_nip' => '10241050' // NIM/NIP fallback.
    ]; // Menutup data fallback.

    $lecturerCourses = \App\Models\Course::where('lecturer_id', $currentUser->id ?? 0)->with(['materials', 'assignments', 'students'])->get(); // Mengambil course yang diampu user aktif.
    if ($lecturerCourses->isEmpty() && $activeRole === 'dosen') { // Memeriksa fallback course untuk dosen.
        $lecturerCourses = \App\Models\Course::take(2)->with(['materials', 'assignments', 'students'])->get(); // Mengambil dua course sebagai fallback.
    } // Mengakhiri kondisi fallback dosen.

    $recentUsers = \App\Models\User::latest()->take(4)->get(); // Mengambil empat user terbaru.

    return view('dashboard', compact( // Mengirim data dashboard ke view.
        'courses', // Mengirim course.
        'allCourses', // Mengirim semua course.
        'totalCourses', // Mengirim total course.
        'totalLecturers', // Mengirim total dosen.
        'totalStudents', // Mengirim total mahasiswa.
        'totalAssignments', // Mengirim total assignment.
        'totalMaterials', // Mengirim total material.
        'totalSubmissions', // Mengirim total submission.
        'topStudents', // Mengirim leaderboard mahasiswa.
        'homeworks', // Mengirim homework.
        'upcomingSchedule', // Mengirim jadwal.
        'urgentAssignment', // Mengirim deadline terdekat.
        'currentUser', // Mengirim user aktif.
        'lecturerCourses', // Mengirim course dosen.
        'recentUsers', // Mengirim user terbaru.
        'activeRole' // Mengirim role aktif.
    )); // Menutup pengiriman data view.
})->name('dashboard'); // Memberikan nama dashboard.

// Seluruh route di bawah ini membutuhkan pengguna yang sudah terautentikasi.
Route::middleware('auth')->group(function () {
    // Dashboard umum, dengan role tampilan yang dipilih pada simulasi lokal.
    // Daftar/detail course umum tetap memakai nama route lama agar tautan dashboard stabil.
    Route::resource('courses', CourseController::class)
        ->only(['index', 'show']);

    // Kompatibilitas URI lama untuk course management. Akses tetap dibatasi role
    // dan kepemilikan objek diperiksa lagi di CourseController.
    Route::middleware('role:admin,dosen')->group(function () {
        Route::resource('courses', CourseController::class)
            ->except(['index', 'show']);
    });

    // Kompatibilitas route pengguna lama; tetap hanya admin yang dapat mengakses.
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    // AREA ADMIN: pengelolaan pengguna dan seluruh course.
    Route::prefix('admin')->name('admin.')
        ->middleware('role:admin')->group(function () {
            Route::resource('users', UserController::class);
            Route::resource('courses', CourseController::class);
            Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'index'])
                ->name('courses.enrollments.index');
            Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
            Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
            Route::get('grades', [GradeController::class, 'indexAdmin'])->name('grades.index');
            Route::get('grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
            Route::resource('notifications', NotificationController::class)->only(['index', 'show', 'destroy']);
            Route::patch('notifications/{notification}/read', [NotificationController::class, 'read'])
                ->name('notifications.read');

            Route::scopeBindings()->group(function () {
                Route::resource('courses.materials', \App\Http\Controllers\MaterialController::class)
                    ->shallow();
                Route::resource('courses.assignments', \App\Http\Controllers\AssignmentController::class)
                    ->shallow();
            });
        });

    // AREA DOSEN: course yang dikelola dibatasi lagi berdasarkan lecturer_id
    // di CourseController; nested resources menggunakan scoped model binding.
    Route::prefix('dosen')->name('dosen.')
        ->middleware('role:dosen')->group(function () {
            Route::resource('courses', CourseController::class);
            Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'index'])
                ->name('courses.enrollments.index');
            Route::get('courses/{course}/grades', [GradeController::class, 'indexForCourse'])
                ->name('courses.grades.index');
            Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
            Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
            Route::post('submissions/{submission}/grade', [GradeController::class, 'store'])
                ->name('submissions.grade.store');
            Route::get('grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
            Route::match(['put', 'patch'], 'grades/{grade}', [GradeController::class, 'update'])
                ->name('grades.update');
            Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');
            Route::resource('notifications', NotificationController::class)->only(['index', 'show', 'destroy']);
            Route::patch('notifications/{notification}/read', [NotificationController::class, 'read'])
                ->name('notifications.read');

            Route::scopeBindings()->group(function () {
                Route::resource('courses.materials', \App\Http\Controllers\MaterialController::class)
                    ->shallow();
                Route::resource('courses.assignments', \App\Http\Controllers\AssignmentController::class)
                    ->shallow();
            });
        });

    // AREA MAHASISWA: hanya daftar/detail course dan materi/tugas.
    Route::prefix('mahasiswa')->name('mahasiswa.')
        ->middleware('role:mahasiswa')->group(function () {
            Route::resource('courses', CourseController::class)
                ->only(['index', 'show']);
            Route::post('courses/{course}/enrollments', [EnrollmentController::class, 'store'])
                ->name('courses.enrollments.store');
            Route::delete('courses/{course}/enrollments', [EnrollmentController::class, 'destroy'])
                ->name('courses.enrollments.destroy');
            Route::get('submissions', [SubmissionController::class, 'index'])->name('submissions.index');
            Route::get('submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
            Route::get('grades', [GradeController::class, 'indexMine'])->name('grades.index');
            Route::get('grades/{grade}', [GradeController::class, 'show'])->name('grades.show');
            Route::resource('notifications', NotificationController::class)->only(['index', 'show', 'destroy']);
            Route::patch('notifications/{notification}/read', [NotificationController::class, 'read'])
                ->name('notifications.read');

            Route::scopeBindings()->group(function () {
                Route::resource('courses.materials', \App\Http\Controllers\MaterialController::class)
                    ->only(['index', 'show'])->shallow();
                Route::resource('courses.assignments', \App\Http\Controllers\AssignmentController::class)
                    ->only(['index', 'show'])->shallow();
            });
        });

    // Route nested eksplisit untuk memperagakan scopeBindings() pada URL
    // /courses/{course}/assignments/{assignment} dan materi.
    Route::scopeBindings()->group(function () {
        Route::get('/courses/{course}/assignments/{assignment}', [
            \App\Http\Controllers\AssignmentController::class, 'showNested',
        ])->middleware('role:admin,dosen,mahasiswa')
            ->name('courses.assignments.scoped-show');

        Route::get('/courses/{course}/materials/{material}', [
            \App\Http\Controllers\MaterialController::class, 'showNested',
        ])->middleware('role:admin,dosen,mahasiswa')
            ->name('courses.materials.scoped-show');
    });

    // Submission detail: mahasiswa pemilik, dosen pengampu, atau admin.
    Route::get('/submissions/{submission}', [
        SubmissionController::class, 'show',
    ])->middleware('role:mahasiswa,dosen,admin')
        ->name('submissions.show');
});
