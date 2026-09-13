<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::inRandomOrder()->value('id'),
            'user_id' => User::where('role', 'mahasiswa')->inRandomOrder()->value('id'),
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'original_name' => 'tugas.pdf',
            'file_size' => fake()->numberBetween(10000, 5000000),
            'note' => fake('id_ID')->optional()->sentence(),
            'submitted_at' => now(),
            'is_late' => false,
        ];
    }
}