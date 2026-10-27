<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    private function ensureCourseManager(Request $request, Course $course): void
    {
        Gate::authorize('create', [Material::class, $course]);
    }

    private function ensureMaterialManager(Material $material, string $ability): void
    {
        $material->loadMissing('course');
        Gate::authorize($ability, $material);
    }

    public function index(Request $request, Course $course): mixed
    {
        Gate::authorize('viewAny', Material::class);
        Gate::authorize('view', $course);

        if (! $request->expectsJson()) {
            return redirect()->route('courses.show', $course);
        }

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'materials' => $course->materials()->latest()->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request, Course $course): mixed
    {
        $this->ensureCourseManager($request, $course);

        if (! $request->expectsJson()) {
            return view('materials.create', compact('course'));
        }

        return response()->json([
            'course' => ['id' => $course->id, 'name' => $course->name],
            'message' => 'Form metadata materi siap diisi. Unggah berkas belum termasuk Week 5.',
        ]);
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

        return redirect()->route('materials.show', $material)
            ->with('success', 'Materi berhasil ditambahkan.');
    }

    /** Shallow detail route: /materials/{material}. */
    public function show(Request $request, Material $material): mixed
    {
        Gate::authorize('view', $material);
        $material->load(['course.lecturer', 'uploader']);

        if (! $request->expectsJson()) {
            return view('materials.show', [
                'material' => $material,
                'course' => $material->course,
            ]);
        }

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
        $this->ensureMaterialManager($material, 'update');

        if (! $request->expectsJson()) {
            return view('materials.edit', [
                'material' => $material,
                'course' => $material->course,
            ]);
        }

        return response()->json(['material' => $material->load('course')]);
    }

    public function update(Request $request, Material $material): mixed
    {
        $this->ensureMaterialManager($material, 'update');
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', 'in:file,link'],
            'external_url' => ['nullable', 'url', 'max:2048', 'required_if:type,link'],
        ]);

        $material->fill(collect($data)->only([
            'title', 'description', 'type', 'external_url',
        ])->all());
        $material->save();

        if ($request->expectsJson()) {
            return response()->json(['material' => $material->fresh('course')]);
        }

        return redirect()->route('materials.show', $material)
            ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Request $request, Material $material): mixed
    {
        $this->ensureMaterialManager($material, 'delete');
        $course = $material->course;
        $material->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Materi berhasil dihapus.']);
        }

        return redirect()->route('courses.show', $course)
            ->with('success', 'Materi berhasil dihapus.');
    }

    /** Explicit nested detail route used to demonstrate scoped binding. */
    public function showNested(Request $request, Course $course, Material $material): mixed
    {
        abort_unless((int) $material->course_id === (int) $course->id, 404);
        Gate::authorize('view', $material);

        if ($request->expectsJson()) {
            return $this->materialResponse($course, $material);
        }

        $material->load(['course', 'uploader']);
        return view('materials.show', compact('course', 'material'));
    }

    private function assignmentResponse(Course $course, Material $material): JsonResponse
    {
        return $this->materialResponse($course, $material);
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
