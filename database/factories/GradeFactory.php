<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    public function definition(): array
    {
        $feedbacks = [
            'Pekerjaan sangat rapi dan memenuhi seluruh kriteria spesifikasi. Analisis dan implementasi terstruktur dengan baik. Pertahankan!',
            'Implementasi kode bersih dan mengikuti konvensi penamaan yang tepat. Penanganan validasi input sudah lengkap.',
            'Penyusunan laporan komprehensif dan mudah dipahami. Hasil pengujian menunjukkan seluruh fungsi bekerja sesuai harapan.',
            'Konsep yang diterapkan sudah tepat. Catatan perbaikan: perhatikan kerapian penulisan komentar kode pada pertemuan berikutnya.',
            'Sangat baik! Arsitektur perancangan menunjukkan pemahaman mendalam terhadap materi perkuliahan.',
            'Hasil pengujian fungsi valid. Dokumentasi diagram sistem tersusun sistematis dan rapi.',
        ];

        return [
            'submission_id' => fn () => Submission::inRandomOrder()->value('id') ?? Submission::factory(),
            'graded_by' => fn (array $attributes) => Submission::find($attributes['submission_id'])?->assignment?->course?->lecturer_id ?? (User::where('role', 'dosen')->inRandomOrder()->value('id') ?? User::factory()->create(['role' => 'dosen'])->id),
            'score' => fake()->numberBetween(75, 100),
            'feedback' => fake()->randomElement($feedbacks),
            'graded_at' => now(),
        ];
    }
}