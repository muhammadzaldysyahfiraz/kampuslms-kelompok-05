<?php

namespace App\Http\Controllers;

use App\Events\AssignmentPublished;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    private function ensureCourseManager(Request $request, Course $course): void
    {
        Gate::authorize('create', [Assignment::class, $course]);
    }

    private function ensureAssignmentManager(Assignment $assignment, string $ability): void
    {
        $assignment->loadMissing('course');
        Gate::authorize($ability, $assignment);
    }

    public function index(Request $request, Course $course): mixed
    {
        Gate::authorize('viewAny', Assignment::class);
        Gate::authorize('view', $course);
        $user = $request->user();

        if (! $request->expectsJson()) {
            return redirect()->route('courses.show', $course);
        }

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'assignments' => $course->assignments()->when($user->role === 'mahasiswa', fn ($q) => $q->where('status', 'published'))->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request, Course $course): mixed
    {
        $this->ensureCourseManager($request, $course);

        if (! $request->expectsJson()) {
            return view('assignments.create', compact('course'));
        }

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'message' => 'Form metadata tugas siap diisi.',
        ]);
    }

    public function store(Request $request, Course $course): mixed
    {
        $this->ensureCourseManager($request, $course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string'],
            'due_at' => ['required', 'date'],
            'max_score' => ['required', 'integer', 'between:1,100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $assignment = $course->assignments()->make($data);
        $assignment->created_by = $request->user()->id;
        $assignment->status = $data['status'] ?? 'draft';
        $assignment->allow_late = $data['allow_late'] ?? true;
        $assignment->save();

        if ($assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

        if ($request->expectsJson()) {
            return response()->json(['assignment' => $assignment], 201);
        }

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    /** Shallow detail route: /assignments/{assignment}. */
    public function show(Request $request, Assignment $assignment): mixed
    {
        Gate::authorize('view', $assignment);
        $assignment->load(['course.lecturer'])->loadCount('submissions');
        $assignment->course->loadCount('students');
        abort_if($request->user()->role === 'mahasiswa' && $assignment->status !== 'published', 404);

        if ($request->expectsJson()) {
            return $this->assignmentResponse($assignment->course, $assignment);
        }

        $mySubmission = $request->user()->role === 'mahasiswa'
            ? $assignment->submissions()->where('user_id', $request->user()->id)->with('grade')->first()
            : null;
        $submittedCount = $assignment->submissions_count;

        $submissions = collect();
        $enrolledStudents = collect();

        if ($request->user() && $request->user()->can('viewSubmissions', $assignment)) {
            $assignment->load([
                'submissions' => fn ($q) => $q->with(['student', 'grade'])->latest('submitted_at'),
                'course.students' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.nim_nip')->orderBy('name'),
            ]);
            $submissions = $assignment->submissions;
            $enrolledStudents = $assignment->course->students;
        }

        return view('assignments.show', [
            'assignment' => $assignment,
            'course' => $assignment->course,
            'mySubmission' => $mySubmission,
            'submittedCount' => $submittedCount,
            'submissions' => $submissions,
            'enrolledStudents' => $enrolledStudents,
        ]);
    }

    public function edit(Request $request, Assignment $assignment): mixed
    {
        $this->ensureAssignmentManager($assignment, 'update');

        if (! $request->expectsJson()) {
            return view('assignments.edit', [
                'assignment' => $assignment,
                'course' => $assignment->course,
            ]);
        }

        return response()->json(['assignment' => $assignment->load('course')]);
    }

    public function update(Request $request, Assignment $assignment): mixed
    {
        $this->ensureAssignmentManager($assignment, 'update');
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'required', 'integer', 'between:1,100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $wasPublished = $assignment->status === 'published';
        $assignment->fill(collect($data)->only([
            'title', 'instructions', 'due_at', 'max_score', 'allow_late',
        ])->all());
        if (array_key_exists('status', $data)) {
            $assignment->status = $data['status'];
        }
        $assignment->save();

        if (! $wasPublished && $assignment->status === 'published') {
            AssignmentPublished::dispatch($assignment);
        }

        if ($request->expectsJson()) {
            return response()->json(['assignment' => $assignment->fresh('course')]);
        }

        return redirect()->route('assignments.show', $assignment)
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Assignment $assignment): mixed
    {
        $this->ensureAssignmentManager($assignment, 'delete');
        $course = $assignment->course;
        $assignment->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tugas berhasil dihapus.']);
        }

        return redirect()->route('courses.show', $course)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    /** Explicit nested detail route used to demonstrate scoped binding. */
    public function showNested(Request $request, Course $course, Assignment $assignment): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless((int) $assignment->course_id === (int) $course->id, 404);
        abort_unless($user->role !== 'dosen' || (int) $course->lecturer_id === (int) $user->id, 403);
        abort_if($user->role === 'mahasiswa' && $assignment->status !== 'published', 404);

        if ($request->expectsJson()) {
            return $this->assignmentResponse($course, $assignment);
        }

        $mySubmission = $user->role === 'mahasiswa'
            ? $assignment->submissions()->where('user_id', $user->id)->with('grade')->first()
            : null;
        $submittedCount = $assignment->submissions()->count();
        $course->loadCount('students');

        $submissions = collect();
        $enrolledStudents = collect();

        if ($user->can('viewSubmissions', $assignment)) {
            $assignment->load([
                'submissions' => fn ($q) => $q->with(['student', 'grade'])->latest('submitted_at'),
                'course.students' => fn ($q) => $q->select('users.id', 'users.name', 'users.email', 'users.nim_nip')->orderBy('name'),
            ]);
            $submissions = $assignment->submissions;
            $enrolledStudents = $assignment->course->students;
        }

        return view('assignments.show', [
            'assignment' => $assignment,
            'course' => $course,
            'mySubmission' => $mySubmission,
            'submittedCount' => $submittedCount,
            'submissions' => $submissions,
            'enrolledStudents' => $enrolledStudents,
        ]);
    }

    private function assignmentResponse(Course $course, Assignment $assignment): JsonResponse
    {
        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'instructions' => $assignment->instructions,
                'due_at' => $assignment->due_at,
                'max_score' => $assignment->max_score,
                'allow_late' => $assignment->allow_late,
                'status' => $assignment->status,
            ],
        ]);
    }
}
