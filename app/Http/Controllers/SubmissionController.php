<?php // Menandai awal file PHP.

namespace App\Http\Controllers; // Menentukan namespace controller.

use App\Models\Submission; // Mengimpor model Submission agar data submission bisa diambil.
use Illuminate\Http\Request; // Mengimpor Request untuk membaca request yang masuk.

class SubmissionController extends Controller // Membuat controller untuk menangani submission.
{
    public function show(Request $request, Submission $submission) // Menampilkan satu submission menggunakan route model binding.
    {
        $user = $request->user(); // Mengambil user yang sedang login dari request.

        abort_unless($user, 401); // Jika tidak ada user yang login, hentikan request dengan status 401.

        abort_unless( // Memastikan user yang membuka submission memang berhak mengaksesnya.
            $submission->user_id === $user->id // Mahasiswa boleh melihat submission miliknya sendiri.
                || $user->role === 'admin' // Admin boleh melihat submission.
                || $submission->assignment->course->lecturer_id === $user->id, // Dosen pengampu course boleh melihat submission.
            403 // Jika tidak memenuhi kondisi di atas, kembalikan status 403.
        );

        $submission->load(['assignment.course', 'student', 'grade']); // Memuat data tugas, course, mahasiswa, dan nilai yang berhubungan.

        return view('submissions.show', compact('submission')); // Mengirim submission ke halaman detail.
    }
}