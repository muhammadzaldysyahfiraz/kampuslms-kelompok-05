<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeekTenGradebookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_student_can_view_own_grade_transcript_page(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');

        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'name' => 'Pemrograman Web Lanjut',
            'code' => 'IF3020',
        ]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'title' => 'Tugas 1: Blade Components',
            'max_score' => 100,
            'due_at' => now()->addDays(2),
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 95,
            'feedback' => 'Implementasi sangat rapi dan sesuai standar Swiss Academic.',
        ]);

        $response = $this->actingAs($student)->get(route('mahasiswa.grades.index'));

        $response->assertOk();
        $response->assertViewIs('grades.mine');
        $response->assertSee('Rekap Nilai Saya');
        $response->assertSee('Pemrograman Web Lanjut');
        $response->assertSee('Tugas 1: Blade Components');
        $response->assertSee('95');
        $response->assertSee('Implementasi sangat rapi dan sesuai standar Swiss Academic.');
        $response->assertSee('Tepat Waktu');
        $response->assertSee('Nilai Saya'); // Navbar link
    }

    public function test_student_grade_transcript_api_returns_json(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');

        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
        Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 88,
        ]);

        $response = $this->actingAs($student)
            ->getJson(route('mahasiswa.grades.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'grades' => [
                'data' => [
                    '*' => ['id', 'submission_id', 'score'],
                ],
            ],
        ]);
    }

    public function test_lecturer_can_view_course_gradebook_matrix_page(): void
    {
        $lecturer = $this->createUser('dosen');
        $studentA = $this->createUser('mahasiswa');
        $studentB = $this->createUser('mahasiswa');

        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'name' => 'Rekayasa Perangkat Lunak',
            'code' => 'IF2010',
        ]);
        $course->students()->attach([$studentA->id, $studentB->id], ['enrolled_at' => now()]);

        $assignment1 = Assignment::factory()->create([
            'course_id' => $course->id,
            'title' => 'Tugas Mandiri 1',
            'max_score' => 100,
        ]);
        $assignment2 = Assignment::factory()->create([
            'course_id' => $course->id,
            'title' => 'Tugas Proyek Akhir',
            'max_score' => 100,
        ]);

        // Student A: submitted assignment 1 with grade
        $subA1 = Submission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $studentA->id,
            'is_late' => false,
        ]);
        Grade::factory()->create([
            'submission_id' => $subA1->id,
            'graded_by' => $lecturer->id,
            'score' => 90,
        ]);

        // Student B: submitted assignment 1 but not graded yet
        $subB1 = Submission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $studentB->id,
            'is_late' => true,
        ]);

        $response = $this->actingAs($lecturer)->get(route('dosen.courses.grades.index', $course));

        $response->assertOk();
        $response->assertViewIs('grades.course');
        $response->assertSee('Rekap Nilai Kelas: Rekayasa Perangkat Lunak');
        $response->assertSee($studentA->name);
        $response->assertSee($studentB->name);
        $response->assertSee('Tugas Mandiri 1');
        $response->assertSee('Tugas Proyek Akhir');
        $response->assertSee('90');
        $response->assertSee('Beri Nilai'); // Quick action for student B's ungraded submission
    }

    public function test_lecturer_course_gradebook_api_returns_json(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');

        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create(['course_id' => $course->id]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
        Grade::factory()->create([
            'submission_id' => $submission->id,
            'graded_by' => $lecturer->id,
            'score' => 85,
        ]);

        $response = $this->actingAs($lecturer)
            ->getJson(route('dosen.courses.grades.index', $course));

        $response->assertOk();
        $response->assertJsonStructure([
            'course' => ['id', 'name'],
            'grades' => [
                'data' => [
                    '*' => ['id', 'submission_id', 'score'],
                ],
            ],
        ]);
    }

    public function test_other_lecturer_cannot_view_course_gradebook_403(): void
    {
        $lecturerOwner = $this->createUser('dosen');
        $otherLecturer = $this->createUser('dosen');

        $course = Course::factory()->create(['lecturer_id' => $lecturerOwner->id]);

        $this->actingAs($otherLecturer)
            ->get(route('dosen.courses.grades.index', $course))
            ->assertForbidden();
    }

    public function test_student_cannot_view_lecturer_course_gradebook_403(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');

        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $this->actingAs($student)
            ->get(route('dosen.courses.grades.index', $course))
            ->assertForbidden();
    }

    public function test_course_show_page_displays_gradebook_link_for_course_lecturer(): void
    {
        $lecturer = $this->createUser('dosen');
        $student = $this->createUser('mahasiswa');

        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'name' => 'Basis Data',
        ]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        // Lecturer pengampu can see the button
        $responseLecturer = $this->actingAs($lecturer)->get(route('courses.show', $course));
        $responseLecturer->assertOk();
        $responseLecturer->assertSee('Rekap Nilai Kelas');
        $responseLecturer->assertSee(route('dosen.courses.grades.index', $course));

        // Mahasiswa cannot see the lecturer gradebook button on course show
        $responseStudent = $this->actingAs($student)->get(route('courses.show', $course));
        $responseStudent->assertOk();
        $responseStudent->assertDontSee(route('dosen.courses.grades.index', $course));
    }
}

