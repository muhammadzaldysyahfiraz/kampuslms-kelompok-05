<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WeekSixApiTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => 'password',
        ], $attributes));
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function courseFor(User $lecturer): Course
    {
        return Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
    }

    private function assignmentFor(Course $course, User $creator, array $attributes = []): Assignment
    {
        return Assignment::factory()->create(array_merge([
            'course_id' => $course->id,
            'created_by' => $creator->id,
            'status' => 'published',
            'max_score' => 100,
            'allow_late' => true,
            'due_at' => now()->addDays(7),
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Autentikasi & Sanctum Token
    |--------------------------------------------------------------------------
    */

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/v1/courses')->assertUnauthorized();
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_login_successful_and_returns_token_without_leaking_sensitive_data(): void
    {
        $user = $this->userWithRole('dosen', [
            'email' => 'dosen.api@kampuslms.test',
            'nim_nip' => 'DOS-API-001',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'dosen.api@kampuslms.test',
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'nim_nip', 'role'],
                ],
            ]);

        $responseData = $response->json('data.user');
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('remember_token', $responseData);
        $this->assertEquals('dosen', $responseData['role']);
    }

    public function test_login_prevents_user_enumeration(): void
    {
        $this->userWithRole('mahasiswa', ['email' => 'mhs.registered@kampuslms.test']);

        // Kasus 1: Email ada, password salah
        $resWrongPassword = $this->postJson('/api/v1/auth/login', [
            'email' => 'mhs.registered@kampuslms.test',
            'password' => 'wrong-pass',
        ]);

        // Kasus 2: Email tidak ada sama sekali
        $resWrongEmail = $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@kampuslms.test',
            'password' => 'wrong-pass',
        ]);

        $resWrongPassword->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Email atau kata sandi salah.');

        $resWrongEmail->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Email atau kata sandi salah.');
    }

    public function test_me_endpoint_returns_authenticated_user_resource(): void
    {
        $user = $this->userWithRole('mahasiswa', [
            'name' => 'Budi Mahasiswa',
            'email' => 'budi@kampuslms.test',
            'nim_nip' => '10241099',
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => 'Budi Mahasiswa',
                    'email' => 'budi@kampuslms.test',
                    'nim_nip' => '10241099',
                    'role' => 'mahasiswa',
                ],
            ]);
    }

    private function resetAuth(): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = $this->userWithRole('dosen');
        $token = $user->createToken('test')->plainTextToken;

        // Logout
        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        // Token sudah hangus di database, reset memory guard agar memicu autentikasi ulang
        $this->resetAuth();

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Courses Scoping, Access & Meta Pagination
    |--------------------------------------------------------------------------
    */

    public function test_courses_index_scoped_by_user_role(): void
    {
        $lecturerA = $this->userWithRole('dosen');
        $lecturerB = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');

        $courseA = $this->courseFor($lecturerA);
        $courseB = $this->courseFor($lecturerB);

        // Mahasiswa terdaftar di Course A
        $courseA->students()->attach($student->id, ['enrolled_at' => now()]);

        // Dosen A hanya melihat course miliknya
        $tokenLecturerA = $lecturerA->createToken('test')->plainTextToken;
        $resDosen = $this->withToken($tokenLecturerA)
            ->getJson('/api/v1/courses')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'code', 'name', 'sks', 'status', 'lecturer', 'counts',
                    ],
                ],
                'meta',
            ]);

        $dosenCourseIds = collect($resDosen->json('data'))->pluck('id')->all();
        $this->assertContains($courseA->id, $dosenCourseIds);
        $this->assertNotContains($courseB->id, $dosenCourseIds);

        // Mahasiswa hanya melihat course yang ia ikuti
        $tokenStudent = $student->createToken('test')->plainTextToken;
        $resMhs = $this->withToken($tokenStudent)
            ->getJson('/api/v1/courses')
            ->assertOk();

        $mhsCourseIds = collect($resMhs->json('data'))->pluck('id')->all();
        $this->assertContains($courseA->id, $mhsCourseIds);
        $this->assertNotContains($courseB->id, $mhsCourseIds);
    }

    public function test_courses_show_accessible_and_forbidden_for_non_enrolled_student(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $studentEnrolled = $this->userWithRole('mahasiswa');
        $studentOther = $this->userWithRole('mahasiswa');

        $course = $this->courseFor($lecturer);
        $course->students()->attach($studentEnrolled->id, ['enrolled_at' => now()]);

        // Mahasiswa terdaftar boleh mengakses detail course
        $tokenEnrolled = $studentEnrolled->createToken('test')->plainTextToken;
        $this->withToken($tokenEnrolled)
            ->getJson("/api/v1/courses/{$course->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $course->id)
            ->assertJsonPath('data.counts.materials', 0);

        // Mahasiswa lain yang TIDAK terdaftar ditolak 403 Forbidden
        $this->resetAuth();
        $tokenOther = $studentOther->createToken('test')->plainTextToken;
        $this->withToken($tokenOther)
            ->getJson("/api/v1/courses/{$course->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Anda tidak memiliki akses ke sumber daya ini.');
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Assignments Management (RBAC & IDOR)
    |--------------------------------------------------------------------------
    */

    public function test_student_cannot_create_assignment(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $course = $this->courseFor($lecturer);
        $student = $this->userWithRole('mahasiswa');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $tokenStudent = $student->createToken('test')->plainTextToken;
        $this->withToken($tokenStudent)
            ->postJson('/api/v1/assignments', [
                'course_id' => $course->id,
                'title' => 'Tugas Ilegal',
                'instructions' => 'Instruksi',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'max_score' => 100,
            ])
            ->assertForbidden();
    }

    public function test_lecturer_cannot_create_assignment_for_another_lecturers_course(): void
    {
        $lecturerA = $this->userWithRole('dosen');
        $lecturerB = $this->userWithRole('dosen');
        $courseB = $this->courseFor($lecturerB);

        $tokenA = $lecturerA->createToken('test')->plainTextToken;
        $this->withToken($tokenA)
            ->postJson('/api/v1/assignments', [
                'course_id' => $courseB->id,
                'title' => 'Tugas Bajakan',
                'instructions' => 'Instruksi',
                'due_at' => now()->addDays(3)->toDateTimeString(),
                'max_score' => 100,
            ])
            ->assertForbidden();
    }

    public function test_lecturer_can_create_update_and_delete_own_assignment(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $course = $this->courseFor($lecturer);
        $token = $lecturer->createToken('test')->plainTextToken;

        // 1. Create (POST) -> 201 Created
        $responseCreate = $this->withToken($token)
            ->postJson('/api/v1/assignments', [
                'course_id' => $course->id,
                'title' => 'Tugas 1 Praktikum',
                'instructions' => 'Kumpulkan laporan PDF',
                'due_at' => now()->addDays(5)->toDateTimeString(),
                'max_score' => 100,
                'allow_late' => true,
                'status' => 'published',
            ]);

        $responseCreate->assertCreated()
            ->assertJsonPath('data.title', 'Tugas 1 Praktikum')
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.status', 'published');

        $assignmentId = $responseCreate->json('data.id');

        // 2. Update (PUT) -> 200 OK
        $responseUpdate = $this->withToken($token)
            ->putJson("/api/v1/assignments/{$assignmentId}", [
                'title' => 'Tugas 1 Praktikum (Revisi Judul)',
                'max_score' => 90,
            ]);

        $responseUpdate->assertOk()
            ->assertJsonPath('data.title', 'Tugas 1 Praktikum (Revisi Judul)')
            ->assertJsonPath('data.max_score', 90);

        // 3. Delete (DELETE) -> 204 No Content
        $this->withToken($token)
            ->deleteJson("/api/v1/assignments/{$assignmentId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('assignments', ['id' => $assignmentId]);
    }

    public function test_other_lecturer_cannot_update_or_delete_assignment(): void
    {
        $lecturerA = $this->userWithRole('dosen');
        $lecturerB = $this->userWithRole('dosen');
        $courseA = $this->courseFor($lecturerA);
        $assignment = $this->assignmentFor($courseA, $lecturerA);

        $tokenB = $lecturerB->createToken('test')->plainTextToken;

        // Update ditolak 403
        $this->withToken($tokenB)
            ->putJson("/api/v1/assignments/{$assignment->id}", [
                'title' => 'Mencoba Bajak Judul',
            ])
            ->assertForbidden();

        // Delete ditolak 403
        $this->withToken($tokenB)
            ->deleteJson("/api/v1/assignments/{$assignment->id}")
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Submissions & Grading (Anti-Duplicate, Multipart, Upsert)
    |--------------------------------------------------------------------------
    */

    public function test_student_can_submit_assignment_and_cannot_submit_twice(): void
    {
        Storage::fake('local');

        $lecturer = $this->userWithRole('dosen');
        $course = $this->courseFor($lecturer);
        $assignment = $this->assignmentFor($course, $lecturer);
        $student = $this->userWithRole('mahasiswa');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $tokenStudent = $student->createToken('test')->plainTextToken;

        // Upload berkas submission pertama -> 201 Created
        $dummyFile = UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf');

        $response = $this->withToken($tokenStudent)
            ->postJson("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => $dummyFile,
                'note' => 'Ini tugas saya pak.',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.assignment_id', $assignment->id)
            ->assertJsonPath('data.student.id', $student->id)
            ->assertJsonPath('data.original_name', 'laporan.pdf')
            ->assertJsonPath('data.note', 'Ini tugas saya pak.');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'laporan.pdf',
        ]);

        // Coba kumpulkan lagi -> 409 Conflict
        $responseDuplicate = $this->withToken($tokenStudent)
            ->postJson("/api/v1/assignments/{$assignment->id}/submissions", [
                'file' => UploadedFile::fake()->create('laporan2.pdf', 300, 'application/pdf'),
            ]);

        $responseDuplicate->assertStatus(409)
            ->assertJsonPath('message', 'Anda sudah mengumpulkan tugas ini.');
    }

    public function test_lecturer_can_grade_submission_with_upsert_status_codes(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $course = $this->courseFor($lecturer);
        $assignment = $this->assignmentFor($course, $lecturer, ['max_score' => 100]);
        $student = $this->userWithRole('mahasiswa');
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $tokenLecturer = $lecturer->createToken('test')->plainTextToken;

        // 1. Penilaian pertama kali -> 201 Created
        $responseCreateGrade = $this->withToken($tokenLecturer)
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 88.5,
                'feedback' => 'Analisis komprehensif, tingkatkan dokumentasi.',
            ]);

        $responseCreateGrade->assertCreated()
            ->assertJsonPath('data.submission_id', $submission->id)
            ->assertJsonPath('data.score', 88.5)
            ->assertJsonPath('data.grader.id', $lecturer->id);

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'score' => 88.5,
            'graded_by' => $lecturer->id,
        ]);

        // 2. Pembaruan penilaian (re-grade) -> 200 OK
        $responseUpdateGrade = $this->withToken($tokenLecturer)
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 95.0,
                'feedback' => 'Nilai diperbaiki setelah klarifikasi.',
            ]);

        $responseUpdateGrade->assertOk()
            ->assertJsonPath('data.score', 95)
            ->assertJsonPath('data.feedback', 'Nilai diperbaiki setelah klarifikasi.');

        $this->assertEquals(1, Grade::where('submission_id', $submission->id)->count());
    }

    public function test_other_lecturer_cannot_grade_submission_403(): void
    {
        $lecturerA = $this->userWithRole('dosen');
        $lecturerB = $this->userWithRole('dosen');
        $courseA = $this->courseFor($lecturerA);
        $assignment = $this->assignmentFor($courseA, $lecturerA);
        $student = $this->userWithRole('mahasiswa');
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $tokenB = $lecturerB->createToken('test')->plainTextToken;

        $this->withToken($tokenB)
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 70,
                'feedback' => 'Percobaan intervensi ilegal',
            ])
            ->assertForbidden()
            ->assertJsonPath('message', 'Anda tidak memiliki akses ke sumber daya ini.');
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Notifications
    |--------------------------------------------------------------------------
    */

    public function test_user_can_view_and_read_own_notifications(): void
    {
        $user = $this->userWithRole('mahasiswa');
        $otherUser = $this->userWithRole('mahasiswa');

        // Buat dummy notifikasi di tabel database
        $notifId = (string) Str::uuid();
        $user->notifications()->create([
            'id' => $notifId,
            'type' => 'App\Notifications\AssignmentCreatedNotification',
            'data' => ['title' => 'Tugas Baru Dibuat'],
            'read_at' => null,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // 1. List notifications
        $responseIndex = $this->withToken($token)
            ->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'read_at', 'created_at'],
                ],
            ]);

        // 2. Mark as read
        $responseRead = $this->withToken($token)
            ->postJson("/api/v1/notifications/{$notifId}/read")
            ->assertOk();

        $responseRead->assertJsonPath('data.id', $notifId);

        $this->assertNotNull($user->notifications()->find($notifId)->read_at);

        // 3. User lain mencoba membaca notifikasi tersebut -> 404 Not Found
        $this->resetAuth();
        $tokenOther = $otherUser->createToken('test')->plainTextToken;
        $this->withToken($tokenOther)
            ->postJson("/api/v1/notifications/{$notifId}/read")
            ->assertNotFound();
    }
}
