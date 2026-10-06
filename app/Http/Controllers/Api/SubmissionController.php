<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request, Assignment $assignment)
    {
        $user = $request->user();

        // TODO minggu 7: dipindah ke SubmissionPolicy
        abort_unless(
            $user->role === 'dosen' && $assignment->course->lecturer_id === $user->id,
            403,
            'Anda tidak memiliki akses ke sumber daya ini.'
        );

        $submissions = $assignment->submissions()
            ->with(['student', 'grade'])
            ->latest('id')
            ->paginate(15);

        return SubmissionResource::collection($submissions);
    }
}