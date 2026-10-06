<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeSubmissionRequest;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Resources\GradeResource;
use App\Http\Resources\SubmissionResource;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

    public function store(StoreSubmissionRequest $request, Assignment $assignment)
    {
        $user = $request->user();

        if ($assignment->submissions()->where('user_id', $user->id)->exists()) {
            abort(409, 'Anda sudah mengumpulkan tugas ini.');
        }

        $isLate = now()->greaterThan($assignment->due_at);

        if ($isLate && ! $assignment->allow_late) {
            throw ValidationException::withMessages([
                'file' => ['Tenggat pengumpulan sudah lewat dan tugas ini tidak menerima keterlambatan.'],
            ]);
        }

        $file = $request->file('file');
        $path = $file->store("submissions/{$assignment->id}", 'local');

        try {
            $submission = new Submission(['note' => $request->validated('note')]);

            $submission->assignment_id = $assignment->id;
            $submission->user_id       = $user->id;
            $submission->file_path     = $path;
            $submission->original_name = basename($file->getClientOriginalName());
            $submission->file_size     = $file->getSize();
            $submission->submitted_at  = now();
            $submission->is_late       = $isLate;
            $submission->save();
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return (new SubmissionResource($submission->load('student')))
            ->response()
            ->setStatusCode(201);
    }

    public function grade(GradeSubmissionRequest $request, Submission $submission)
    {
        // Kepemilikan sudah dicek di GradeSubmissionRequest::authorize().
        $grade = $submission->grade ?? new Grade();

        // Hanya score dan feedback yang fillable.
        $grade->fill($request->safe()->only(['score', 'feedback']));

        // Tiga kolom ini tidak fillable, diisi dari sumber tepercaya.
        $grade->submission_id = $submission->id;
        $grade->graded_by     = $request->user()->id;
        $grade->graded_at     = now();
        $grade->save();

        // 201 saat nilai baru dibuat, 200 saat diperbarui.
        return (new GradeResource($grade->load('grader')))
            ->response()
            ->setStatusCode($grade->wasRecentlyCreated ? 201 : 200);
    }
}