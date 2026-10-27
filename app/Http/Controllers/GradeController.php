<?php

namespace App\Http\Controllers;

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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'course' => ['id' => $course->id, 'name' => $course->name],
                'grades' => Grade::with(['submission.assignment', 'submission.student', 'grader'])
                    ->whereHas('submission.assignment', fn ($query) => $query->where('course_id', $course->id))
                    ->latest()->paginate(20),
            ]);
        }

        $course->load(['lecturer']);

        // Seluruh tugas pada mata kuliah ini
        $assignments = $course->assignments()
            ->orderBy('due_at', 'asc')
            ->get();

        // Seluruh mahasiswa yang terdaftar
        $students = $course->students()
            ->orderBy('name', 'asc')
            ->get();

        // Seluruh submission untuk tugas di course ini beserta relasi nilai dan mahasiswa
        $submissions = Submission::with(['grade.grader', 'student'])
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get();

        // Matriks pengumpulan: [user_id][assignment_id] => Submission
        $submissionMatrix = [];
        foreach ($submissions as $sub) {
            $submissionMatrix[$sub->user_id][$sub->assignment_id] = $sub;
        }

        // Hitung rata-rata per mahasiswa
        $studentAverages = [];
        foreach ($students as $student) {
            $scores = [];
            foreach ($assignments as $assignment) {
                $sub = $submissionMatrix[$student->id][$assignment->id] ?? null;
                if ($sub && $sub->grade !== null) {
                    $scores[] = (float) $sub->grade->score;
                }
            }
            $studentAverages[$student->id] = count($scores) > 0
                ? round(array_sum($scores) / count($scores), 1)
                : null;
        }

        // Hitung rata-rata per tugas untuk ringkasan footer kolom
        $assignmentAverages = [];
        foreach ($assignments as $assignment) {
            $asgScores = [];
            foreach ($students as $student) {
                $sub = $submissionMatrix[$student->id][$assignment->id] ?? null;
                if ($sub && $sub->grade !== null) {
                    $asgScores[] = (float) $sub->grade->score;
                }
            }
            $assignmentAverages[$assignment->id] = count($asgScores) > 0
                ? round(array_sum($asgScores) / count($asgScores), 1)
                : null;
        }

        $totalAssignments = $assignments->count();
        $totalStudents = $students->count();
        $totalSubmissions = $submissions->count();
        $totalGraded = $submissions->filter(fn ($s) => $s->grade !== null)->count();
        $totalPending = $totalSubmissions - $totalGraded;

        $scoredAverages = array_filter($studentAverages, fn ($avg) => $avg !== null);
        $classAverage = count($scoredAverages) > 0
            ? round(array_sum($scoredAverages) / count($scoredAverages), 1)
            : null;

        return view('grades.course', compact(
            'course',
            'assignments',
            'students',
            'submissionMatrix',
            'studentAverages',
            'assignmentAverages',
            'totalAssignments',
            'totalStudents',
            'totalSubmissions',
            'totalGraded',
            'totalPending',
            'classAverage'
        ));
    }

    public function indexMine(Request $request): mixed
    {
        Gate::authorize('viewAny', Grade::class);
        $user = $request->user();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'grades' => Grade::with(['submission.assignment.course', 'grader'])
                    ->whereHas('submission', fn ($query) => $query->where('user_id', $user->id))
                    ->latest()->paginate(20),
            ]);
        }

        // Ambil semua submission milik mahasiswa beserta relasi tugas, mata kuliah, nilai, dan dosen penilai
        $submissions = Submission::with(['assignment.course.lecturer', 'grade.grader'])
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->get();

        $totalSubmitted = $submissions->count();
        $gradedSubmissions = $submissions->filter(fn ($s) => $s->grade !== null);
        $totalGraded = $gradedSubmissions->count();
        $averageScore = $totalGraded > 0
            ? round($gradedSubmissions->avg(fn ($s) => (float) $s->grade->score), 1)
            : null;

        // Ambil mata kuliah yang di-enroll oleh mahasiswa
        $enrolledCourses = $user->courses()->with('lecturer')->get();

        // Kelompokkan data per mata kuliah
        $courseGroups = collect();
        foreach ($enrolledCourses as $course) {
            $courseSubs = $submissions->filter(fn ($s) => $s->assignment && $s->assignment->course_id === $course->id);
            $graded = $courseSubs->filter(fn ($s) => $s->grade !== null);
            $avg = $graded->isNotEmpty() ? round($graded->avg(fn ($s) => (float) $s->grade->score), 1) : null;

            $courseGroups->put($course->id, (object) [
                'course' => $course,
                'submissions' => $courseSubs,
                'total_submitted' => $courseSubs->count(),
                'total_graded' => $graded->count(),
                'average_score' => $avg,
            ]);
        }

        // Tangani jika ada submission yang mata kuliahnya belum ada di courseGroups (misal un-enroll atau relasi lepas)
        $orphanSubs = $submissions->filter(fn ($s) => $s->assignment && !$courseGroups->has($s->assignment->course_id));
        if ($orphanSubs->isNotEmpty()) {
            foreach ($orphanSubs->groupBy(fn ($s) => $s->assignment->course_id) as $cId => $subs) {
                $firstCourse = $subs->first()->assignment->course;
                $graded = $subs->filter(fn ($s) => $s->grade !== null);
                $avg = $graded->isNotEmpty() ? round($graded->avg(fn ($s) => (float) $s->grade->score), 1) : null;

                $courseGroups->put($cId, (object) [
                    'course' => $firstCourse,
                    'submissions' => $subs,
                    'total_submitted' => $subs->count(),
                    'total_graded' => $graded->count(),
                    'average_score' => $avg,
                ]);
            }
        }

        return view('grades.mine', compact(
            'submissions',
            'totalSubmitted',
            'totalGraded',
            'averageScore',
            'courseGroups'
        ));
    }

    public function show(Request $request, Grade $grade): JsonResponse
    {
        Gate::authorize('view', $grade);
        return response()->json(['grade' => $grade->load(['submission.assignment.course', 'submission.student', 'grader'])]);
    }

    public function store(Request $request, Submission $submission)
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

        if ($request->wantsJson()) {
            return response()->json(['grade' => $grade], 201);
        }

        return redirect()->back()->with('success', 'Nilai dan umpan balik tugas berhasil disimpan.');
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

        return response()->json(['grade' => $grade->fresh(['submission.assignment.course', 'grader'])]);
    }

    public function destroy(Request $request, Grade $grade): JsonResponse
    {
        Gate::authorize('delete', $grade);
        $grade->delete();

        return response()->json(['message' => 'Nilai berhasil dihapus.']);
    }
}
