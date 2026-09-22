<?php // Menandai awal file PHP.

use Illuminate\Support\Facades\Route; // Mengimpor facade Route Laravel.
use App\Http\Controllers\CourseController; // Mengimpor CourseController.
use App\Http\Controllers\UserController; // Mengimpor UserController.

Route::get('/', function () { // Membuat route halaman utama.
    return redirect()->route('dashboard'); // Mengarahkan halaman utama ke dashboard.
})->name('home'); // Memberikan nama home pada route utama.

Route::get('/tentang', function () { // Membuat route halaman tentang.
    return view('tentang'); // Mengembalikan view tentang.
})->name('tentang'); // Memberikan nama tentang pada route.

Route::get('/switch-role/{role}', function ($role) { // Membuat route untuk mengganti role simulasi.
    $validRoles = ['mahasiswa', 'dosen', 'admin', 'all']; // Menentukan daftar role yang boleh dipakai.
    if (in_array($role, $validRoles)) { // Memeriksa apakah role yang diterima valid.
        session(['active_role' => $role]); // Menyimpan role aktif ke session.
    } // Mengakhiri pengecekan role.
    return redirect()->back(); // Mengembalikan pengguna ke halaman sebelumnya.
})->name('switch-role'); // Memberikan nama route switch-role.

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
        'role' => $activeRole === 'all' ? 'mahasiswa' : $activeRole, // Menentukan role fallback.
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

Route::resource('courses', CourseController::class); // Membuat seluruh route CRUD courses termasuk GET /courses menuju index().
Route::resource('users', UserController::class); // Membuat seluruh route CRUD users.