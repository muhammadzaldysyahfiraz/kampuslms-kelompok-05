<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeekNineFileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** Test dosen dapat mengunggah materi berkas ke disk privat. */
    public function test_lecturer_can_upload_material_to_private_disk(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);

        $file = UploadedFile::fake()->create('silabus-pertemuan-1.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($lecturer)->post(route('courses.materials.store', $course), [
            'title' => 'Silabus Pertemuan 1',
            'description' => 'Materi kuliah pertemuan pertama.',
            'type' => 'file',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materials', [
            'course_id' => $course->id,
            'title' => 'Silabus Pertemuan 1',
            'type' => 'file',
            'original_name' => 'silabus-pertemuan-1.pdf',
        ]);

        $material = Material::where('title', 'Silabus Pertemuan 1')->first();
        $this->assertNotNull($material->file_path);
        Storage::disk('local')->assertExists($material->file_path);
    }

    /** Test mahasiswa terdaftar dapat mengunduh materi berkas. */
    public function test_enrolled_student_can_download_material(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $filePath = "materials/{$course->id}/sample-material.pdf";
        Storage::disk('local')->put($filePath, 'konten berkas materi privat');

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $lecturer->id;
        $material->title = 'Modul Pertemuan 2';
        $material->description = 'Deskripsi modul';
        $material->type = 'file';
        $material->file_path = $filePath;
        $material->original_name = 'modul-2.pdf';
        $material->file_size = 1024;
        $material->mime_type = 'application/pdf';
        $material->save();

        $response = $this->actingAs($student)->get(route('materials.download', $material));
        $response->assertStatus(200);
        $response->assertDownload('modul-2.pdf');
    }

    /** Test mahasiswa yang tidak terdaftar ditolak mengunduh materi (403). */
    public function test_non_enrolled_student_forbidden_to_download_material(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $stranger = User::factory()->create(['role' => 'mahasiswa']); // tidak di-enroll

        $filePath = "materials/{$course->id}/sample-material.pdf";
        Storage::disk('local')->put($filePath, 'konten berkas materi privat');

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $lecturer->id;
        $material->title = 'Materi Rahasia';
        $material->description = 'Deskripsi materi';
        $material->type = 'file';
        $material->file_path = $filePath;
        $material->original_name = 'rahasia.pdf';
        $material->file_size = 1024;
        $material->mime_type = 'application/pdf';
        $material->save();

        $response = $this->actingAs($stranger)->get(route('materials.download', $material));
        $response->assertStatus(403);
    }

    /** Test mahasiswa terdaftar dapat mengumpulkan tugas dengan berkas. */
    public function test_enrolled_student_can_submit_assignment_file(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->addDays(3),
            'allow_late' => true,
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->create('laporan-tugas-mahasiswa.pdf', 500, 'application/pdf');

        $response = $this->actingAs($student)->post(route('assignments.submissions.store', $assignment), [
            'file' => $file,
            'note' => 'Berikut berkas tugas saya Pak.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'original_name' => 'laporan-tugas-mahasiswa.pdf',
            'is_late' => false,
        ]);

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->first();

        $this->assertNotNull($submission->file_path);
        Storage::disk('local')->assertExists($submission->file_path);
    }

    /** Test pengumpulan ulang memperbarui berkas dan menghapus berkas lama di disk. */
    public function test_resubmission_replaces_old_file_and_deletes_old_physical_file(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->addDays(3),
            'allow_late' => true,
            'status' => 'published',
        ]);

        // Pengumpulan pertama
        $firstFile = UploadedFile::fake()->create('jawaban-v1.pdf', 300, 'application/pdf');
        $this->actingAs($student)->post(route('assignments.submissions.store', $assignment), [
            'file' => $firstFile,
            'note' => 'Versi 1',
        ]);

        $subFirst = Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->first();
        $oldFilePath = $subFirst->file_path;
        Storage::disk('local')->assertExists($oldFilePath);

        // Pengumpulan kedua (updateOrCreate)
        $secondFile = UploadedFile::fake()->create('jawaban-v2-revisi.pdf', 400, 'application/pdf');
        $this->actingAs($student)->post(route('assignments.submissions.store', $assignment), [
            'file' => $secondFile,
            'note' => 'Versi 2 Revisi',
        ]);

        $subSecond = Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->first();

        // Pastikan record di DB tetap 1 (unique composite)
        $this->assertEquals(1, Submission::where('assignment_id', $assignment->id)->where('user_id', $student->id)->count());
        $this->assertEquals('jawaban-v2-revisi.pdf', $subSecond->original_name);
        $this->assertEquals('Versi 2 Revisi', $subSecond->note);

        // Pastikan berkas lama sudah dihapus dari disk, dan berkas baru ada
        Storage::disk('local')->assertMissing($oldFilePath);
        Storage::disk('local')->assertExists($subSecond->file_path);
    }

    /** Test pengumpulan terlambat ditolak jika allow_late bernilai false. */
    public function test_late_submission_rejected_when_allow_late_is_false(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->subDay(), // Sudah lewat deadline
            'allow_late' => false,
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->create('tugas-terlambat.pdf', 300, 'application/pdf');

        $response = $this->actingAs($student)->post(route('assignments.submissions.store', $assignment), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);
    }

    /** Test mahasiswa lain dilarang mengunduh berkas submission milik orang lain (403). */
    public function test_student_forbidden_to_download_other_student_submission(): void
    {
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $studentA = User::factory()->create(['role' => 'mahasiswa']);
        $studentB = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach([$studentA->id, $studentB->id], ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->addDays(2),
            'status' => 'published',
        ]);

        $filePath = "submissions/{$assignment->id}/tugas-student-a.pdf";
        Storage::disk('local')->put($filePath, 'tugas a');

        $subA = new Submission();
        $subA->assignment_id = $assignment->id;
        $subA->user_id = $studentA->id;
        $subA->file_path = $filePath;
        $subA->original_name = 'tugas-a.pdf';
        $subA->file_size = 1024;
        $subA->submitted_at = now();
        $subA->is_late = false;
        $subA->save();

        // Student A dapat mengunduh miliknya sendiri
        $responseA = $this->actingAs($studentA)->get(route('submissions.download', $subA));
        $responseA->assertStatus(200);

        // Student B dilarang mengunduh milik Student A (403)
        $responseB = $this->actingAs($studentB)->get(route('submissions.download', $subA));
        $responseB->assertStatus(403);

        // Dosen pengampu dapat mengunduh submission mahasiswa di kelasnya
        $responseLecturer = $this->actingAs($lecturer)->get(route('submissions.download', $subA));
        $responseLecturer->assertStatus(200);
    }

    /** Test penghapusan Assignment dan Course turut menghapus berkas fisik di disk privat (Section 9.1). */
    public function test_deleting_course_and_assignment_deletes_physical_files(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create(['lecturer_id' => $lecturer->id, 'status' => 'active']);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $materialPath = "materials/{$course->id}/materi-uji.pdf";
        Storage::disk('local')->put($materialPath, 'isi materi');

        $material = new Material();
        $material->course_id = $course->id;
        $material->uploaded_by = $lecturer->id;
        $material->title = 'Materi Uji Hapus';
        $material->type = 'file';
        $material->file_path = $materialPath;
        $material->original_name = 'materi-uji.pdf';
        $material->file_size = 512;
        $material->mime_type = 'application/pdf';
        $material->save();

        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'due_at' => now()->addDays(2),
            'status' => 'published',
        ]);

        $subPath = "submissions/{$assignment->id}/jawaban-uji.pdf";
        Storage::disk('local')->put($subPath, 'isi jawaban');

        $sub = new Submission();
        $sub->assignment_id = $assignment->id;
        $sub->user_id = $student->id;
        $sub->file_path = $subPath;
        $sub->original_name = 'jawaban-uji.pdf';
        $sub->file_size = 512;
        $sub->submitted_at = now();
        $sub->is_late = false;
        $sub->save();

        Storage::disk('local')->assertExists($materialPath);
        Storage::disk('local')->assertExists($subPath);

        // Hapus course -> harus menghapus berkas fisik material dan submission terkait
        $this->actingAs($admin)->delete(route('courses.destroy', $course));

        Storage::disk('local')->assertMissing($materialPath);
        Storage::disk('local')->assertMissing($subPath);
    }
}
