<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan master database seeder.
     *
     * Refaktor M13 (Peer Review Response):
     * Memecah proses seeding monolitik menjadi seeder modular berurutan
     * dengan optimasi bulk attach dan hash map lookup untuk efisiensi eksekusi.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CourseSeeder::class,
            AssignmentSeeder::class,
            SubmissionSeeder::class,
        ]);
    }
}
