<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Http\Requests\UpdateAssignmentRequest;
use App\Http\Resources\AssignmentResource;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function index(Request $request, Course $course)
    {
        $user = $request->user();

        Gate::authorize('viewAny', Assignment::class);
        Gate::authorize('view', $course);

        $validated = $request->validate([
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $assignments = $course->assignments()
            ->when($user->role === 'mahasiswa', fn ($q) => $q->where('status', 'published'))
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return AssignmentResource::collection($assignments);
    }

    public function store(StoreAssignmentRequest $request)
    {
        $user = $request->user();
        $course = Course::findOrFail($request->validated('course_id'));
        Gate::authorize('create', [Assignment::class, $course]);

        $assignment = new Assignment($request->safe()->only([
            'title',
            'instructions',
            'due_at',
            'max_score',
            'allow_late',
        ]));

        $assignment->course_id = $course->id;
        $assignment->created_by = $user->id;
        $assignment->status = $request->validated('status') ?? 'draft';
        $assignment->save();

        return (new AssignmentResource($assignment))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment)
    {
        $data = $request->validated();
        Gate::authorize('update', $assignment);

        $assignment->fill(collect($data)->except('status')->all());

        if (array_key_exists('status', $data)) {
            $assignment->status = $data['status'];
        }

        $assignment->save();

        return new AssignmentResource($assignment);
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        Gate::authorize('delete', $assignment);

        $assignment->delete();

        return response()->noContent();
    }
}
