<?php // Menandai awal file PHP.

namespace App\Http\Controllers; // Menentukan namespace controller.

use App\Models\Course; // Mengimpor model Course untuk mengakses tabel courses.
use App\Models\User; // Mengimpor model User untuk mengambil data dosen.
use App\Http\Requests\StoreCourseRequest; // Mengimpor Form Request untuk validasi saat menambah course.
use App\Http\Requests\UpdateCourseRequest; // Mengimpor Form Request untuk validasi saat mengedit course.
use Illuminate\Http\Request; // Mengimpor Request untuk membaca query string pencarian dan filter.

class CourseController extends Controller // Membuat controller untuk mengelola mata kuliah.
{
    // Menampilkan daftar mata kuliah dengan pencarian, filter, dan pagination.
    public function index(Request $request) // Menerima request agar parameter q dan status dari URL bisa dibaca.
    {
        $validated = $request->validate([ // Memvalidasi query string sebelum digunakan dalam query database.
            'q' => ['nullable', 'string', 'max:100'], // Kata pencarian boleh kosong, harus teks, dan maksimal 100 karakter.
            'status' => ['nullable', 'in:all,draft,active,archived'], // Status boleh kosong dan hanya boleh menggunakan pilihan yang ditentukan.
        ]); // Mengakhiri aturan validasi query string.

        $keyword = $validated['q'] ?? null; // Mengambil kata pencarian yang sudah lolos validasi.
        $status = $validated['status'] ?? 'all'; // Mengambil status dari URL atau memakai all sebagai nilai default.

        $query = Course::with('lecturer')->latest(); // Mengambil course beserta lecturer dengan eager loading dan mengurutkan dari terbaru.

        if (!empty($keyword)) { // Memeriksa apakah pengguna benar-benar memasukkan kata pencarian.
            $query->where(function ($query) use ($keyword) { // Membuat kelompok pencarian untuk code atau name.
                $query->where('code', 'like', "%{$keyword}%") // Mencari kata kunci pada kolom code menggunakan query builder Laravel.
                    ->orWhere('name', 'like', "%{$keyword}%"); // Mencari kata kunci pada kolom name menggunakan query builder Laravel.
            }); // Menutup kelompok kondisi pencarian.
        } // Menutup pengecekan kata pencarian.

        if ($status !== 'all') { // Memeriksa apakah pengguna memilih status tertentu.
            $query->where('status', $status); // Memfilter course berdasarkan status yang sudah divalidasi.
        } // Menutup pengecekan status.

        $courses = $query->paginate(15)->withQueryString(); // Membatasi 15 data per halaman dan mempertahankan parameter query string saat pindah halaman.

        $courseCounts = Course::query() // Membuat query terpisah untuk menghitung jumlah course berdasarkan status.
            ->selectRaw('status, COUNT(*) as total') // Mengambil status dan jumlah data untuk setiap status.
            ->groupBy('status') // Mengelompokkan hasil berdasarkan status course.
            ->pluck('total', 'status'); // Mengubah hasil menjadi pasangan status => jumlah agar mudah digunakan di Blade.

        $totalCourseCount = $courseCounts->sum(); // Menghitung jumlah seluruh course dari semua status.

        return view('courses.index', compact('courses', 'courseCounts', 'totalCourseCount')); // Mengirim data course dan statistik status ke halaman daftar.
    } // Menutup method index.

    // Menampilkan form tambah mata kuliah.
    public function create() // Menjalankan halaman form tambah course.
    {
        $lecturers = User::where('role', 'dosen')->get(); // Mengambil semua user yang memiliki role dosen.

        return view('courses.create', compact('lecturers')); // Mengirim daftar dosen ke halaman form.
    } // Menutup method create.

    // Menyimpan mata kuliah baru.
    public function store(StoreCourseRequest $request) // Menerima request yang sudah melewati validasi StoreCourseRequest.
    {
        $data = $request->validated(); // Mengambil hanya field yang lolos aturan validasi.

        Course::create($data); // Menyimpan data tervalidasi ke tabel courses.

        return redirect() // Membuat response redirect setelah data berhasil disimpan.
            ->route('courses.index') // Mengarahkan pengguna kembali ke daftar mata kuliah.
            ->with('success', 'Mata kuliah berhasil ditambahkan.'); // Menyimpan pesan sukses sebagai flash session.
    } // Menutup method store.

    // Menampilkan detail mata kuliah.
    public function show(Course $course) // Menerima course melalui route model binding.
    {
        $course->load(['lecturer', 'materials', 'assignments']); // Memuat relasi lecturer, materials, dan assignments untuk halaman detail.
        $course->loadCount('students'); // Menghitung jumlah mahasiswa yang terdaftar pada course.

        return view('courses.show', compact('course')); // Mengirim data course ke halaman detail.
    } // Menutup method show.

    // Menampilkan form edit.
    public function edit(Course $course) // Menerima course yang akan diedit melalui route model binding.
    {
        $lecturers = User::where('role', 'dosen')->get(); // Mengambil daftar user yang berperan sebagai dosen.

        return view('courses.edit', compact('course', 'lecturers')); // Mengirim course dan daftar dosen ke halaman edit.
    } // Menutup method edit.

    // Memperbarui mata kuliah.
    public function update(UpdateCourseRequest $request, Course $course) // Menerima request tervalidasi dan course yang akan diperbarui.
    {
        $course->update($request->validated()); // Memperbarui course hanya menggunakan data yang sudah lolos validasi.

        return redirect() // Membuat response redirect setelah update selesai.
            ->route('courses.show', $course) // Mengarahkan pengguna ke halaman detail course.
            ->with('success', 'Mata kuliah berhasil diperbarui.'); // Menyimpan pesan sukses sebagai flash session.
    } // Menutup method update.

    // Menghapus mata kuliah.
    public function destroy(Course $course) // Menerima course yang akan dihapus melalui route model binding.
    {
        $course->delete(); // Menghapus course dari database.

        return redirect() // Membuat response redirect setelah penghapusan.
            ->route('courses.index') // Mengarahkan kembali ke daftar course.
            ->with('success', 'Mata kuliah berhasil dihapus.'); // Menyimpan pesan sukses sebagai flash session.
    } // Menutup method destroy.
} // Menutup class CourseController.