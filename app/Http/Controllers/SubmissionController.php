<?php // Menandai awal file PHP.

namespace App\Http\Controllers; // Menentukan namespace controller.

use App\Models\Submission; // Mengimpor model Submission agar data submission bisa diambil.
use Illuminate\Http\Request; // Mengimpor Request untuk membaca request yang masuk.
use Illuminate\Http\JsonResponse;

class SubmissionController extends Controller // Membuat controller untuk menangani submission.
{
    /** List submissions filtered to the authenticated user's access scope. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

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

        return response()->json(['submissions' => $query->latest()->paginate(20)]);
    }

    public function show(Request $request, Submission $submission) // Menampilkan satu submission menggunakan route model binding.
    {
        $user = $request->user(); // Mengambil user yang sedang login dari request.

        abort_unless($user, 401); // Jika tidak ada user yang login, hentikan request dengan status 401.

        abort_unless( // Memastikan user yang membuka submission memang berhak mengaksesnya.
            (int) $submission->user_id === (int) $user->id // Mahasiswa boleh melihat submission miliknya sendiri.
                || $user->role === 'admin' // Admin boleh melihat submission.
                || (
                    $user->role === 'dosen'
                    && (int) $submission->assignment->course->lecturer_id === (int) $user->id
                ), // Hanya dosen pengampu course yang boleh melihat submission.
            403 // Jika tidak memenuhi kondisi di atas, kembalikan status 403.
        );

        $submission->load(['assignment.course', 'student', 'grade']); // Memuat data tugas, course, mahasiswa, dan nilai yang berhubungan.

        $activeRole = $user->role; // Mengambil role asli dari akun yang terautentikasi.
        return view('submissions.show', compact('submission', 'activeRole')); // Mengirim data ke halaman detail.
    }
}