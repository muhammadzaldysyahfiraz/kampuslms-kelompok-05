<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class CourseController extends Controller
{
    // Menyediakan data mata kuliah sementara dalam bentuk array.
    // Array digunakan karena database belum digunakan pada tahap ini.
    private function courses(): array
    {
        return [
            1 => [
                'name' => 'Pemrograman Web',
                'code' => 'SI2514024',
                'sks' => 3,
                'lecturer' => 'Dr. Budi Santoso',
            ],

            2 => [
                'name' => 'Basis Data',
                'code' => 'SI2514012',
                'sks' => 3,
                'lecturer' => 'Siti Rahma, M.Kom.',
            ],

            3 => [
                'name' => 'Analisis dan Perancangan Sistem',
                'code' => 'SI2514031',
                'sks' => 3,
                'lecturer' => 'Andi Pratama, M.Kom.',
            ],
        ];
    }

    // Menampilkan seluruh daftar mata kuliah.
    // Data diambil dari method courses() kemudian dikirim ke view index.
    public function index(): View
    {
        $courses = $this->courses();

        return view('courses.index', compact('courses'));
    }

    // Menampilkan detail satu mata kuliah berdasarkan ID dari URL.
    // Parameter $course berasal dari {course} pada route.
    public function show(string $course): View
    {
        $courses = $this->courses();

        // Memastikan ID yang diminta tersedia dalam data.
        // Jika tidak ditemukan, Laravel memberikan response 404.
        abort_unless(isset($courses[$course]), 404);

        // Mengirim data satu mata kuliah ke view detail.
        return view('courses.show', [
            'course' => $courses[$course],
        ]);
    }
}