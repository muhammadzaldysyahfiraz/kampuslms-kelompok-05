<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionFormTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $lecturer;
    private Course $course;
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');

        $this->lecturer = User::factory()->create(['role' => 'dosen']);
        $this->student = User::factory()->create(['role' => 'mahasiswa']);

        $this->course = Course::factory()->create([
            'lecturer_id' => $this->lecturer->id,
            'status' => 'active',
        ]);
        $this->course->students()->attach($this->student->id, ['enrolled_at' => now()]);

        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'created_by' => $this->lecturer->id,
            'status' => 'published',
            'due_at' => now()->addDays(3),
            'allow_late' => false,
        ]);
    }

    /**
     * 1. Jalur Sukses: Mahasiswa terdaftar berhasil mengumpulkan tugas dengan file PDF valid.
     */
    public function test_student_can_submit_assignment_form_with_valid_file(): void
    {
        $file = UploadedFile::fake()->create('laporan_proweb.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->student)
            ->from(route('assignments.show', $this->assignment))
            ->post(route('assignments.submissions.store', $this->assignment), [
                'file' => $file,
                'note' => 'Tugas praktikum minggu 14 selesai tepat waktu.',
            ]);

        $response->assertRedirect(route('assignments.show', $this->assignment));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
            'note' => 'Tugas praktikum minggu 14 selesai tepat waktu.',
            'is_late' => false,
        ]);

        $submission = Submission::where('assignment_id', $this->assignment->id)
            ->where('user_id', $this->student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertTrue(Storage::disk('local')->exists($submission->file_path));
    }

    /**
     * 2. Jalur Gagal: Form menolak berkas dengan ekstensi non-dokumen (misal .exe / .php).
     */
    public function test_submission_form_rejects_non_pdf_files(): void
    {
        $dangerousFile = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');

        $response = $this->actingAs($this->student)
            ->from(route('assignments.show', $this->assignment))
            ->post(route('assignments.submissions.store', $this->assignment), [
                'file' => $dangerousFile,
            ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * 3. Jalur Gagal: Form menolak berkas yang melebihi batas ukuran maksimal (5 MB).
     */
    public function test_submission_form_rejects_oversized_file(): void
    {
        // Simulasi berkas 6 MB (6144 KB), melebihi batas 'max:5120'
        $oversizedFile = UploadedFile::fake()->create('dokumen_raksasa.pdf', 6144, 'application/pdf');

        $response = $this->actingAs($this->student)
            ->from(route('assignments.show', $this->assignment))
            ->post(route('assignments.submissions.store', $this->assignment), [
                'file' => $oversizedFile,
            ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * 4. Jalur Gagal: Form menolak pengumpulan tugas setelah deadline jika allow_late bernilai false.
     */
    public function test_submission_form_rejects_late_submission(): void
    {
        // Ubah tenggat waktu penugasan ke masa lalu
        $this->assignment->update([
            'due_at' => now()->subDay(),
            'allow_late' => false,
        ]);

        $file = UploadedFile::fake()->create('terlambat.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->student)
            ->from(route('assignments.show', $this->assignment))
            ->post(route('assignments.submissions.store', $this->assignment), [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors(['file']);
        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * 5. Jalur Gagal Otorisasi: Mahasiswa yang tidak terdaftar di mata kuliah ditolak (403 Forbidden).
     */
    public function test_unregistered_student_cannot_submit_assignment(): void
    {
        $unregisteredStudent = User::factory()->create(['role' => 'mahasiswa']);

        $file = UploadedFile::fake()->create('tugas_ilegal.pdf', 200, 'application/pdf');

        $response = $this->actingAs($unregisteredStudent)
            ->post(route('assignments.submissions.store', $this->assignment), [
                'file' => $file,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('submissions', 0);
    }

    /**
     * 6. Jalur Gagal Autentikasi: Pengguna tanpa login (guest) ditolak (401 Unauthorized).
     */
    public function test_guest_cannot_submit_assignment(): void
    {
        $file = UploadedFile::fake()->create('tugas.pdf', 200, 'application/pdf');

        $response = $this->post(route('assignments.submissions.store', $this->assignment), [
            'file' => $file,
        ]);

        // Route middleware 'auth' menolak guest (redirect ke login atau 401)
        $this->assertTrue(
            $response->status() === 401 || $response->isRedirect(route('login')),
            'Pengguna yang belum login wajib ditolak atau diarahkan ke login.'
        );
        $this->assertDatabaseCount('submissions', 0);
    }
}
