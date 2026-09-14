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
        return [
            'course_id' => Course::inRandomOrder()->value('id'),

            'created_by' => User::where('role', 'dosen')
                ->inRandomOrder()
                ->value('id'),

            'title' => fake('id_ID')->sentence(3),

            'instructions' => fake('id_ID')->paragraph(),

            'due_at' => now()->addDays(7),

            'max_score' => 100,

            'allow_late' => true,

            'status' => 'published',
        ];
    }
}