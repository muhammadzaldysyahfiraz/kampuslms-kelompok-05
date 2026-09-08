<?php

namespace App\Http\Controllers;

class CourseController extends Controller
{
    // Menampilkan daftar mata kuliah.
    // Data dibuat dalam array sementara karena database belum digunakan.
    public function index()
    {
        $courses = [
            [
                'id' => 1,
                'code' => 'SI101',
                'name' => 'Pemrograman Web',
                'sks' => 3,
                'lecturer' => 'Aidil Saputra Kirsan',
            ],
            [
                'id' => 2,
                'code' => 'SI102',
                'name' => 'Kecerdasan Bisnis',
                'sks' => 3,
                'lecturer' => 'Dosen Kecerdasan Bisnis',
            ],
            [
                'id' => 3,
                'code' => 'SI103',
                'name' => 'Perencanaan Arsitektur Teknologi Informasi',
                'sks' => 3,
                'lecturer' => 'Dosen PATI',
            ],
        ];

        // Mengirim data courses ke view index agar dapat ditampilkan sebagai daftar.
        return view('courses.index', compact('courses'));
    }

    // Menampilkan halaman untuk membuat mata kuliah baru.
    // Digunakan sementara untuk menguji perbedaan route spesifik dan route dinamis.
    public function create()
    {
        return 'Halaman Tambah Mata Kuliah';
    }

    // Menampilkan detail satu mata kuliah berdasarkan ID.
    // Data masih menggunakan array statis karena database belum digunakan.
    public function show($course)
    {
        $courses = [
            1 => [
                'id' => 1,
                'code' => 'SI101',
                'name' => 'Pemrograman Web',
                'sks' => 3,
                'lecturer' => 'Aidil Saputra Kirsan',
            ],
            2 => [
                'id' => 2,
                'code' => 'SI102',
                'name' => 'Kecerdasan Bisnis',
                'sks' => 3,
                'lecturer' => 'Dosen Kecerdasan Bisnis',
            ],
            3 => [
                'id' => 3,
                'code' => 'SI103',
                'name' => 'Perencanaan Arsitektur Teknologi Informasi',
                'sks' => 3,
                'lecturer' => 'Dosen PATI',
            ],
        ];

        // Menghentikan proses dengan 404 jika ID mata kuliah tidak ditemukan.
        abort_unless(isset($courses[$course]), 404);

        // Mengirim data mata kuliah yang dipilih ke view detail.
        return view('courses.show', [
            'course' => $courses[$course],
        ]);
    }
}