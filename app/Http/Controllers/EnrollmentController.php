<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    private function authorizeManager(Request $request, Course $course): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->can('manageContent', $course), 403);
    }

    /** Admin or assigned lecturer may inspect a course roster. */
    public function index(Request $request, Course $course)
    {
        $this->authorizeManager($request, $course);

        if ($request->wantsJson()) {
            return response()->json([
                'course' => ['id' => $course->id, 'name' => $course->name],
                'students' => $course->students()->select('users.id', 'users.name', 'users.email', 'users.nim_nip')->get(),
            ]);
        }

        return redirect()->route('courses.show', $course);
    }

    /** Admin or the assigned lecturer registers a student for this course. */
    public function store(Request $request, Course $course)
    {
        $this->authorizeManager($request, $course);
        $validated = $request->validate([
            'student_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('users', 'id')->where('role', 'mahasiswa'),
            ],
        ], [
            'student_id.required' => 'Pilih mahasiswa yang ingin didaftarkan.',
            'student_id.exists' => 'Mahasiswa tidak ditemukan atau bukan berstatus mahasiswa.',
        ]);
        $studentId = (int) $validated['student_id'];

        $alreadyEnrolled = $course->students()->whereKey($studentId)->exists();
        if (!$alreadyEnrolled) {
            $course->students()->attach($studentId, ['enrolled_at' => now()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $alreadyEnrolled ? 'Mahasiswa sudah terdaftar pada mata kuliah ini.' : 'Pendaftaran mahasiswa berhasil.',
                'course_id' => $course->id,
                'student_id' => $studentId,
            ], $alreadyEnrolled ? 200 : 201);
        }

        return back()->with('success', $alreadyEnrolled ? 'Mahasiswa sudah terdaftar pada mata kuliah ini.' : 'Pendaftaran mahasiswa berhasil.');
    }

    /** Admin or the assigned lecturer removes a student's enrollment. */
    public function destroy(Request $request, Course $course, User $student)
    {
        $this->authorizeManager($request, $course);
        abort_unless($student->role === 'mahasiswa', 404);

        $detached = $course->students()->detach($student->id);
        abort_unless($detached > 0, 404);

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return back()->with('success', 'Mahasiswa berhasil dikeluarkan dari mata kuliah.');
    }
}
