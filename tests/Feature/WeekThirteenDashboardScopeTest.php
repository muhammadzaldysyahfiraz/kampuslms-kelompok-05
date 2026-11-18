<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeekThirteenDashboardScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_lecturer_without_courses_does_not_see_other_lecturers_submissions_on_dashboard(): void
    {
        $lecturerA = User::factory()->create(['role' => 'dosen']);
        $lecturerB = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Mahasiswa Rahasia Kelas Lain']);

        $courseA = Course::factory()->create([
            'lecturer_id' => $lecturerA->id,
            'name' => 'Pemrograman Web Rahasia',
            'status' => 'active',
        ]);
        $courseA->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignmentA = Assignment::factory()->create([
            'course_id' => $courseA->id,
            'created_by' => $lecturerA->id,
            'title' => 'Tugas Rahasia Dosen A',
            'status' => 'published',
        ]);

        Submission::factory()->create([
            'assignment_id' => $assignmentA->id,
            'user_id' => $student->id,
        ]);

        $response = $this->actingAs($lecturerB)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Tugas Rahasia Dosen A');
        $response->assertDontSee('Mahasiswa Rahasia Kelas Lain');
    }

    public function test_student_does_not_see_draft_assignments_on_dashboard(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Draft Tugas Belum Terbit',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Draft Tugas Belum Terbit');
    }
}
