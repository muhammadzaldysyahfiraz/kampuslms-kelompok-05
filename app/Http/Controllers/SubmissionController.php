<?php // Menandai awal file PHP.

namespace App\Http\Controllers; // Menentukan namespace controller.

use App\Models\Assignment;
use App\Models\Submission; // Mengimpor model Submission agar data submission bisa diambil.
use Illuminate\Http\Request; // Mengimpor Request untuk membaca request yang masuk.
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SubmissionController extends Controller // Membuat controller untuk menangani submission.
{
    /** List submissions filtered to the authenticated user's access scope. */
    public function index(Request $request): JsonResponse
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

        return response()->json(['submissions' => $query->latest()->paginate(20)]);
    }

    public function show(Request $request, Submission $submission) // Menampilkan satu submission menggunakan route model binding.
    {
        Gate::authorize('view', $submission);
        $user = $request->user();

        $submission->load(['assignment.course', 'student', 'grade']); // Memuat data tugas, course, mahasiswa, dan nilai yang berhubungan.

        $activeRole = $user->role; // Mengambil role asli dari akun yang terautentikasi.
        return view('submissions.show', compact('submission', 'activeRole')); // Mengirim data ke halaman detail.
    }

    /**
     * Menyimpan atau memperbarui berkas pengumpulan tugas mahasiswa (Minggu 9).
     * Menggunakan updateOrCreate untuk menegakkan aturan unique composite (assignment_id, user_id).
     */
    public function store(Request $request, Assignment $assignment): mixed
    {
        $user = $request->user();
        abort_unless($user, 401);

        Gate::authorize('create', [Submission::class, $assignment]);

        // Periksa apakah deadline sudah lewat dan pengumpulan terlambat tidak diizinkan.
        $isLate = now()->gt($assignment->due_at);

        if ($isLate && ! $assignment->allow_late) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tenggat waktu pengumpulan sudah lewat dan tugas ini tidak menerima keterlambatan.',
                    'errors' => ['file' => ['Tenggat waktu pengumpulan sudah lewat dan tugas ini tidak menerima keterlambatan.']],
                ], 422);
            }

            return back()->withErrors([
                'file' => 'Tenggat waktu pengumpulan sudah lewat dan tugas ini tidak menerima keterlambatan.',
            ])->withInput();
        }

        // Validasi berkas: mimes memeriksa magic bytes berkas, max dalam satuan KB (5120 = 5 MB).
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'file.required' => 'Berkas tugas wajib diunggah.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.mimes' => 'Format berkas harus berupa PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran berkas maksimal adalah 5 MB.',
        ]);

        $file = $request->file('file');

        // Berkas disimpan di disk privat 'local' (storage/app/private/submissions/{id}) dengan nama acak.
        $storedPath = $file->store("submissions/{$assignment->id}", 'local');
        abort_if($storedPath === false, 500, 'Berkas gagal disimpan ke penyimpanan privat server.');

        // Ambil submission lama jika mahasiswa mengumpulkan ulang
        $existing = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->first();
        $oldPath = $existing?->file_path;

        try {
            $submission = $existing ?? new Submission();
            $submission->assignment_id = $assignment->id;
            $submission->user_id = $user->id;
            $submission->file_path = $storedPath;
            $submission->original_name = Str::limit($file->getClientOriginalName(), 255, '');
            $submission->file_size = $file->getSize();
            $submission->note = $validated['note'] ?? null;
            $submission->submitted_at = now();
            $submission->is_late = $isLate;
            $submission->save();
        } catch (Throwable $e) {
            // Rollback berkas fisik jika penyimpanan basis data gagal
            Storage::disk('local')->delete($storedPath);
            throw $e;
        }

        // Bersihkan berkas lama di disk fisik jika pengumpulan ulang berhasil menggantikannya
        if ($oldPath && $oldPath !== $storedPath) {
            Storage::disk('local')->delete($oldPath);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tugas berhasil dikumpulkan.',
                'submission' => $submission->load(['student', 'assignment']),
            ], 201);
        }

        return back()->with('success', $oldPath ? 'Tugas berhasil diperbarui dengan berkas baru.' : 'Tugas berhasil dikumpulkan.');
    }

    /**
     * Mengunduh berkas pengumpulan tugas dengan verifikasi otorisasi Policy (Minggu 9).
     */
    public function download(Submission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);

        abort_unless($submission->file_path, 404, 'Berkas pengumpulan tidak tercatat di sistem.');

        $disk = Storage::disk('local');
        abort_unless($disk->exists($submission->file_path), 404, 'Berkas fisik tidak ditemukan di penyimpanan server.');

        return $disk->download(
            $submission->file_path,
            $submission->original_name ?: 'submission-' . $submission->id . '.pdf'
        );
    }

    /**
     * Menghapus submission dan membersihkan berkas fisiknya.
     */
    public function destroy(Submission $submission): mixed
    {
        Gate::authorize('delete', $submission);

        $filePath = $submission->file_path;
        $submission->delete();

        if ($filePath) {
            Storage::disk('local')->delete($filePath);
        }

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Pengumpulan tugas berhasil dihapus.']);
        }

        return back()->with('success', 'Pengumpulan tugas berhasil dihapus.');
    }
}
