<?php

namespace Tests\Feature;

use App\Http\Controllers\GradeController;
use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class WeekThirteenPeerReviewFixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_enrolled_student_is_denied_view_course_grades_even_when_calling_controller_directly(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $this->assertFalse(Gate::forUser($student)->allows('viewCourseGrades', $course));
        $this->assertTrue(Gate::forUser($lecturer)->allows('viewCourseGrades', $course));

        $request = Request::create('/dosen/courses/' . $course->id . '/grades', 'GET');
        $request->setUserResolver(fn () => $student);
        $this->actingAs($student);

        $this->expectException(AuthorizationException::class);
        app(GradeController::class)->indexForCourse($request, $course);
    }

    public function test_admin_and_course_lecturer_see_gradebook_button_but_other_lecturer_does_not(): void
    {
        $owner = User::factory()->create(['role' => 'dosen']);
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $admin = User::factory()->create(['role' => 'admin']);
        $course = Course::factory()->create(['lecturer_id' => $owner->id]);

        $this->actingAs($owner)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Rekap Nilai Kelas');

        $this->actingAs($admin)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Rekap Nilai Kelas');

        $this->actingAs($otherLecturer)
            ->get(route('courses.show', $course))
            ->assertForbidden();
    }

    public function test_navbar_renders_policy_scoped_links_for_each_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($admin)
            ->get(route('tentang'))
            ->assertOk()
            ->assertSee('Pengguna')
            ->assertDontSee('Nilai Saya');

        $this->actingAs($student)
            ->get(route('tentang'))
            ->assertOk()
            ->assertSee('Nilai Saya')
            ->assertDontSee('Pengguna');
    }
}
