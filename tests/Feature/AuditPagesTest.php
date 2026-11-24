<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function createData(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $otherLecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $material = Material::factory()->create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'type' => 'link',
            'external_url' => 'https://example.com',
        ]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'status' => 'published',
            'due_at' => now()->addDays(7),
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        return compact('admin', 'lecturer', 'otherLecturer', 'student', 'otherStudent', 'course', 'material', 'assignment', 'submission');
    }

    public function test_pages_audit(): void
    {
        $data = $this->createData();
        extract($data);

        $urls = [
            'Home (redirect to dashboard)' => ['user' => $student, 'url' => '/', 'expect' => 302],
            'Tentang' => ['user' => $student, 'url' => '/tentang', 'expect' => 200],
            'Login (guest)' => ['user' => null, 'url' => '/login', 'expect' => 200],
            'Dashboard as Student' => ['user' => $student, 'url' => '/dashboard', 'expect' => 200],
            'Dashboard as Lecturer' => ['user' => $lecturer, 'url' => '/dashboard', 'expect' => 200],
            'Dashboard as Admin' => ['user' => $admin, 'url' => '/dashboard', 'expect' => 200],
            'Courses Index' => ['user' => $student, 'url' => '/courses', 'expect' => 200],
            'Courses Show' => ['user' => $student, 'url' => "/courses/{$course->id}", 'expect' => 200],
            'Courses Create as Admin' => ['user' => $admin, 'url' => '/courses/create', 'expect' => 200],
            'Courses Edit as Admin' => ['user' => $admin, 'url' => "/courses/{$course->id}/edit", 'expect' => 200],
            'Courses Create Forbidden for Lecturer' => ['user' => $lecturer, 'url' => '/courses/create', 'expect' => 403],
            'Courses Edit Forbidden for Lecturer' => ['user' => $lecturer, 'url' => "/courses/{$course->id}/edit", 'expect' => 403],
            'Users Index as Admin' => ['user' => $admin, 'url' => '/users', 'expect' => 200],
            'Users Create as Admin' => ['user' => $admin, 'url' => '/users/create', 'expect' => 200],
            'Users Show as Admin' => ['user' => $admin, 'url' => "/users/{$student->id}", 'expect' => 200],
            'Users Edit as Admin' => ['user' => $admin, 'url' => "/users/{$student->id}/edit", 'expect' => 200],
            'Submission Detail as Student' => ['user' => $student, 'url' => "/submissions/{$submission->id}", 'expect' => 200],

            // Nested Scoped URLs
            'Scoped Assignment URL' => ['user' => $student, 'url' => "/courses/{$course->id}/assignments/{$assignment->id}", 'expect' => 200],
            'Scoped Material URL' => ['user' => $student, 'url' => "/courses/{$course->id}/materials/{$material->id}", 'expect' => 200],

            // Assignment & Material Creation & Editing URLs
            'Dosen Assignment Create' => ['user' => $lecturer, 'url' => "/dosen/courses/{$course->id}/assignments/create", 'expect' => 200],
            'Dosen Assignment Edit' => ['user' => $lecturer, 'url' => "/dosen/assignments/{$assignment->id}/edit", 'expect' => 200],
            'Dosen Assignment Show' => ['user' => $lecturer, 'url' => "/dosen/assignments/{$assignment->id}", 'expect' => 200],
            'Dosen Material Create' => ['user' => $lecturer, 'url' => "/dosen/courses/{$course->id}/materials/create", 'expect' => 200],
            'Dosen Material Edit' => ['user' => $lecturer, 'url' => "/dosen/materials/{$material->id}/edit", 'expect' => 200],
            'Dosen Material Show' => ['user' => $lecturer, 'url' => "/dosen/materials/{$material->id}", 'expect' => 200],

            // Direct & Shallow URLs
            'Direct Assignment Show (/assignments/{id})' => ['user' => $student, 'url' => "/assignments/{$assignment->id}", 'expect' => 200],
            'Direct Material Show (/materials/{id})' => ['user' => $student, 'url' => "/materials/{$material->id}", 'expect' => 200],
            'Direct Assignment Create (/courses/{id}/assignments/create)' => ['user' => $lecturer, 'url' => "/courses/{$course->id}/assignments/create", 'expect' => 200],
            'Direct Material Create (/courses/{id}/materials/create)' => ['user' => $lecturer, 'url' => "/courses/{$course->id}/materials/create", 'expect' => 200],
        ];

        foreach ($urls as $name => $item) {
            if ($item['user']) {
                $response = $this->actingAs($item['user'])->get($item['url']);
            } else {
                $this->app['auth']->forgetGuards();
                $response = $this->get($item['url']);
            }

            $response->assertStatus(
                $item['expect'],
                "Gagal pada rute audit [{$name}] ({$item['url']})"
            );
        }
    }
}
