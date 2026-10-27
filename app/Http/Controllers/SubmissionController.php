<?php // Menandai awal file PHP.

namespace App\Http\Controllers; // Menentukan namespace controller.

use App\Models\Submission; // Mengimpor model Submission agar data submission bisa diambil.
use Illuminate\Http\Request; // Mengimpor Request untuk membaca request yang masuk.
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class SubmissionController extends Controller // Membuat controller untuk menangani submission.
{
    /** List submissions filtered to the authenticated user's access scope. */
    public function index(Request $request): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);
        Gate::authorize('viewAny', Submission::class);

        $query = Submission::with(['assignment.course', 'student', 'grade']);
        if ($user->role === 'mahasiswa') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'dosen') {
            $query->whereHas('assignment.course', fn ($courseQuery) =>
                $courseQuery->where('lecturer_id', $user->id)
            );
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $submissions = $query->latest('submitted_at')->paginate(20)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['submissions' => $submissions]);
        }

        return view('submissions.index', compact('submissions'));
    }

    public function show(Request $request, Submission $submission) // Menampilkan satu submission menggunakan route model binding.
    {
        Gate::authorize('view', $submission);
        $user = $request->user();

        $submission->load(['assignment.course', 'student', 'grade']); // Memuat data tugas, course, mahasiswa, dan nilai yang berhubungan.

        $activeRole = $user->role; // Mengambil role asli dari akun yang terautentikasi.
        return view('submissions.show', compact('submission', 'activeRole')); // Mengirim data ke halaman detail.
    }
}
