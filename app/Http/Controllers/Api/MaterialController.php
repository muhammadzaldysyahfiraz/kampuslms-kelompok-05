<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request, Course $course)
    {
        // TODO minggu 7: dipindah ke CoursePolicy
        abort_unless(
            $course->isAccessibleBy($request->user()),
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $materials = $course->materials()
            ->with('uploader')
            ->latest('id')
            ->paginate(15);

        return MaterialResource::collection($materials);
    }
}