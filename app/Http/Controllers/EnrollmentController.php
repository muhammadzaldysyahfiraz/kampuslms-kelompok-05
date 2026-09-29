<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /** Admin or assigned lecturer may inspect a course roster. */
    public function index(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless(
            $user->role === 'admin'
                || ($user->role === 'dosen' && (int) $course->lecturer_id === (int) $user->id),
            403
        );

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'students' => $course->students()->select('users.id', 'users.name', 'users.email', 'users.nim_nip')->get(),
        ]);
    }

    /** A student can enroll only their own authenticated account. */
    public function store(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role === 'mahasiswa', 403);

        $alreadyEnrolled = $course->students()->whereKey($user->id)->exists();
        if (!$alreadyEnrolled) {
            $course->students()->attach($user->id, ['enrolled_at' => now()]);
        }

        return response()->json([
            'message' => $alreadyEnrolled ? 'Anda sudah terdaftar pada mata kuliah ini.' : 'Pendaftaran mata kuliah berhasil.',
            'course_id' => $course->id,
            'user_id' => $user->id,
        ], $alreadyEnrolled ? 200 : 201);
    }

    /** A student can withdraw only their own authenticated account. */
    public function destroy(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role === 'mahasiswa', 403);

        $detached = $course->students()->detach($user->id);
        abort_unless($detached > 0, 404);

        return response()->json(['message' => 'Pendaftaran mata kuliah berhasil dibatalkan.']);
    }
}
