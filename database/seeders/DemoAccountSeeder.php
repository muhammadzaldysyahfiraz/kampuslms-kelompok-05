<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoAccountSeeder extends Seeder
{
    /**
     * Jalankan seeder akun demo produksi.
     *
     * Seeder ini bertanggung jawab menginisialisasi atau memperbarui kata sandi
     * ketiga akun demo wajib pada lingkungan produksi dengan kata sandi kuat
     * yang dibaca dari variabel konfigurasi config('demo.*').
     */
    public function run(): void
    {
        $isProduction = app()->isProduction();

        $accounts = [
            [
                'email' => 'admin@kampuslms.test',
                'name' => 'Admin KampusLMS',
                'role' => 'admin',
                'nim_nip' => 'ADM001',
                'config_key' => 'demo.admin_password',
                'label' => 'Admin Demo',
            ],
            [
                'email' => 'dosen@kampuslms.test',
                'name' => 'Dosen Demo',
                'role' => 'dosen',
                'nim_nip' => 'DOS001',
                'config_key' => 'demo.dosen_password',
                'label' => 'Dosen Demo',
            ],
            [
                'email' => 'mahasiswa@kampuslms.test',
                'name' => 'Mahasiswa Demo',
                'role' => 'mahasiswa',
                'nim_nip' => 'MHS001',
                'config_key' => 'demo.mahasiswa_password',
                'label' => 'Mahasiswa Demo',
            ],
        ];

        foreach ($accounts as $acc) {
            $password = config($acc['config_key']);

            if ($isProduction) {
                if (empty($password)) {
                    throw new RuntimeException(
                        "Gagal menjalankan DemoAccountSeeder di produksi: Password untuk {$acc['label']} ({$acc['config_key']}) belum disetel di environment variables."
                    );
                }

                if (strlen($password) < 8) {
                    throw new RuntimeException(
                        "Gagal menjalankan DemoAccountSeeder di produksi: Password untuk {$acc['label']} harus minimal 8 karakter."
                    );
                }

                if (strtolower($password) === 'password') {
                    throw new RuntimeException(
                        "Gagal menjalankan DemoAccountSeeder di produksi: Password untuk {$acc['label']} tidak boleh menggunakan kata sandi default 'password'."
                    );
                }
            } else {
                // Di lokal/testing, gunakan fallback 'password' jika env belum disetel
                $password = !empty($password) ? $password : 'password';
            }

            $user = User::withTrashed()->firstOrNew(['email' => $acc['email']]);

            if ($user->trashed()) {
                $user->restore();
            }

            if (!$user->exists) {
                $user->name = $acc['name'];
                $user->nim_nip = $acc['nim_nip'];
                $user->email_verified_at = now();
            }

            // Kolom role sengaja non-fillable untuk mencegah mass-assignment
            $user->role = $acc['role'];
            // Model User memiliki cast 'password' => 'hashed', jadi string biasa otomatis di-hash
            $user->password = $password;

            $user->save();
        }
    }
}
