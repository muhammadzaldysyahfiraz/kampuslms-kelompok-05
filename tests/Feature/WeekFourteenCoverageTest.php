<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeReleasedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeekFourteenCoverageTest extends TestCase
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

    public function test_guest_is_redirected_to_login_for_protected_web_routes_and_downloads(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/courses')->assertRedirect('/login');
        $this->get(route('submissions.download', $submission))->assertRedirect('/login');
    }

    public function test_admin_can_update_course_while_lecturer_and_student_cannot_create_update_or_delete_courses(): void
    {
        $admin = $this->userWithRole('admin');
        $lecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'code' => 'SI1401',
            'name' => 'Pemrograman Web Awal',
            'sks' => 3,
            'status' => 'active',
        ]);

        // Admin sukses memperbarui mata kuliah
        $this->actingAs($admin)
            ->put(route('courses.update', $course), [
                'code' => 'SI1401',
                'name' => 'Pemrograman Web Lanjutan',
                'description' => 'Deskripsi terbaru',
                'sks' => 4,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ])
            ->assertRedirect(route('courses.show', $course));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Pemrograman Web Lanjutan',
            'sks' => 4,
        ]);

        // Mahasiswa ditolak 403 untuk create, update, dan delete
        $this->actingAs($student)
            ->post(route('courses.store'), [
                'code' => 'SI9991',
                'name' => 'Kelas Ilegal Mahasiswa',
                'description' => 'Tidak boleh tersimpan',
                'sks' => 3,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ])
            ->assertForbidden();
        $this->assertDatabaseMissing('courses', ['code' => 'SI9991']);

        $this->actingAs($student)
            ->put(route('courses.update', $course), [
                'code' => 'SI1401',
                'name' => 'Diubah Mahasiswa',
                'description' => 'Ditolak',
                'sks' => 3,
                'lecturer_id' => $lecturer->id,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->actingAs($student)
            ->delete(route('courses.destroy', $course))
            ->assertForbidden();

        // Dosen (bahkan pengampu) ditolak 403 untuk delete course
        $this->actingAs($lecturer)
            ->delete(route('courses.destroy', $course))
            ->assertForbidden();

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Pemrograman Web Lanjutan',
        ]);
    }

    public function test_student_web_courses_index_and_show_are_scoped_to_enrolled_courses(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');

        $enrolledCourse = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'name' => 'Basis Data Terdaftar',
            'code' => 'SI2001',
            'status' => 'active',
        ]);
        $otherCourse = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'name' => 'Jaringan Tidak Terdaftar',
            'code' => 'SI2002',
            'status' => 'active',
        ]);

        $enrolledCourse->students()->attach($student->id, ['enrolled_at' => now()]);

        $this->actingAs($student)
            ->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Basis Data Terdaftar')
            ->assertDontSee('Jaringan Tidak Terdaftar');

        $this->actingAs($student)
            ->get(route('courses.show', $enrolledCourse))
            ->assertOk()
            ->assertSee('Basis Data Terdaftar');

        $this->actingAs($student)
            ->get(route('courses.show', $otherCourse))
            ->assertForbidden();
    }

    public function test_other_lecturer_cannot_upload_update_or_delete_material_in_another_lecturers_course(): void
    {
        Storage::fake('local');

        $ownerLecturer = $this->userWithRole('dosen');
        $otherLecturer = $this->userWithRole('dosen');
        $course = Course::factory()->create(['lecturer_id' => $ownerLecturer->id]);

        $storedPath = "materials/{$course->id}/materi-asli.pdf";
        Storage::disk('local')->put($storedPath, 'isi materi asli');

        $material = Material::factory()->create([
            'course_id' => $course->id,
            'uploaded_by' => $ownerLecturer->id,
            'title' => 'Materi Asli Dosen A',
            'description' => 'Deskripsi awal',
            'type' => 'file',
            'file_path' => $storedPath,
            'original_name' => 'materi-asli.pdf',
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
        ]);

        // Dosen lain dilarang unggah materi baru (403)
        $this->actingAs($otherLecturer)
            ->post(route('courses.materials.store', $course), [
                'title' => 'Materi Sisipan Dosen Lain',
                'description' => 'Tidak boleh tersimpan',
                'type' => 'file',
                'file' => UploadedFile::fake()->create('sisipan.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('materials', ['title' => 'Materi Sisipan Dosen Lain']);

        // Dosen lain dilarang mengubah materi milik dosen pengampu (403)
        $this->actingAs($otherLecturer)
            ->put(route('materials.update', $material), [
                'title' => 'Judul Dibajak Dosen Lain',
                'description' => 'Diubah ilegal',
                'type' => 'link',
                'external_url' => 'https://example.com/ilegal',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'title' => 'Materi Asli Dosen A',
        ]);

        // Dosen lain dilarang menghapus materi milik dosen pengampu (403)
        $this->actingAs($otherLecturer)
            ->delete(route('materials.destroy', $material))
            ->assertForbidden();

        $this->assertDatabaseHas('materials', ['id' => $material->id]);
        Storage::disk('local')->assertExists($storedPath);
    }

    public function test_course_lecturer_can_update_and_delete_own_material_cleaning_up_old_file(): void
    {
        Storage::fake('local');

        $lecturer = $this->userWithRole('dosen');
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $oldPath = "materials/{$course->id}/lama.pdf";
        Storage::disk('local')->put($oldPath, 'berkas lama');

        $material = Material::factory()->create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Materi Awal',
            'description' => 'Deskripsi awal',
            'type' => 'file',
            'file_path' => $oldPath,
            'original_name' => 'lama.pdf',
        ]);

        $newFile = UploadedFile::fake()->create('revisi.pdf', 250, 'application/pdf');
        $this->actingAs($lecturer)
            ->put(route('materials.update', $material), [
                'title' => 'Materi Revisi',
                'description' => 'Deskripsi revisi',
                'type' => 'file',
                'file' => $newFile,
            ])
            ->assertRedirect(route('materials.show', $material));

        $material->refresh();
        $this->assertSame('Materi Revisi', $material->title);
        $this->assertNotSame($oldPath, $material->file_path);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($material->file_path);

        $updatedPath = $material->file_path;
        $this->actingAs($lecturer)
            ->delete(route('materials.destroy', $material))
            ->assertRedirect(route('courses.show', $course));

        $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        Storage::disk('local')->assertMissing($updatedPath);
    }

    public function test_nested_material_binding_rejects_material_from_another_course(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');
        $courseA = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $courseB = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $courseA->students()->attach($student->id, ['enrolled_at' => now()]);
        $courseB->students()->attach($student->id, ['enrolled_at' => now()]);

        $materialB = Material::factory()->create([
            'course_id' => $courseB->id,
            'uploaded_by' => $lecturer->id,
            'type' => 'link',
            'external_url' => 'https://example.com/materi-b',
        ]);

        $this->actingAs($student)
            ->getJson("/courses/{$courseA->id}/materials/{$materialB->id}")
            ->assertNotFound();

        $this->actingAs($student)
            ->getJson("/courses/{$courseB->id}/materials/{$materialB->id}")
            ->assertOk()
            ->assertJsonPath('material.id', $materialB->id);
    }

    public function test_other_lecturer_cannot_update_or_delete_assignment_via_web(): void
    {
        $ownerLecturer = $this->userWithRole('dosen');
        $otherLecturer = $this->userWithRole('dosen');
        $course = Course::factory()->create(['lecturer_id' => $ownerLecturer->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $ownerLecturer->id,
            'title' => 'Tugas Resmi Dosen A',
            'status' => 'published',
        ]);

        $this->actingAs($otherLecturer)
            ->put(route('assignments.update', $assignment), [
                'title' => 'Tugas Dibajak Dosen B',
            ])
            ->assertForbidden();

        $this->actingAs($otherLecturer)
            ->delete(route('assignments.destroy', $assignment))
            ->assertForbidden();

        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'title' => 'Tugas Resmi Dosen A',
        ]);
    }

    public function test_non_enrolled_student_cannot_submit_assignment(): void
    {
        Storage::fake('local');

        $lecturer = $this->userWithRole('dosen');
        $outsiderStudent = $this->userWithRole('mahasiswa');
        $course = Course::factory()->create([
            'lecturer_id' => $lecturer->id,
            'status' => 'active',
        ]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->addDays(2),
            'allow_late' => true,
            'status' => 'published',
        ]);

        $this->actingAs($outsiderStudent)
            ->post(route('assignments.submissions.store', $assignment), [
                'file' => UploadedFile::fake()->create('ilegal.pdf', 150, 'application/pdf'),
                'note' => 'Saya tidak terdaftar di kelas ini',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $outsiderStudent->id,
        ]);
        $this->assertEmpty(Storage::disk('local')->allFiles());
    }

    public function test_web_grading_succeeds_for_course_lecturer_and_forbids_other_lecturer_and_student(): void
    {
        Notification::fake();

        $ownerLecturer = $this->userWithRole('dosen');
        $otherLecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');
        $otherStudent = $this->userWithRole('mahasiswa');

        $course = Course::factory()->create(['lecturer_id' => $ownerLecturer->id]);
        $course->students()->attach([$student->id, $otherStudent->id], ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $ownerLecturer->id,
            'max_score' => 100,
        ]);
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        // Mahasiswa dilarang menilai via web maupun API (403)
        $this->actingAs($student)
            ->post(route('submissions.grade.store', $submission), [
                'score' => 100,
                'feedback' => 'Nilai mandiri ilegal',
            ])
            ->assertForbidden();

        $this->actingAs($student, 'sanctum')
            ->putJson("/api/v1/submissions/{$submission->id}/grade", [
                'score' => 100,
                'feedback' => 'Nilai API ilegal',
            ])
            ->assertForbidden();

        // Dosen lain dilarang menilai via web (403)
        $this->actingAs($otherLecturer)
            ->post(route('submissions.grade.store', $submission), [
                'score' => 80,
                'feedback' => 'Intervensi dosen lain',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('grades', ['submission_id' => $submission->id]);
        Notification::assertNothingSent();

        // Dosen pengampu berhasil memberi nilai dan memicu notifikasi ke pemilik submission
        $this->actingAs($ownerLecturer)
            ->post(route('submissions.grade.store', $submission), [
                'score' => 92,
                'feedback' => 'Penjelasan sangat runtut.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('grades', [
            'submission_id' => $submission->id,
            'graded_by' => $ownerLecturer->id,
            'score' => 92,
        ]);

        Notification::assertSentTo($student, GradeReleasedNotification::class);
        Notification::assertNotSentTo($otherStudent, GradeReleasedNotification::class);
    }

    public function test_non_admin_cannot_access_or_create_users_in_admin_routes(): void
    {
        $lecturer = $this->userWithRole('dosen');
        $student = $this->userWithRole('mahasiswa');

        $this->actingAs($lecturer)->get('/admin/users')->assertForbidden();
        $this->actingAs($student)->get('/admin/users')->assertForbidden();

        $payload = [
            'name' => 'Pengguna Ilegal',
            'email' => 'ilegal@kampuslms.test',
            'nim_nip' => '99999999',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->actingAs($lecturer)->post('/users', $payload)->assertForbidden();
        $this->actingAs($student)->post('/admin/users', $payload)->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'ilegal@kampuslms.test']);
    }
}
