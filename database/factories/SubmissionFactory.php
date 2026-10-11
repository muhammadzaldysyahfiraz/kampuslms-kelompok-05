<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Submission>
 */
class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        $notes = [
            'Tugas praktikum telah diselesaikan secara lengkap sesuai dengan instruksi modul. Seluruh kode program dan laporan pengujian terlampir.',
            'Berikut berkas laporan pengumpulan tugas saya. Semua fitur utama dan penanganan validasi telah diuji dan berjalan normal.',
            'Laporan perancangan sistem dan diagram arsitektur telah selesai disusun sesuai format ketentuan. Terima kasih banyak, Bapak/Ibu.',
            'Pengumpulan tugas praktikum tepat waktu. Seluruh fungsi telah divalidasi dan siap untuk diperiksa.',
            'Tugas telah dikerjakan secara mandiri. Dokumentasi analisis dan screenshot hasil pengujian disertakan di dalam berkas PDF.',
        ];

        return [
            'assignment_id' => Assignment::inRandomOrder()->value('id'),
            'user_id' => User::where('role', 'mahasiswa')
                ->inRandomOrder()
                ->value('id'),
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'original_name' => 'tugas-praktikum.pdf',
            'file_size' => fake()->numberBetween(120000, 3500000),
            'note' => fake()->randomElement($notes),
            'submitted_at' => now(),
            'is_late' => false,
        ];
    }
}