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
        return [
            'submission_id' => Submission::inRandomOrder()->value('id'),
            'graded_by' => User::where('role', 'dosen')->inRandomOrder()->value('id'),
            'score' => fake()->numberBetween(60, 100),
            'feedback' => fake('id_ID')->sentence(),
            'graded_at' => now(),
        ];
    }
}