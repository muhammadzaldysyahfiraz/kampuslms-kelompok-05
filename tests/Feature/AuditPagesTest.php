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
            'Home (redirect to dashboard)' => ['role' => 'student', 'user' => $student, 'url' => '/', 'expect' => 302],
            'Tentang' => ['role' => 'student', 'user' => $student, 'url' => '/tentang', 'expect' => 200],
            'Login' => ['role' => 'guest', 'user' => null, 'url' => '/login', 'expect' => 200],
            'Dashboard as Student' => ['role' => 'student', 'user' => $student, 'url' => '/dashboard', 'expect' => 200],
            'Dashboard as Lecturer' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => '/dashboard', 'expect' => 200],
            'Dashboard as Admin' => ['role' => 'admin', 'user' => $admin, 'url' => '/dashboard', 'expect' => 200],
            'Courses Index' => ['role' => 'student', 'user' => $student, 'url' => '/courses', 'expect' => 200],
            'Courses Show' => ['role' => 'student', 'user' => $student, 'url' => "/courses/{$course->id}", 'expect' => 200],
            'Courses Create as Lecturer' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => '/courses/create', 'expect' => 200],
            'Courses Edit as Lecturer' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/courses/{$course->id}/edit", 'expect' => 200],
            'Users Index as Admin' => ['role' => 'admin', 'user' => $admin, 'url' => '/users', 'expect' => 200],
            'Users Create as Admin' => ['role' => 'admin', 'user' => $admin, 'url' => '/users/create', 'expect' => 200],
            'Users Show as Admin' => ['role' => 'admin', 'user' => $admin, 'url' => "/users/{$student->id}", 'expect' => 200],
            'Users Edit as Admin' => ['role' => 'admin', 'user' => $admin, 'url' => "/users/{$student->id}/edit", 'expect' => 200],
            'Submission Detail as Student' => ['role' => 'student', 'user' => $student, 'url' => "/submissions/{$submission->id}", 'expect' => 200],
            
            // Nested Scoped URLs
            'Scoped Assignment URL' => ['role' => 'student', 'user' => $student, 'url' => "/courses/{$course->id}/assignments/{$assignment->id}", 'expect' => 200],
            'Scoped Material URL' => ['role' => 'student', 'user' => $student, 'url' => "/courses/{$course->id}/materials/{$material->id}", 'expect' => 200],

            // Assignment & Material Creation & Editing URLs
            'Dosen Assignment Create' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/dosen/courses/{$course->id}/assignments/create", 'expect' => 200],
            'Dosen Assignment Edit' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/dosen/assignments/{$assignment->id}/edit", 'expect' => 200],
            'Dosen Assignment Show' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/dosen/assignments/{$assignment->id}", 'expect' => 200],
            'Dosen Material Create' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/dosen/courses/{$course->id}/materials/create", 'expect' => 200],
            'Dosen Material Edit' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/dosen/materials/{$material->id}/edit", 'expect' => 200],
            'Dosen Material Show' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/dosen/materials/{$material->id}", 'expect' => 200],

            // Check if shallow/direct URLs return 404
            'Direct Assignment Show (/assignments/{id})' => ['role' => 'student', 'user' => $student, 'url' => "/assignments/{$assignment->id}", 'expect' => 200],
            'Direct Material Show (/materials/{id})' => ['role' => 'student', 'user' => $student, 'url' => "/materials/{$material->id}", 'expect' => 200],
            'Direct Assignment Create (/courses/{id}/assignments/create)' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/courses/{$course->id}/assignments/create", 'expect' => 200],
            'Direct Material Create (/courses/{id}/materials/create)' => ['role' => 'lecturer', 'user' => $lecturer, 'url' => "/courses/{$course->id}/materials/create", 'expect' => 200],
        ];

        echo "\n\n--- DETAILED AUDIT RESULTS ---\n";
        $this->actingAs($student)->get("/assignments/{$assignment->id}")->assertOk();
        foreach ($urls as $name => $item) {
            $req = $item['user'] ? $this->actingAs($item['user']) : $this;
            $response = $req->get($item['url']);
            $status = $response->getStatusCode();
            $contentType = $response->headers->get('content-type', '');
            $isJson = str_contains($contentType, 'json');
            
            $statusStr = "$status";
            if ($status === 404) {
                $statusStr .= " [404 NOT FOUND!]";
            } elseif ($status === 500) {
                $statusStr .= " [500 SERVER ERROR!]";
            } elseif ($isJson) {
                $statusStr .= " [JSON RESPONSE, NOT BLADE HTML!]";
            } else {
                $statusStr .= " [OK HTML VIEW]";
            }
            
            echo sprintf("%-45s | %s\n", $name, $statusStr);
        }
        echo "------------------------------\n\n";

        $this->assertTrue(true);
    }
}
