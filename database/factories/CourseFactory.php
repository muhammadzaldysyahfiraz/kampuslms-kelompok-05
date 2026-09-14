<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'SI' . fake()->unique()->numerify('#######'),

            'name' => fake()->randomElement([
                'Pemrograman Web',
                'Basis Data',
                'Jaringan Komputer',
                'Sistem Informasi',
                'Analisis dan Perancangan Sistem',
            ]),

            'description' => fake('id_ID')->paragraph(),

            'sks' => fake()->numberBetween(2, 4),

            'lecturer_id' => User::where('role', 'dosen')
                ->inRandomOrder()
                ->value('id'),

            'status' => 'active',
        ];
    }
}