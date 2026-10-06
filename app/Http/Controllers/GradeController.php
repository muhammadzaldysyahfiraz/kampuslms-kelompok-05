<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    private function ensureGrader(Request $request, Submission $submission): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        $submission->loadMissing('assignment.course');
        abort_unless(
            $user->role === 'admin'
                || ($user->role === 'dosen' && (int) $submission->assignment->course->lecturer_id === (int) $user->id),
            403
        );
    }

    private function ensureGradeViewer(Request $request, Grade $grade): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        $grade->loadMissing('submission.assignment.course');
        $submission = $grade->submission;
        abort_unless(
            $user->role === 'admin'
                || ($user->role === 'dosen' && (int) $submission->assignment->course->lecturer_id === (int) $user->id)
                || ($user->role === 'mahasiswa' && (int) $submission->user_id === (int) $user->id),
            403
        );
    }

    public function indexAdmin(Request $request): JsonResponse
    {
        abort_unless($request->user(), 401);
        return response()->json([
            'grades' => Grade::with(['submission.assignment.course', 'submission.student', 'grader'])->latest()->paginate(20),
        ]);
    }

    public function indexForCourse(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role === 'admin' || (int) $course->lecturer_id === (int) $user->id, 403);

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'grades' => Grade::with(['submission.assignment', 'submission.student', 'grader'])
                ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
                ->latest()->paginate(20),
        ]);
    }

    public function indexMine(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role === 'mahasiswa', 403);

        return response()->json([
            'grades' => Grade::with(['submission.assignment.course', 'grader'])
                ->whereHas('submission', fn ($query) => $query->where('user_id', $user->id))
                ->latest()->paginate(20),
        ]);
    }

    public function show(Request $request, Grade $grade): JsonResponse
    {
        $this->ensureGradeViewer($request, $grade);
        return response()->json(['grade' => $grade->load(['submission.assignment.course', 'submission.student', 'grader'])]);
    }

    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->ensureGrader($request, $submission);
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ]);

        $grade = $submission->grade()->first() ?? new Grade();
        $grade->submission_id = $submission->id;
        $grade->graded_by = $request->user()->id;
        $grade->score = $data['score'];
        $grade->feedback = $data['feedback'] ?? null;
        $grade->graded_at = now();
        $grade->save();

        return response()->json(['grade' => $grade], 201);
    }

    public function update(Request $request, Grade $grade): JsonResponse
    {
        $this->ensureGradeViewer($request, $grade);
        abort_unless(in_array($request->user()->role, ['admin', 'dosen'], true), 403);
        $this->ensureGrader($request, $grade->submission);
        $data = $request->validate([
            'score' => ['sometimes', 'required', 'numeric', 'min:0', 'max:'.$grade->submission->assignment->max_score],
            'feedback' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('score', $data)) $grade->score = $data['score'];
        if (array_key_exists('feedback', $data)) $grade->feedback = $data['feedback'];
        $grade->graded_by = $request->user()->id;
        $grade->graded_at = now();
        $grade->save();

        return response()->json(['grade' => $grade->fresh(['submission.assignment.course', 'grader'])]);
    }

    public function destroy(Request $request, Grade $grade): JsonResponse
    {
        $this->ensureGradeViewer($request, $grade);
        abort_unless(in_array($request->user()->role, ['admin', 'dosen'], true), 403);
        $this->ensureGrader($request, $grade->submission);
        $grade->delete();

        return response()->json(['message' => 'Nilai berhasil dihapus.']);
    }
}
