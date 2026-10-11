<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        Gate::authorize('viewAny', Course::class);

        $courses = (match ($user->role) {
            'admin'     => Course::query(),
            'dosen'     => $user->taughtCourses(),
            'mahasiswa' => $user->courses(),
            default     => abort(403, 'Anda tidak memiliki akses ke sumber daya ini.'),
        })
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->latest('courses.id')
            ->paginate(15);

        return CourseResource::collection($courses);
    }

    public function show(Request $request, int $id)
    {
        $course = Course::findOrFail($id);

        Gate::authorize('view', $course);

        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return new CourseResource($course);
    }

}
