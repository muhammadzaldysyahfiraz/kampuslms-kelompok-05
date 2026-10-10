<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    private function ensureCourseManager(Request $request, Course $course): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless(
            $user->role === 'admin'
                || ($user->role === 'dosen' && (int) $course->lecturer_id === (int) $user->id),
            403
        );
    }

    private function ensureAssignmentManager(Request $request, Assignment $assignment): void
    {
        $assignment->loadMissing('course');
        $this->ensureCourseManager($request, $assignment->course);
    }

    public function index(Request $request, Course $course): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role !== 'dosen' || (int) $course->lecturer_id === (int) $user->id, 403);

        if ($request->expectsJson()) {
            return response()->json([
                'course' => ['id' => $course->id, 'name' => $course->name],
                'assignments' => $course->assignments()->latest()->get(),
            ]);
        }

        return redirect()->route('courses.show', $course);
    }

    public function create(Request $request, Course $course): mixed
    {
        $this->ensureCourseManager($request, $course);

        if ($request->expectsJson()) {
            return response()->json([
                'course' => ['id' => $course->id, 'name' => $course->name],
                'message' => 'Form metadata tugas siap diisi.',
            ]);
        }

        return view('assignments.create', compact('course'));
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
        $assignment->allow_late = $data['allow_late'] ?? false;
        $assignment->save();

        if ($request->expectsJson()) {
            return response()->json(['assignment' => $assignment], 201);
        }

        return redirect()->route('courses.show', $course)->with('success', 'Tugas berhasil ditambahkan.');
    }

    /** Shallow detail route: /assignments/{assignment}. */
    public function show(Request $request, Assignment $assignment): mixed
    {
        abort_unless($request->user(), 401);
        $assignment->load(['course', 'submissions.user']);
        abort_unless(
            $request->user()->role !== 'dosen'
                || (int) $assignment->course->lecturer_id === (int) $request->user()->id,
            403
        );

        if ($request->expectsJson()) {
            return $this->assignmentResponse($assignment->course, $assignment);
        }

        return view('assignments.show', [
            'assignment' => $assignment,
            'course' => $assignment->course,
        ]);
    }

    public function edit(Request $request, Assignment $assignment): mixed
    {
        $this->ensureAssignmentManager($request, $assignment);
        $assignment->load('course');

        if ($request->expectsJson()) {
            return response()->json(['assignment' => $assignment]);
        }

        return view('assignments.edit', [
            'assignment' => $assignment,
            'course' => $assignment->course,
        ]);
    }

    public function update(Request $request, Assignment $assignment): mixed
    {
        $this->ensureAssignmentManager($request, $assignment);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'instructions' => ['sometimes', 'required', 'string'],
            'due_at' => ['sometimes', 'required', 'date'],
            'max_score' => ['sometimes', 'required', 'integer', 'between:1,100'],
            'allow_late' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'in:draft,published'],
        ]);

        $assignment->fill(collect($data)->only([
            'title', 'instructions', 'due_at', 'max_score', 'allow_late',
        ])->all());
        if (array_key_exists('status', $data)) {
            $assignment->status = $data['status'];
        }
        $assignment->save();

        if ($request->expectsJson()) {
            return response()->json(['assignment' => $assignment->fresh('course')]);
        }

        return redirect()->route('courses.show', $assignment->course)->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Request $request, Assignment $assignment): mixed
    {
        $this->ensureAssignmentManager($request, $assignment);
        $course = $assignment->course;
        $assignment->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tugas berhasil dihapus.']);
        }

        return redirect()->route('courses.show', $course)->with('success', 'Tugas berhasil dihapus.');
    }

    /** Explicit nested detail route used to demonstrate scoped binding. */
    public function showNested(Request $request, Course $course, Assignment $assignment): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless((int) $assignment->course_id === (int) $course->id, 404);
        abort_unless($user->role !== 'dosen' || (int) $course->lecturer_id === (int) $user->id, 403);

        if ($request->expectsJson()) {
            return $this->assignmentResponse($course, $assignment);
        }

        $assignment->load(['course', 'submissions.user']);
        return view('assignments.show', compact('course', 'assignment'));
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
