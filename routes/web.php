<?php // Menandai awal file PHP.

use Illuminate\Support\Facades\Route; // Mengimpor facade Route Laravel.
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
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
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/dashboard', function () { // Membuat route dashboard.
    $currentUser = request()->user();
    $activeRole = $currentUser?->role ?? 'mahasiswa';
    $accessibleCourses = match ($activeRole) {
        'admin' => \App\Models\Course::query(),
        'dosen' => $currentUser ? $currentUser->taughtCourses() : \App\Models\Course::query(),
        'mahasiswa' => $currentUser ? $currentUser->courses() : \App\Models\Course::query(),
        default => \App\Models\Course::query(),
    };
    $courseIds = (clone $accessibleCourses)->select('courses.id');

    $courses = (clone $accessibleCourses)->with(['lecturer', 'students', 'materials', 'assignments']) // Mengambil course beserta relasi dashboard.
        ->where('status', 'active') // Membatasi course aktif.
        ->latest('courses.id') // Mengurutkan course terbaru lebih dulu.
        ->take(3) // Membatasi tiga course.
        ->get(); // Menjalankan query.

    if ($courses->isEmpty()) {
        $courses = (clone $accessibleCourses)->with(['lecturer', 'students', 'materials', 'assignments'])->latest('courses.id')->take(3)->get();
    }

    $allCourses = (clone $accessibleCourses)->with('lecturer')->orderBy('name')->get();
    $totalCourses = (clone $accessibleCourses)->count();
    $totalLecturers = (clone $accessibleCourses)->distinct()->count('lecturer_id');
    $totalStudents = \App\Models\Course::whereIn('courses.id', $courseIds)->with('students')->get()->flatMap->students->unique('id')->count();
    $totalAssignments = \App\Models\Assignment::whereIn('course_id', $courseIds)->count();
    $totalMaterials = \App\Models\Material::whereIn('course_id', $courseIds)->count();
    $totalSubmissions = \App\Models\Submission::whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))->count();

    $topStudents = \App\Models\User::where('role', 'mahasiswa')->whereHas('courses', fn ($q) => $q->whereIn('courses.id', $courseIds)) // Mahasiswa pada course yang dapat diakses.
        ->withCount(['submissions' => fn ($q) => $q->whereHas('assignment', fn ($a) => $a->whereIn('course_id', $courseIds))]) // Hanya menghitung pengumpulan pada course tersebut.
        ->take(4) // Membatasi empat mahasiswa.
        ->get() // Menjalankan query.
        ->map(function ($student, $index) { // Memproses hasil mahasiswa.
            $student->calculated_score = 96 - ($index * 4); // Membuat skor tampilan berdasarkan urutan.
            return $student; // Mengembalikan data mahasiswa.
        }); // Mengakhiri map.

    $homeworks = \App\Models\Assignment::with('course')->whereIn('course_id', $courseIds) // Hanya tugas dari course yang dapat diakses.
        ->when($activeRole === 'mahasiswa', fn ($q) => $q->where('status', 'published'))
        ->where('status', 'published') // Membatasi assignment published.
        ->orderBy('due_at', 'desc') // Mengurutkan berdasarkan deadline.
        ->take(3) // Membatasi tiga assignment.
        ->get(); // Menjalankan query.

    if ($homeworks->isEmpty()) { // Memeriksa apakah tidak ada homework published.
        $homeworks = \App\Models\Assignment::with('course')->whereIn('course_id', $courseIds) // Mengambil tugas dalam cakupan yang sama.
            ->when($activeRole === 'mahasiswa', fn ($q) => $q->where('status', 'published'))
            ->latest() // Mengurutkan terbaru.
            ->take(3) // Membatasi tiga data.
            ->get(); // Menjalankan query.
    } // Mengakhiri kondisi homework kosong.

    $upcomingSchedule = \App\Models\Assignment::with('course')->whereIn('course_id', $courseIds) // Jadwal hanya dari course yang dapat diakses.
        ->where('status', 'published') // Membatasi assignment published.
        ->where('due_at', '>=', now()) // Mengambil deadline yang belum lewat.
        ->orderBy('due_at', 'asc') // Mengurutkan dari deadline terdekat.
        ->take(2) // Membatasi dua jadwal.
        ->get(); // Menjalankan query.

    $urgentAssignment = \App\Models\Assignment::with('course')->whereIn('course_id', $courseIds) // Deadline course yang dapat diakses.
        ->where('status', 'published') // Membatasi assignment published.
        ->where('due_at', '>=', now()) // Mengambil deadline yang belum lewat.
        ->orderBy('due_at', 'asc') // Mengurutkan deadline terdekat.
        ->first(); // Mengambil assignment pertama.

    $lecturerCourses = $currentUser ? $currentUser->taughtCourses()->with(['materials', 'assignments', 'students'])->get() : collect();
    $recentUsers = $activeRole === 'admin' ? \App\Models\User::latest()->take(4)->get() : collect();

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

// Rute registrasi terpusat oleh Admin (Minggu 07)
Route::get('/register', function () {
    return redirect()->route('login')->with('info', 'Pendaftaran akun dilakukan secara terpusat oleh Administrator.');
})->name('register');

// Seluruh route di bawah ini membutuhkan pengguna yang sudah terautentikasi.
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Rute manajemen course: didefinisikan sebelum route model binding courses/{course}
    // agar URL /courses/create tidak tertangkap sebagai parameter {course} (mencegah 404).
    Route::middleware('role:admin,dosen')->group(function () {
        Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::match(['put', 'patch'], 'courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    });

    // Detail dan daftar mata kuliah umum
    Route::resource('courses', CourseController::class)
        ->only(['index', 'show']);

    // CRUD metadata untuk halaman Blade. Gate di controller tetap memeriksa
    // bahwa dosen hanya mengelola materi/tugas course yang ia ampu.
    Route::middleware('role:admin,dosen')->scopeBindings()->group(function () {
        Route::resource('courses.materials', \App\Http\Controllers\MaterialController::class)
            ->except(['show'])->shallow();
        Route::resource('courses.assignments', \App\Http\Controllers\AssignmentController::class)
            ->except(['show'])->shallow();
    });

    // Detail dapat dibuka semua role yang login; Policy memeriksa akses objek.
    Route::get('materials/{material}', [\App\Http\Controllers\MaterialController::class, 'show'])
        ->name('materials.show');
    Route::get('assignments/{assignment}', [\App\Http\Controllers\AssignmentController::class, 'show'])
        ->name('assignments.show');

    // Kompatibilitas URI lama untuk course management. Akses tetap dibatasi role
    // dan kepemilikan objek diperiksa lagi di CourseController.
    Route::middleware('role:admin')->group(function () {
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
            Route::post('courses/{course}/enrollments', [EnrollmentController::class, 'store'])
                ->name('courses.enrollments.store');
            Route::delete('courses/{course}/enrollments/{student}', [EnrollmentController::class, 'destroy'])
                ->name('courses.enrollments.destroy');
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
            Route::resource('courses', CourseController::class)->only(['index', 'show']);
            Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'index'])
                ->name('courses.enrollments.index');
            Route::post('courses/{course}/enrollments', [EnrollmentController::class, 'store'])
                ->name('courses.enrollments.store');
            Route::delete('courses/{course}/enrollments/{student}', [EnrollmentController::class, 'destroy'])
                ->name('courses.enrollments.destroy');
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

    Route::post('/submissions/{submission}/grade', [GradeController::class, 'store'])
        ->middleware('role:dosen')
        ->name('submissions.grade.store');
});
