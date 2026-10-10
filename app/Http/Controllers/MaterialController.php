<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialController extends Controller
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

    private function ensureMaterialManager(Request $request, Material $material): void
    {
        $material->loadMissing('course');
        $this->ensureCourseManager($request, $material->course);
    }

    public function index(Request $request, Course $course): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless($user->role !== 'dosen' || (int) $course->lecturer_id === (int) $user->id, 403);

        if ($request->expectsJson()) {
            return response()->json([
                'course' => ['id' => $course->id, 'name' => $course->name],
                'materials' => $course->materials()->latest()->get(),
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
                'message' => 'Form metadata materi siap diisi. Unggah berkas belum termasuk Week 5.',
            ]);
        }

        return view('materials.create', compact('course'));
    }

    public function store(Request $request, Course $course): mixed
    {
        $this->ensureCourseManager($request, $course);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'type' => ['required', 'in:file,link'],
            'external_url' => ['nullable', 'url', 'max:2048', 'required_if:type,link'],
        ]);

        $material = $course->materials()->make(collect($data)->only([
            'title', 'description', 'type', 'external_url',
        ])->all());
        $material->uploaded_by = $request->user()->id;
        $material->save();

        if ($request->expectsJson()) {
            return response()->json(['material' => $material], 201);
        }

        return redirect()->route('courses.show', $course)->with('success', 'Materi berhasil ditambahkan.');
    }

    /** Shallow detail route: /materials/{material}. */
    public function show(Request $request, Material $material): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        $material->load(['course', 'uploader']);
        abort_unless(
            $user->role !== 'dosen'
                || (int) $material->course->lecturer_id === (int) $user->id,
            403
        );

        if ($request->expectsJson()) {
            return $this->materialResponse($material->course, $material);
        }

        return view('materials.show', [
            'material' => $material,
            'course' => $material->course,
        ]);
    }

    public function edit(Request $request, Material $material): mixed
    {
        $this->ensureMaterialManager($request, $material);
        $material->load('course');

        if ($request->expectsJson()) {
            return response()->json(['material' => $material]);
        }

        return view('materials.edit', [
            'material' => $material,
            'course' => $material->course,
        ]);
    }

    public function update(Request $request, Material $material): mixed
    {
        $this->ensureMaterialManager($request, $material);
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', 'in:file,link'],
            'external_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $material->fill(collect($data)->only([
            'title', 'description', 'type', 'external_url',
        ])->all());
        $material->save();

        if ($request->expectsJson()) {
            return response()->json(['material' => $material->fresh('course')]);
        }

        return redirect()->route('courses.show', $material->course)->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Material $material): mixed
    {
        $this->ensureMaterialManager($request, $material);
        $course = $material->course;
        $material->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Materi berhasil dihapus.']);
        }

        return redirect()->route('courses.show', $course)->with('success', 'Materi berhasil dihapus.');
    }

    /** Explicit nested detail route used to demonstrate scoped binding. */
    public function showNested(Request $request, Course $course, Material $material): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless((int) $material->course_id === (int) $course->id, 404);
        abort_unless($user->role !== 'dosen' || (int) $course->lecturer_id === (int) $user->id, 403);

        if ($request->expectsJson()) {
            return $this->materialResponse($course, $material);
        }

        $material->load(['course', 'uploader']);
        return view('materials.show', compact('course', 'material'));
    }

    private function materialResponse(Course $course, Material $material): JsonResponse
    {
        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'material' => [
                'id' => $material->id,
                'title' => $material->title,
                'description' => $material->description,
                'type' => $material->type,
                'external_url' => $material->external_url,
            ],
        ]);
    }
}
