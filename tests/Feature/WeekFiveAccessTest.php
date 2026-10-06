<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeekFiveAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function courseFor(User $lecturer): Course
    {
        return Course::factory()->create([
            'lecturer_id' => $lecturer->id,
        ]);
    }

    private function assignmentFor(Course $course, User $creator): Assignment
    {
        return Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $creator->id,
        ]);
    }

    private function submissionFor(Assignment $assignment, User $student): Submission
    {
        return Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_guest_gets_401_for_protected_course_routes(): void
    {
        $this->getJson('/courses')->assertUnauthorized();
    }

    public function test_student_cannot_open_course_management_form(): void
    {
        $student = $this->userWithRole('mahasiswa');
        $course = $this->courseFor($this->userWithRole('dosen'));

        $this->actingAs($student)
            ->getJson("/courses/{$course->id}/edit")
            ->assertForbidden();
    }

    public function test_lecturer_cannot_edit_another_lecturers_course(): void
    {
        $lecturerA = $this->userWithRole('dosen');
        $lecturerB = $this->userWithRole('dosen');
        $courseB = $this->courseFor($lecturerB);

        $this->actingAs($lecturerA)
            ->putJson("/courses/{$courseB->id}", [
                'code' => 'SI9999001',
                'name' => 'Course tidak boleh diubah',
                'description' => 'Percobaan IDOR',
                'sks' => 3,
                'lecturer_id' => $lecturerB->id,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('courses', [
            'id' => $courseB->id,
            'name' => $courseB->name,
            'lecturer_id' => $lecturerB->id,
        ]);
    }

    public function test_lecturer_can_edit_own_course(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $course = $this->courseFor($lecturer);

        $this->actingAs($lecturer)
            ->put("/courses/{$course->id}", [
                'code' => 'SI9999002',
                'name' => 'Course dosen sendiri',
                'description' => 'Perubahan yang diizinkan',
                'sks' => 3,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ])
            ->assertRedirect(route('courses.show', $course));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Course dosen sendiri',
            'lecturer_id' => $lecturer->id,
        ]);
    }

    public function test_lecturer_cannot_assign_new_course_to_another_lecturer(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $otherLecturer = $this->userWithRole('dosen');

        $this->actingAs($lecturer)
            ->post('/courses', [
                'code' => 'SI9999003',
                'name' => 'Course baru dosen',
                'description' => 'Uji kepemilikan',
                'sks' => 3,
                'lecturer_id' => $otherLecturer->id,
                'status' => 'active',
            ])
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', [
            'code' => 'SI9999003',
            'lecturer_id' => $lecturer->id,
        ]);
    }

    public function test_student_can_view_own_submission_but_not_another_students_submission(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $studentA = $this->userWithRole('mahasiswa');
        $studentB = $this->userWithRole('mahasiswa');
        $course = $this->courseFor($lecturer);
        $assignment = $this->assignmentFor($course, $lecturer);
        $submissionA = $this->submissionFor($assignment, $studentA);
        $submissionB = $this->submissionFor($assignment, $studentB);

        $this->actingAs($studentA)
            ->get("/submissions/{$submissionA->id}")
            ->assertOk()
            ->assertSee($assignment->title);

        $this->actingAs($studentA)
            ->getJson("/submissions/{$submissionB->id}")
            ->assertForbidden();
    }

    public function test_course_lecturer_and_admin_can_view_submission(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $admin = $this->userWithRole('admin');
        $student = $this->userWithRole('mahasiswa');
        $course = $this->courseFor($lecturer);
        $assignment = $this->assignmentFor($course, $lecturer);
        $submission = $this->submissionFor($assignment, $student);

        $this->actingAs($lecturer)
            ->get("/submissions/{$submission->id}")
            ->assertOk();

        $this->actingAs($admin)
            ->get("/submissions/{$submission->id}")
            ->assertOk();
    }

    public function test_nested_assignment_binding_rejects_assignment_from_another_course(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');
        $courseA = $this->courseFor($lecturer);
        $courseB = $this->courseFor($lecturer);
        $assignmentB = $this->assignmentFor($courseB, $lecturer);

        $this->actingAs($student)
            ->getJson("/courses/{$courseA->id}/assignments/{$assignmentB->id}")
            ->assertNotFound();

        $this->actingAs($student)
            ->getJson("/courses/{$courseB->id}/assignments/{$assignmentB->id}")
            ->assertOk()
            ->assertJsonPath('assignment.id', $assignmentB->id);
    }

    public function test_only_admin_can_manage_users(): void
    {
        $student = $this->userWithRole('mahasiswa');

        $this->actingAs($student)
            ->getJson('/users')
            ->assertForbidden();

        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk();
    }
}
