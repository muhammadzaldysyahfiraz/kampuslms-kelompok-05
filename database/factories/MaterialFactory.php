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

            'title' => fake('id_ID')->sentence(4),

            'description' => fake('id_ID')->paragraph(),

            'type' => 'file',

            'file_path' => 'materials/' . fake()->uuid() . '.pdf',

            'original_name' => 'materi.pdf',

            'file_size' => fake()->numberBetween(10000, 5000000),

            'mime_type' => 'application/pdf',

            'external_url' => null,
        ];
    }
}