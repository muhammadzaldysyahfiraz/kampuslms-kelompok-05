<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubmissionSeeder extends Seeder
{
    /**
     * Jalankan seeder pengumpulan tugas (submission) dan penilaian (grade).
     *
     * Refaktor M13 (Optimasi Hash Map Lookup & Modularisasi):
     * 1. Mengganti pencarian linier O(N) contains() dengan Hash Map asosiatif O(1)
     *    isset($submissionMap["{$assignId}_{$studentId}"]) untuk eliminasi overhead CPU.
     * 2. Menjaga kontrak data spesifikasi: Tepat 125 submission, submission 1 milik demo,
     *    isolasi MK 3-5 untuk uji IDOR submissions/79 pada test-authz.sh, dan >= 70 grade.
     */
    public function run(): void
    {
        $dosenDemo = User::where('email', 'dosen@kampuslms.test')->firstOrFail();
        $mahasiswaDemo = User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail();

        $courses = Course::orderBy('id')->get();
        $assignments = Assignment::orderBy('id')->get();

        // Peta assignment_id => lecturer_id untuk grading bebas lazy-loading
        $assignmentLecturerMap = [];
        foreach ($courses as $course) {
            foreach ($assignments->where('course_id', $course->id) as $assignment) {
                $assignmentLecturerMap[$assignment->id] = $course->lecturer_id;
            }
        }

        $submissions = collect();

        // Optimasi M13: Hash Map Lookup O(1) untuk mencegah duplikasi unique(['assignment_id', 'user_id'])
        $submissionMap = [];

        /*
        |--------------------------------------------------------------------------
        | SUBMISSION 1: Wajib milik $mahasiswaDemo pada MK 1 Tugas 1
        |--------------------------------------------------------------------------
        */
        $submDemo = new Submission();
        $submDemo->assignment_id = $assignments[0]->id;
        $submDemo->user_id = $mahasiswaDemo->id;
        $submDemo->file_path = "submissions/demo-mahasiswa-tugas-1-" . Str::uuid() . ".pdf";
        $submDemo->original_name = "Laporan_Tugas1_Web_10241000.pdf";
        $submDemo->file_size = 1048576;
        $submDemo->submitted_at = now()->subDays(11);
        $submDemo->is_late = false;
        $submDemo->note = 'Tugas 1 Pemrograman Web telah selesai diimplementasikan sesuai seluruh kriteria.';
        $submDemo->save();

        $submissions->push($submDemo);
        $submissionMap["{$assignments[0]->id}_{$mahasiswaDemo->id}"] = true;

        /*
        |--------------------------------------------------------------------------
        | GENERASI SISA 124 SUBMISSION (TOTAL 125 SUBMISSION)
        |--------------------------------------------------------------------------
        */
        $sampleNotes = [
            'Berikut laporan tugas telah diselesaikan dan diuji pada lingkungan lokal.',
            'Pengumpulan berkas penugasan tepat waktu, seluruh requirement terpenuhi.',
            'Tugas telah direview dan seluruh unit testing lulus tanpa error.',
            'Mohon feedback dan masukan untuk pengembangan tugas berikutnya, Pak.',
            'Kode sumber dan dokumentasi telah diunggah sesuai format.',
        ];

        foreach ($courses as $courseIndex => $course) {
            $courseAssignments = $assignments->where('course_id', $course->id)->values();
            $targetCount = ($courseIndex === 0) ? 24 : 25;

            $enrolledStudents = $course->students()->get();

            $createdForCourse = 0;
            while ($createdForCourse < $targetCount) {
                $assignment = $courseAssignments[array_rand($courseAssignments->all())];

                // PENTING UNTUK UJI IDOR (submissions/79):
                // Pada MK 3, 4, dan 5 ($courseIndex >= 2), kecualikan $mahasiswaDemo
                // agar Submission ID 79 dipastikan bukan milik $mahasiswaDemo!
                if ($courseIndex >= 2) {
                    $candidateStudents = $enrolledStudents->where('id', '!=', $mahasiswaDemo->id)->values();
                } else {
                    $candidateStudents = $enrolledStudents;
                }

                $student = $candidateStudents[array_rand($candidateStudents->all())];

                // Pengecekan O(1) instan via Hash Map
                $mapKey = "{$assignment->id}_{$student->id}";
                if (isset($submissionMap[$mapKey])) {
                    continue;
                }

                $isLate = (random_int(1, 100) <= 20); // 20% kemungkinan terlambat
                $submittedAt = $isLate
                    ? (clone $assignment->due_at)->addHours(random_int(2, 48))
                    : (clone $assignment->due_at)->subHours(random_int(2, 72));

                $subm = new Submission();
                $subm->assignment_id = $assignment->id;
                $subm->user_id = $student->id;
                $subm->file_path = "submissions/" . Str::uuid() . ".pdf";
                $subm->original_name = "Tugas_{$assignment->id}_{$student->nim_nip}.pdf";
                $subm->file_size = random_int(450000, 2500000);
                $subm->submitted_at = $submittedAt;
                $subm->is_late = $isLate;
                $subm->note = $sampleNotes[array_rand($sampleNotes)];
                $subm->save();

                $submissions->push($subm);
                $submissionMap[$mapKey] = true;
                $createdForCourse++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PENILAIAN (GRADE) ±60% SUBMISSION (>= 70 GRADES)
        |--------------------------------------------------------------------------
        */
        $sampleFeedbacks = [
            'Struktur kode sangat rapi, pemisahan concern arsitektur terjaga dengan baik.',
            'Implementasi sudah memenuhi spesifikasi, pertahankan konsistensi dokumentasi.',
            'Hasil pengujian memuaskan, seluruh validasi masukan ditangani dengan benar.',
            'Pekerjaan luar biasa! Solusi yang diterapkan sangat elegan dan efisien.',
            'Secara umum bagus, perhatikan kembali optimasi query pada relasi bersarang.',
        ];

        $numberOfGrades = (int) floor($submissions->count() * 0.60); // 75 grade
        $submissionsToGrade = $submissions->shuffle()->take($numberOfGrades);

        // Pastikan Submission 1 milik demo pasti mendapat nilai
        if (!$submissionsToGrade->contains('id', $submDemo->id)) {
            $submissionsToGrade->push($submDemo);
        }

        foreach ($submissionsToGrade as $submission) {
            $graderId = $assignmentLecturerMap[$submission->assignment_id] ?? $dosenDemo->id;

            $grade = new Grade();
            $grade->submission_id = $submission->id;
            $grade->graded_by = $graderId;
            $grade->score = random_int(80, 100);
            $grade->feedback = $sampleFeedbacks[array_rand($sampleFeedbacks)];
            $grade->graded_at = now()->subDays(random_int(1, 5));
            $grade->save();
        }
    }
}
