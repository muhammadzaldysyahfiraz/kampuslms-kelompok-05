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

    public function indexForCourse(Request $request, Course $course): mixed
    {
        Gate::authorize('view', $course);
        Gate::authorize('viewAny', Grade::class);

        $gradeQuery = Grade::with(['submission.assignment', 'submission.student', 'grader'])
                ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
                ->latest();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'course' => ['id' => $course->id, 'name' => $course->name],
                'grades' => $gradeQuery->paginate(20),
            ]);
        }

        $assignments = $course->assignments()->orderBy('due_at')->get();
        $students = $course->students()->orderBy('name')->get();
        $submissions = Submission::with(['grade.grader', 'student'])
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get();

        $submissionMatrix = [];
        foreach ($submissions as $submission) {
            $submissionMatrix[$submission->user_id][$submission->assignment_id] = $submission;
        }

        $studentAverages = [];
        foreach ($students as $student) {
            $scores = [];
            foreach ($assignments as $assignment) {
                $grade = $submissionMatrix[$student->id][$assignment->id]->grade ?? null;
                if ($grade) {
                    $scores[] = (float) $grade->score;
                }
            }
            $studentAverages[$student->id] = $scores ? round(array_sum($scores) / count($scores), 1) : null;
        }

        $assignmentAverages = [];
        foreach ($assignments as $assignment) {
            $scores = [];
            foreach ($students as $student) {
                $grade = $submissionMatrix[$student->id][$assignment->id]->grade ?? null;
                if ($grade) {
                    $scores[] = (float) $grade->score;
                }
            }
            $assignmentAverages[$assignment->id] = $scores ? round(array_sum($scores) / count($scores), 1) : null;
        }

        $totalGraded = $submissions->filter(fn ($submission) => $submission->grade !== null)->count();
        $scoredAverages = array_filter($studentAverages, fn ($average) => $average !== null);
        $classAverage = $scoredAverages ? round(array_sum($scoredAverages) / count($scoredAverages), 1) : null;

        return view('grades.course', compact(
            'course',
            'assignments',
            'students',
            'submissionMatrix',
            'studentAverages',
            'assignmentAverages',
            'submissions',
            'totalGraded',
            'classAverage'
        ));
    }

    public function indexMine(Request $request): mixed
    {
        Gate::authorize('viewAny', Grade::class);
        $user = $request->user();
        $grades = Grade::with(['submission.assignment.course', 'grader'])
            ->whereHas('submission', fn ($query) => $query->where('user_id', $user->id))
            ->latest();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['grades' => $grades->paginate(20)]);
        }

        return view('grades.mine', ['grades' => $grades->paginate(20)]);
    }

    public function show(Request $request, Grade $grade): JsonResponse
    {
        Gate::authorize('view', $grade);
        return response()->json(['grade' => $grade->load(['submission.assignment.course', 'submission.student', 'grader'])]);
    }

    public function store(Request $request, Submission $submission): mixed
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

        if ($isNew || $grade->wasChanged(['score', 'feedback'])) {
            GradeReleased::dispatch($grade);
        }

        if ($request->wantsJson()) {
            return response()->json(['grade' => $grade], 201);
        }

        return redirect()->back()->with(
            'success',
            'Nilai dan umpan balik tugas berhasil disimpan.'
        );

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
