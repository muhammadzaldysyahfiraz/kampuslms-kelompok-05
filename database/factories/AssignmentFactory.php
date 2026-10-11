<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        $assignments = [
            [
                'title' => 'Tugas 1: Analisis Kebutuhan & Skema Sistem',
                'instructions' => 'Susun analisis kebutuhan sistem informasi dan rancang skema data relasional sesuai studi kasus. Lampirkan berkas laporan dalam format PDF.',
            ],
            [
                'title' => 'Tugas 2: Implementasi Kontrol Akses & Keamanan',
                'instructions' => 'Terapkan mekanisme otorisasi berbasis hak akses peran (RBAC) dan mitigasi celah keamanan IDOR pada endpoint aplikasi. Uji fungsionalitas menggunakan unit test.',
            ],
            [
                'title' => 'Tugas 3: Integrasi Antarmuka & Layanan API',
                'instructions' => 'Kembangkan antarmuka pengguna responsif dan hubungkan dengan layanan RESTful API. Sertakan dokumentasi endpoint dan instruksi pengujian sistem.',
            ],
        ];

        $selected = fake()->randomElement($assignments);

        return [
            'course_id' => Course::inRandomOrder()->value('id'),
            'created_by' => User::where('role', 'dosen')
                ->inRandomOrder()
                ->value('id'),
            'title' => $selected['title'],
            'instructions' => $selected['instructions'],
            'due_at' => now()->addDays(7),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ];
    }
}