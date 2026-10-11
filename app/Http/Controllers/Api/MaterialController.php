<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialResource;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaterialController extends Controller
{
    public function index(Request $request, Course $course)
    {
        Gate::authorize('viewAny', Material::class);
        Gate::authorize('view', $course);

        $materials = $course->materials()
            ->with('uploader')
            ->latest('id')
            ->paginate(15);

        return MaterialResource::collection($materials);
    }
}
