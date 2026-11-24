<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
class MaterialFactory extends Factory
{
    public function definition(): array
    {
        $materials = [
            [
                'title' => 'Pertemuan 1: Pengenalan Arsitektur & Teori Dasar',
                'description' => 'Materi pengantar perkuliahan, silabus semester, pengenalan konsep dasar, dan instalasi perangkat lunak pendukung pembelajaran.',
            ],
            [
                'title' => 'Pertemuan 2: Implementasi Praktis & Studi Kasus',
                'description' => 'Pembahasan modul implementasi, best practices pengembangan, studi kasus industri, dan panduan latihan laboratorium.',
            ],
            [
                'title' => 'Pertemuan 3: Evaluasi Keamanan & Optimasi Sistem',
                'description' => 'Pendalaman aspek keamanan data, standarisasi pengujian fungsionalitas, serta teknik optimasi kinerja sistem aplikasi.',
            ],
        ];

        $selected = fake()->randomElement($materials);

        return [
            'course_id' => fn () => Course::inRandomOrder()->value('id') ?? Course::factory(),
            'uploaded_by' => fn (array $attributes) => Course::find($attributes['course_id'])?->lecturer_id ?? (User::where('role', 'dosen')->inRandomOrder()->value('id') ?? User::factory()->create(['role' => 'dosen'])->id),
            'title' => $selected['title'],
            'description' => $selected['description'],
            'type' => 'file',
            'file_path' => 'materials/' . fake()->uuid() . '.pdf',
            'original_name' => 'materi-kuliah.pdf',
            'file_size' => fake()->numberBetween(150000, 2500000),
            'mime_type' => 'application/pdf',
            'external_url' => null,
        ];
    }
}