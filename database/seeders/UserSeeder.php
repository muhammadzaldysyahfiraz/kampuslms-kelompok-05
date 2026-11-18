<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder pengguna (Admin, Dosen, Mahasiswa).
     *
     * Refaktor M13 (Modularisasi & Bebas Faker):
     * Memisahkan inisialisasi akun dari DatabaseSeeder utama agar sesuai prinsip
     * Single Responsibility. Data statis menjaga kompatibilitas dengan test-authz.sh
     * dan lingkungan produksi (--no-dev).
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. ADMIN (1 PENGGUNA)
        |--------------------------------------------------------------------------
        */
        $admin = new User();
        $admin->name = 'Admin KampusLMS';
        $admin->email = 'admin@kampuslms.test';
        $admin->nim_nip = 'ADM001';
        $admin->role = 'admin';
        $admin->password = 'password';
        $admin->email_verified_at = now();
        $admin->save();

        /*
        |--------------------------------------------------------------------------
        | 2. DOSEN (3 PENGGUNA)
        |--------------------------------------------------------------------------
        */
        $dosenNames = [
            ['name' => 'Dosen Demo', 'email' => 'dosen@kampuslms.test', 'nim_nip' => 'DOS001'],
            ['name' => 'Dr. Eng. Aidil Saputra Kirsan, S.Kom., M.Kom.', 'email' => 'aidil.kirsan@itk.ac.id', 'nim_nip' => '198904152019031012'],
            ['name' => 'Achmad Zaki Zaidan, S.Kom., M.Cs.', 'email' => 'zaki.zaidan@itk.ac.id', 'nim_nip' => '199208202020121008'],
        ];

        foreach ($dosenNames as $dosenData) {
            $d = new User();
            $d->name = $dosenData['name'];
            $d->email = $dosenData['email'];
            $d->nim_nip = $dosenData['nim_nip'];
            $d->role = 'dosen';
            $d->password = 'password';
            $d->email_verified_at = now();
            $d->save();
        }

        /*
        |--------------------------------------------------------------------------
        | 3. MAHASISWA (30 PENGGUNA)
        |--------------------------------------------------------------------------
        */
        $mahasiswaNames = [
            ['name' => 'Mahasiswa Demo', 'email' => 'mahasiswa@kampuslms.test', 'nim_nip' => 'MHS001'],
            ['name' => 'Muhammad Farin Murtadho Syafiq', 'email' => '10241046@student.itk.ac.id', 'nim_nip' => '10241046'],
            ['name' => 'Muhammad Rifa Al Rizqul Aulia', 'email' => '10241050@student.itk.ac.id', 'nim_nip' => '10241050'],
            ['name' => 'Muhammad Yuspa Ardiansyah', 'email' => '10241052@student.itk.ac.id', 'nim_nip' => '10241052'],
            ['name' => 'Muhammad Zaldy Syah Firaz', 'email' => '10241054@student.itk.ac.id', 'nim_nip' => '10241054'],
            ['name' => 'Nova Reskianti', 'email' => '10241058@student.itk.ac.id', 'nim_nip' => '10241058'],
            ['name' => 'Ahmad Fadhil Ramadhan', 'email' => '10241001@student.itk.ac.id', 'nim_nip' => '10241001'],
            ['name' => 'Anisa Rahmawati', 'email' => '10241002@student.itk.ac.id', 'nim_nip' => '10241002'],
            ['name' => 'Bagus Dwi Saputra', 'email' => '10241003@student.itk.ac.id', 'nim_nip' => '10241003'],
            ['name' => 'Cahya Putri Lestari', 'email' => '10241004@student.itk.ac.id', 'nim_nip' => '10241004'],
            ['name' => 'Dimas Arya Pratama', 'email' => '10241005@student.itk.ac.id', 'nim_nip' => '10241005'],
            ['name' => 'Eka Wahyu Nugroho', 'email' => '10241006@student.itk.ac.id', 'nim_nip' => '10241006'],
            ['name' => 'Fajar Maulana Malik', 'email' => '10241007@student.itk.ac.id', 'nim_nip' => '10241007'],
            ['name' => 'Gita Nur Anggraini', 'email' => '10241008@student.itk.ac.id', 'nim_nip' => '10241008'],
            ['name' => 'Hendra Tri Kusuma', 'email' => '10241009@student.itk.ac.id', 'nim_nip' => '10241009'],
            ['name' => 'Indah Permata Sari', 'email' => '10241010@student.itk.ac.id', 'nim_nip' => '10241010'],
            ['name' => 'Joko Santoso Wibowo', 'email' => '10241011@student.itk.ac.id', 'nim_nip' => '10241011'],
            ['name' => 'Kartika Wulandari', 'email' => '10241012@student.itk.ac.id', 'nim_nip' => '10241012'],
            ['name' => 'Lukman Hakim Siregar', 'email' => '10241013@student.itk.ac.id', 'nim_nip' => '10241013'],
            ['name' => 'Megawati Dewi Utami', 'email' => '10241014@student.itk.ac.id', 'nim_nip' => '10241014'],
            ['name' => 'Naufal Izza Rabbani', 'email' => '10241015@student.itk.ac.id', 'nim_nip' => '10241015'],
            ['name' => 'Octavia Bella Safitri', 'email' => '10241016@student.itk.ac.id', 'nim_nip' => '10241016'],
            ['name' => 'Panji Asmoro Bangun', 'email' => '10241017@student.itk.ac.id', 'nim_nip' => '10241017'],
            ['name' => 'Qoriatul Ainiyah', 'email' => '10241018@student.itk.ac.id', 'nim_nip' => '10241018'],
            ['name' => 'Rangga Setiawan', 'email' => '10241019@student.itk.ac.id', 'nim_nip' => '10241019'],
            ['name' => 'Siti Khodijah Azzahra', 'email' => '10241020@student.itk.ac.id', 'nim_nip' => '10241020'],
            ['name' => 'Taufiq Hidayatullah', 'email' => '10241021@student.itk.ac.id', 'nim_nip' => '10241021'],
            ['name' => 'Umar Said Al-Habsyi', 'email' => '10241022@student.itk.ac.id', 'nim_nip' => '10241022'],
            ['name' => 'Vina Yuliana Putri', 'email' => '10241023@student.itk.ac.id', 'nim_nip' => '10241023'],
            ['name' => 'Wahyu Hidayat', 'email' => '10241024@student.itk.ac.id', 'nim_nip' => '10241024'],
        ];

        foreach ($mahasiswaNames as $mhsData) {
            $m = new User();
            $m->name = $mhsData['name'];
            $m->email = $mhsData['email'];
            $m->nim_nip = $mhsData['nim_nip'];
            $m->role = 'mahasiswa';
            $m->password = 'password';
            $m->email_verified_at = now();
            $m->save();
        }
    }
}
