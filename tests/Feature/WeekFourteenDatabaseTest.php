<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pengujian Khusus Database Engineer (Minggu 14)
 * Fokus:
 * 1. Kemandirian Factory tanpa data Seeder (Seederless DB Isolation)
 * 2. Integritas Relasional Database (Restrict on Delete & Cascade on Delete)
 * 3. Query Profiling & Pencegahan Masalah N+1 Query (Eager Loading Optimization)
 */
class WeekFourteenDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }

    /**
     * Test 1: Memverifikasi bahwa seluruh factory model sistem dapat membangkitkan data
     * secara mandiri di database kosong tanpa ketergantungan pada data seeder.
     */
    public function test_seederless_database_factories_can_generate_all_models_without_seeder(): void
    {
        // Pastikan tabel kosong sebelum pembuatan data factory
        $this->assertEquals(0, User::count());
        $this->assertEquals(0, Course::count());
        $this->assertEquals(0, Assignment::count());
        $this->assertEquals(0, Material::count());
        $this->assertEquals(0, Submission::count());
        $this->assertEquals(0, Grade::count());

        // 1. Uji pemanggilan factory terarah
        $admin = User::factory()->create(['role' => 'admin']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $course = Course::factory()->create([
            'lecturer_id' => $dosen->id,
            'status' => 'active',
        ]);
        $course->students()->attach($mahasiswa->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->for($course)->create([
            'created_by' => $dosen->id,
        ]);

        $material = Material::factory()->for($course)->create([
            'uploaded_by' => $dosen->id,
        ]);

        $submission = Submission::factory()->for($assignment)->for($mahasiswa)->create();

        $grade = Grade::factory()->for($submission)->create([
            'graded_by' => $dosen->id,
            'score' => 95,
        ]);

        // Verifikasi persistensi data pada database
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['id' => $dosen->id, 'role' => 'dosen']);
        $this->assertDatabaseHas('users', ['id' => $mahasiswa->id, 'role' => 'mahasiswa']);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'lecturer_id' => $dosen->id]);
        $this->assertDatabaseHas('course_user', ['course_id' => $course->id, 'user_id' => $mahasiswa->id]);
        $this->assertDatabaseHas('assignments', ['id' => $assignment->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('materials', ['id' => $material->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('submissions', ['id' => $submission->id, 'user_id' => $mahasiswa->id]);
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'submission_id' => $submission->id, 'score' => 95]);

        // 2. Uji kemandirian relasional otomatis (autonomous cascade factory)
        // Pemanggilan GradeFactory tanpa parameter harus otomatis membuat submission -> assignment -> course -> user
        $autoGrade = Grade::factory()->create();
        $this->assertNotNull($autoGrade->submission);
        $this->assertNotNull($autoGrade->submission->assignment);
        $this->assertNotNull($autoGrade->submission->assignment->course);
        $this->assertNotNull($autoGrade->grader);
    }

    /**
     * Test 2: Memverifikasi integritas constraint database sesuai kontrak spesifikasi:
     * - restrictOnDelete pada courses.lecturer_id (mencegah dosen pengampu dihapus jika masih ada mata kuliah aktif)
     * - cascadeOnDelete saat course dihapus (menghapus assignment, material, submission, dan pivot enrollments)
     */
    public function test_database_cascade_and_restrict_integrity_constraints(): void
    {
        // 1. Verifikasi Restrict on Delete
        $dosen = User::factory()->create(['role' => 'dosen']);
        $course = Course::factory()->create(['lecturer_id' => $dosen->id]);

        try {
            // User menggunakan SoftDeletes, sehingga forceDelete() mengeksekusi DELETE fisik
            // yang memicu restrictOnDelete pada foreign key courses.lecturer_id
            $dosen->forceDelete();
            $this->fail('Penghapusan fisik dosen yang mengampu mata kuliah seharusnya melempar QueryException (restrictOnDelete).');
        } catch (QueryException $e) {
            $this->assertTrue(true, 'DB constraint restrictOnDelete berhasil mencegah penghapusan fisik.');
        }

        // 2. Verifikasi Cascade on Delete
        $assignment = Assignment::factory()->for($course)->create(['created_by' => $dosen->id]);
        $material = Material::factory()->for($course)->create(['uploaded_by' => $dosen->id]);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $course->students()->attach($mahasiswa->id, ['enrolled_at' => now()]);
        $submission = Submission::factory()->for($assignment)->for($mahasiswa)->create();

        $courseId = $course->id;
        $assignmentId = $assignment->id;
        $materialId = $material->id;
        $submissionId = $submission->id;

        // Hapus mata kuliah
        $course->delete();

        // Pastikan seluruh entitas anak terhapus secara berantai (cascade)
        $this->assertDatabaseMissing('courses', ['id' => $courseId]);
        $this->assertDatabaseMissing('assignments', ['id' => $assignmentId]);
        $this->assertDatabaseMissing('materials', ['id' => $materialId]);
        $this->assertDatabaseMissing('submissions', ['id' => $submissionId]);
        $this->assertDatabaseMissing('course_user', ['course_id' => $courseId, 'user_id' => $mahasiswa->id]);

        // Akun user mahasiswa dan dosen tetap aman (tidak ikut terhapus)
        $this->assertDatabaseHas('users', ['id' => $dosen->id]);
        $this->assertDatabaseHas('users', ['id' => $mahasiswa->id]);
    }

    /**
     * Test 3: Query Profiling & Optimasi Query (Mencegah Masalah N+1 Query)
     * Memverifikasi bahwa pengambilan daftar mata kuliah beserta data dosen relasi
     * dan jumlah mahasiswa terdaftar selalu dieksekusi dalam jumlah query konstan (O(1)).
     */
    public function test_query_profiling_and_eager_loading_prevents_n_plus_one_on_course_listing(): void
    {
        // Siapkan 5 mata kuliah dengan masing-masing dosen dan beberapa mahasiswa terdaftar
        for ($i = 0; $i < 5; $i++) {
            $dosen = User::factory()->create(['role' => 'dosen']);
            $course = Course::factory()->create(['lecturer_id' => $dosen->id]);
            $students = User::factory()->count(3)->create(['role' => 'mahasiswa']);
            foreach ($students as $st) {
                $course->students()->attach($st->id, ['enrolled_at' => now()]);
            }
        }

        $this->assertEquals(5, Course::count());

        // Aktifkan query logging untuk mengukur query
        DB::flushQueryLog();
        DB::enableQueryLog();

        // Query optimal: Eager load lecturer dan agregasi withCount students
        $courses = Course::query()
            ->with('lecturer')
            ->withCount('students')
            ->get();

        // Iterasi seluruh hasil untuk memastikan tidak ada kueri lazy loading tersembunyi
        foreach ($courses as $c) {
            $lecturerName = $c->lecturer->name;
            $studentCount = $c->students_count;
            $this->assertNotEmpty($lecturerName);
            $this->assertEquals(3, $studentCount);
        }

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Jumlah kueri yang dieksekusi harus maksimal 2 kueri:
        // 1. SELECT courses.*, (COUNT student) FROM courses
        // 2. SELECT * FROM users WHERE users.id IN (...)
        // Jika terjadi N+1 query, jumlahnya akan melonjak menjadi 1 + 5 = 6 kueri atau lebih.
        $this->assertLessThanOrEqual(
            2,
            count($queries),
            'Terjadi masalah N+1 Query! Ditemukan ' . count($queries) . ' kueri yang dieksekusi.'
        );
    }
}
