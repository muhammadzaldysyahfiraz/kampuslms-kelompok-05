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

        $admin = User::factory()->create([
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
        | AKUN DOSEN DEMO
        |--------------------------------------------------------------------------
        */

        $dosenDemo = $dosen->first();

        $dosenDemo->update([
            'name' => 'Dosen Demo',
            'email' => 'dosen@kampuslms.test',
            'nim_nip' => 'DOS001',
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
        | AKUN MAHASISWA DEMO
        |--------------------------------------------------------------------------
        */

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
        | 6. 3 TUGAS PER MATA KULIAH
        |--------------------------------------------------------------------------
        */

        foreach ($courses as $course) {

            // Tugas sudah lewat deadline
            Assignment::factory()->create([
                'course_id' => $course->id,
                'created_by' => $course->lecturer_id,
                'title' => 'Tugas Pertemuan 1',
                'due_at' => now()->subDays(7),
                'status' => 'published',
            ]);

            // Tugas masih aktif
            Assignment::factory()->create([
                'course_id' => $course->id,
                'created_by' => $course->lecturer_id,
                'title' => 'Tugas Pertemuan 2',
                'due_at' => now()->addDays(7),
                'status' => 'published',
            ]);

            // Tugas draft
            Assignment::factory()->create([
                'course_id' => $course->id,
                'created_by' => $course->lecturer_id,
                'title' => 'Tugas Akhir',
                'due_at' => now()->addDays(30),
                'status' => 'draft',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. SUBMISSION
        |--------------------------------------------------------------------------
        |
        | 5 MK x 25 submission = 125 submission.
        | Submission hanya berasal dari mahasiswa yang terdaftar
        | pada mata kuliah tersebut.
        |
        */

        $submissions = collect();

        foreach ($courses as $course) {

            // Mahasiswa yang terdaftar di MK ini
            $students = $course->students()->get();

            // Tugas yang dimiliki MK ini
            $assignments = $course->assignments()->get();

            $created = 0;

            while ($created < 25) {

                $student = $students->random();
                $assignment = $assignments->random();

                /*
                | Hindari submission ganda.
                | Database juga memiliki unique:
                | assignment_id + user_id
                */

                $exists = Submission::where('assignment_id', $assignment->id)
                    ->where('user_id', $student->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                /*
                | Sekitar 30% submission terlambat.
                */

                $isLate = fake()->boolean(30);

                if ($isLate) {

                    $submittedAt = $assignment->due_at
                        ->copy()
                        ->addDays(1);

                } else {

                    $submittedAt = $assignment->due_at
                        ->copy()
                        ->subDays(1);
                }

                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                    'submitted_at' => $submittedAt,
                    'is_late' => $isLate,
                ]);

                $submissions->push($submission);

                $created++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 8. NILAI ±60% SUBMISSION
        |--------------------------------------------------------------------------
        */

        $numberOfGrades = (int) floor(
            $submissions->count() * 0.60
        );

        /*
        | Ambil submission secara acak untuk diberi nilai.
        */

        $submissionsToGrade = $submissions
            ->random($numberOfGrades);

        foreach ($submissionsToGrade as $submission) {

            Grade::factory()->create([
                'submission_id' => $submission->id,

                // Nilai diberikan oleh dosen pengampu MK
                'graded_by' => $submission
                    ->assignment
                    ->course
                    ->lecturer_id,

                'score' => fake()->numberBetween(60, 100),

                'feedback' => fake('id_ID')->sentence(),

                'graded_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. MATERIAL
        |--------------------------------------------------------------------------
        |
        | Data tambahan materi untuk setiap mata kuliah.
        |
        */

        foreach ($courses as $course) {

            $course->materials()->create([
                'uploaded_by' => $course->lecturer_id,
                'title' => 'Materi Pertemuan 1',
                'description' => 'Materi pembelajaran pertemuan pertama.',
                'type' => 'file',
                'file_path' => 'materials/materi-pertemuan-1.pdf',
                'original_name' => 'materi-pertemuan-1.pdf',
                'file_size' => 500000,
                'mime_type' => 'application/pdf',
                'external_url' => null,
            ]);
        }
    }
}