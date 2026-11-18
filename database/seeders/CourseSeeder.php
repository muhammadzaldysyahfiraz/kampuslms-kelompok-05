<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Jalankan seeder mata kuliah dan enrollment.
     *
     * Refaktor M13 (Optimasi Performa Bulk Attach):
     * Memangkas ~90 query INSERT terpisah menjadi hanya 5 query bulk insert
     * dengan memanfaatkan pemetaan array pivot pada method attach($pivotData).
     */
    public function run(): void
    {
        $dosenDemo = User::where('email', 'dosen@kampuslms.test')->firstOrFail();
        $dosenAidil = User::where('email', 'aidil.kirsan@itk.ac.id')->firstOrFail();
        $dosenZaki = User::where('email', 'zaki.zaidan@itk.ac.id')->firstOrFail();

        $mahasiswaList = User::where('role', 'mahasiswa')->get();
        $mahasiswaDemo = $mahasiswaList->firstWhere('email', 'mahasiswa@kampuslms.test');

        $coursesData = [
            [
                'code' => 'SI2514024',
                'name' => 'Pemrograman Web',
                'description' => 'Mata kuliah ini membahas perancangan dan pembangunan aplikasi web skala enterprise menggunakan teknologi modern. Materi mencakup protokol HTTP, arsitektur MVC pada Laravel 12, integrasi database relasional, autentikasi sesi, otorisasi berbasis Policy/RBAC, mitigasi kerentanan keamanan web (OWASP Top 10), serta implementasi RESTful API terstandarisasi.',
                'sks' => 3,
                'lecturer_id' => $dosenDemo->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2112015',
                'name' => 'Basis Data',
                'description' => 'Membahas fundamental perancangan sistem basis data relasional, pemodelan Entity-Relationship Diagram (ERD), normalisasi data dari 1NF hingga BCNF, sintaks SQL tingkat lanjut (DDL, DML, DCL), transaksi ACID, indexing, serta optimasi query pada database MySQL dan PostgreSQL.',
                'sks' => 3,
                'lecturer_id' => $dosenDemo->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2213018',
                'name' => 'Jaringan Komputer',
                'description' => 'Mengkaji arsitektur jaringan komputer dan komunikasi data berbasis model referensi OSI 7-Layer dan TCP/IP stack. Topik mencakup pengalamatan IPv4/IPv6, subnetting VLSM, routing dinamis (OSPF/BGP), switching VLAN, konfigurasi DNS/DHCP, analisis lalu lintas dengan Wireshark, serta dasar keamanan jaringan.',
                'sks' => 3,
                'lecturer_id' => $dosenAidil->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2312012',
                'name' => 'Sistem Informasi Manajemen',
                'description' => 'Mempelajari pemanfaatan teknologi informasi dan sistem informasi untuk mendukung pengambilan keputusan strategis, taktis, dan operasional dalam organisasi bisnis modern.',
                'sks' => 2,
                'lecturer_id' => $dosenZaki->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2413020',
                'name' => 'Analisis dan Perancangan Sistem Informasi',
                'description' => 'Mata kuliah terapan yang berfokus pada metodologi pengembangan perangkat lunak (SDLC, Waterfall, Agile Scrum). Mahasiswa mempelajari teknik elisitasi kebutuhan pengguna, pemodelan proses bisnis, perancangan antarmuka pengguna (UI/UX wireframing), dan penyusunan dokumen SRS.',
                'sks' => 3,
                'lecturer_id' => $dosenZaki->id,
                'status' => 'active',
            ],
        ];

        foreach ($coursesData as $cData) {
            $course = Course::create($cData);

            // Enrollment: Minimal 15 mahasiswa per course (termasuk mahasiswaDemo)
            $enrolled = collect([$mahasiswaDemo]);
            $otherStudents = $mahasiswaList
                ->where('id', '!=', $mahasiswaDemo->id)
                ->shuffle()
                ->take(17);

            $allEnrolled = $enrolled->merge($otherStudents);

            // Optimasi M13: Bulk Attach (1 query per mata kuliah, bukan 18 query terpisah)
            $pivotData = [];
            foreach ($allEnrolled as $student) {
                $pivotData[$student->id] = [
                    'enrolled_at' => now()->subDays(random_int(20, 40)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $course->students()->attach($pivotData);
        }
    }
}
