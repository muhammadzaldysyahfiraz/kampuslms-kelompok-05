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
        return [
            'course_id' => Course::inRandomOrder()->value('id'),

            'uploaded_by' => User::where('role', 'dosen')
                ->inRandomOrder()
                ->value('id'),

            'title' => fake('id_ID')->randomElement([
                'Materi Pertemuan 1',
                'Materi Pertemuan 2',
                'Materi Pembelajaran',
            ]),

            'description' => fake('id_ID')->paragraph(),

            'type' => 'link',

            'file_path' => null,
            'original_name' => null,
            'file_size' => null,
            'mime_type' => null,

            'external_url' => 'https://example.com/materi',
        ];
    }
}