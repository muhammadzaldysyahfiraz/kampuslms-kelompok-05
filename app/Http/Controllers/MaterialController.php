<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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
            'message' => 'Form materi siap diisi.',
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
            // mimes memeriksa isi berkas, bukan cuma ekstensi. max dalam KB (20480 = 20 MB).
            'file' => ['nullable', 'required_if:type,file', 'file', 'mimes:pdf,pptx,docx', 'max:20480'],
        ]);

        $material = new Material([
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'external_url' => $data['type'] === 'link' ? $data['external_url'] : null,
        ]);
        // Kolom ini sengaja tidak fillable, jadi diisi manual dari sisi server.
        $material->course_id = $course->id;
        $material->uploaded_by = $request->user()->id;

        $storedPath = null;

        if ($data['type'] === 'file') {
            $file = $request->file('file');

            // Metadata diambil dari server, bukan dari klaim klien.
            $material->original_name = Str::limit($file->getClientOriginalName(), 255, '');
            $material->file_size = $file->getSize();
            $material->mime_type = $file->getMimeType();

            // Nama acak di disk privat. Nama asli TIDAK dipakai sebagai nama di disk.
            $storedPath = $file->store("materials/{$course->id}", 'local');
            abort_if($storedPath === false, 500, 'Berkas gagal disimpan.');
            $material->file_path = $storedPath;
        }

        try {
            $material->save();
        } catch (Throwable $e) {
            // Jangan tinggalkan berkas yatim kalau insert DB gagal.
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $e;
        }

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

        return $this->materialResponse($material->course, $material);
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

    /** Unduh berkas materi. Akses dicek lewat Policy, bukan lewat URL. */
    public function download(Material $material): StreamedResponse
    {
        Gate::authorize('view', $material);

        abort_unless($material->type === 'file' && $material->file_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($material->file_path), 404);

        return $disk->download($material->file_path, $material->original_name);
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