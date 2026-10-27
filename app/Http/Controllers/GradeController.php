<?php

namespace App\Http\Controllers;

use App\Events\GradeReleased;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    private function ensureGrader(Request $request, Submission $submission): void
    {
        Gate::authorize('create', [Grade::class, $submission]);
    }

    public function indexAdmin(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Grade::class);
        return response()->json([
            'grades' => Grade::with(['submission.assignment.course', 'submission.student', 'grader'])->latest()->paginate(20),
        ]);
    }

    public function indexForCourse(Request $request, Course $course): JsonResponse
    {
        Gate::authorize('view', $course);
        Gate::authorize('viewAny', Grade::class);

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'grades' => Grade::with(['submission.assignment', 'submission.student', 'grader'])
                ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
                ->latest()->paginate(20),
        ]);
    }

    public function indexMine(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Grade::class);
        $user = $request->user();

        return response()->json([
            'grades' => Grade::with(['submission.assignment.course', 'grader'])
                ->whereHas('submission', fn ($query) => $query->where('user_id', $user->id))
                ->latest()->paginate(20),
        ]);
    }

    public function show(Request $request, Grade $grade): JsonResponse
    {
        Gate::authorize('view', $grade);
        return response()->json(['grade' => $grade->load(['submission.assignment.course', 'submission.student', 'grader'])]);
    }

    public function store(Request $request, Submission $submission): JsonResponse
    {
        $this->ensureGrader($request, $submission);
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
        ]);

        $grade = $submission->grade()->first();
        $isNew = $grade === null;
        $grade ??= new Grade();
        $grade->submission_id = $submission->id;
        $grade->graded_by = $request->user()->id;
        $grade->score = $data['score'];
        $grade->feedback = $data['feedback'] ?? null;
        $grade->graded_at = now();
        $grade->save();

<<<<<<< Updated upstream
        return response()->json(['grade' => $grade], 201);
=======
        if ($isNew || $grade->wasChanged(['score', 'feedback'])) {
            GradeReleased::dispatch($grade);
        }

        if ($request->wantsJson()) {
            return response()->json(['grade' => $grade], 201);
        }

        return redirect()->back()->with('success', 'Nilai dan umpan balik tugas berhasil disimpan.');
>>>>>>> Stashed changes
    }

    public function update(Request $request, Grade $grade): JsonResponse
    {
        Gate::authorize('update', $grade);
        $data = $request->validate([
            'score' => ['sometimes', 'required', 'numeric', 'min:0', 'max:'.$grade->submission->assignment->max_score],
            'feedback' => ['sometimes', 'nullable', 'string'],
        ]);

        if (array_key_exists('score', $data)) $grade->score = $data['score'];
        if (array_key_exists('feedback', $data)) $grade->feedback = $data['feedback'];
        $grade->graded_by = $request->user()->id;
        $grade->graded_at = now();
        $grade->save();

        if ($grade->wasChanged(['score', 'feedback'])) {
            GradeReleased::dispatch($grade);
        }

        return response()->json(['grade' => $grade->fresh(['submission.assignment.course', 'grader'])]);
    }

    public function destroy(Request $request, Grade $grade): JsonResponse
    {
        Gate::authorize('delete', $grade);
        $grade->delete();

        return response()->json(['message' => 'Nilai berhasil dihapus.']);
    }
}
