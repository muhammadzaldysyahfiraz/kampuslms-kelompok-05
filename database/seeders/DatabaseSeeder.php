<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. ADMIN
        |--------------------------------------------------------------------------
        */

        User::factory()->create([
            'name' => 'Admin KampusLMS',
            'email' => 'admin@kampuslms.test',
            'password' => 'password',
            'role' => 'admin',
            'nim_nip' => 'ADM001',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. DOSEN
        |--------------------------------------------------------------------------
        */

        $dosen = User::factory()
            ->count(3)
            ->create([
                'role' => 'dosen',
            ]);

        /*
        |--------------------------------------------------------------------------
        | 3. MAHASISWA
        |--------------------------------------------------------------------------
        */

        $mahasiswa = User::factory()
            ->count(30)
            ->create([
                'role' => 'mahasiswa',
            ]);

        /*
        |--------------------------------------------------------------------------
        | AKUN DEMO WAJIB
        |--------------------------------------------------------------------------
        */

        $dosenDemo = $dosen->first();
        $dosenDemo->update([
            'name' => 'Dosen Demo',
            'email' => 'dosen@kampuslms.test',
            'nim_nip' => 'DOS001',
        ]);

        $mahasiswaDemo = $mahasiswa->first();
        $mahasiswaDemo->update([
            'name' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@kampuslms.test',
            'nim_nip' => 'MHS001',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. 5 MATA KULIAH
        |--------------------------------------------------------------------------
        */

        $courses = Course::factory()
            ->count(5)
            ->create();

        /*
        |--------------------------------------------------------------------------
        | 5. SETIAP MK MINIMAL 15 MAHASISWA
        |--------------------------------------------------------------------------
        */

        foreach ($courses as $course) {
            $students = $mahasiswa->random(15);

            foreach ($students as $student) {
                $course->students()->attach($student->id, [
                    'enrolled_at' => now(),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 6. 3 TUGAS UNTUK SETIAP MK
        |--------------------------------------------------------------------------
        */

        foreach ($courses as $course) {

            // Tugas sudah lewat deadline
            $pastAssignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'created_by' => $course->lecturer_id,
                'title' => 'Tugas Pertemuan 1',
                'due_at' => now()->subDays(7),
                'status' => 'published',
            ]);

            // Tugas yang masih aktif
            $activeAssignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'created_by' => $course->lecturer_id,
                'title' => 'Tugas Pertemuan 2',
                'due_at' => now()->addDays(7),
                'status' => 'published',
            ]);

            // Tugas draft
            $draftAssignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'created_by' => $course->lecturer_id,
                'title' => 'Tugas Akhir',
                'due_at' => now()->addDays(30),
                'status' => 'draft',
            ]);
        }

/*
|--------------------------------------------------------------------------
| 6.5. MATERIAL UNTUK SETIAP MK
|--------------------------------------------------------------------------
*/

foreach ($courses as $course) {

    $course->materials()->createMany([
        [
            'uploaded_by' => $course->lecturer_id,
            'title' => 'Materi Pertemuan 1',
            'description' => 'Materi pembelajaran pertemuan pertama.',
            'type' => 'link',
            'file_path' => null,
            'original_name' => null,
            'file_size' => null,
            'mime_type' => null,
            'external_url' => 'https://example.com/materi-1',
        ],
        [
            'uploaded_by' => $course->lecturer_id,
            'title' => 'Materi Pertemuan 2',
            'description' => 'Materi pembelajaran pertemuan kedua.',
            'type' => 'link',
            'file_path' => null,
            'original_name' => null,
            'file_size' => null,
            'mime_type' => null,
            'external_url' => 'https://example.com/materi-2',
        ],
    ]);
}


        /*
        |--------------------------------------------------------------------------
        | 7. MINIMAL 100 SUBMISSION
        |--------------------------------------------------------------------------
        |
        | Kita membuat 25 submission per mata kuliah.
        | 5 MK x 25 = 125 submission.
        |
        */

        $submissions = collect();

        foreach ($courses as $course) {

            // Ambil mahasiswa yang benar-benar terdaftar
            $students = $course->students()->get();

            // Ambil tugas pada MK tersebut
            $assignments = $course->assignments()->get();

            for ($i = 0; $i < 25; $i++) {

                $student = $students->random();
                $assignment = $assignments->random();

                // Hindari submission ganda
                if (
                    Submission::where('assignment_id', $assignment->id)
                        ->where('user_id', $student->id)
                        ->exists()
                ) {
                    $i--;
                    continue;
                }

                // Sebagian submission dibuat terlambat
                $isLate = fake()->boolean(30);

                $submittedAt = $isLate
                    ? $assignment->due_at->copy()->addDays(1)
                    : $assignment->due_at->copy()->subDays(1);

                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                    'submitted_at' => $submittedAt,
                    'is_late' => $isLate,
                ]);

                $submissions->push($submission);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 8. NILAI ±60% SUBMISSION
        |--------------------------------------------------------------------------
        */

        $numberOfGrades = (int) floor($submissions->count() * 0.60);

        $submissionsToGrade = $submissions
            ->random($numberOfGrades);

        foreach ($submissionsToGrade as $submission) {

            Grade::factory()->create([
                'submission_id' => $submission->id,
                'graded_by' => $submission->assignment->course->lecturer_id,
                'score' => fake()->numberBetween(60, 100),
                'feedback' => fake('id_ID')->sentence(),
                'graded_at' => now(),
            ]);
        }
    }
}