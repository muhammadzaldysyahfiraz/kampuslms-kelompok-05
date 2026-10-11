<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = User::factory()->create([
            'name' => 'Admin KampusLMS',
            'email' => 'admin@kampuslms.test',
            'password' => 'password',
            'role' => 'admin',
            'nim_nip' => 'ADM001',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2. DOSEN
        |--------------------------------------------------------------------------
        */

        $dosen = User::factory()
            ->count(3)
            ->create([
                'role' => 'dosen',
            ]);

        /*
        |--------------------------------------------------------------------------
        | AKUN DOSEN DEMO
        |--------------------------------------------------------------------------
        */

        $dosenDemo = $dosen->first();

        $dosenDemo->update([
            'name' => 'Dosen Demo',
            'email' => 'dosen@kampuslms.test',
            'nim_nip' => 'DOS001',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3. MAHASISWA
        |--------------------------------------------------------------------------
        */

        $mahasiswa = User::factory()
            ->count(30)
            ->create([
                'role' => 'mahasiswa',
            ]);

        /*
        |--------------------------------------------------------------------------
        | AKUN MAHASISWA DEMO
        |--------------------------------------------------------------------------
        */

        $mahasiswaDemo = $mahasiswa->first();

        $mahasiswaDemo->update([
            'name' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@kampuslms.test',
            'nim_nip' => 'MHS001',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 4. 5 MATA KULIAH RIIL ITK
        |--------------------------------------------------------------------------
        */

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
                'description' => 'Mempelajari arsitektur dan protokol komunikasi data komputer, model referensi OSI 7 Layer, protokol suite TCP/IP, pengalamatan IPv4 dan IPv6, teknik subnetting VLSM/CIDR, routing statis dan dinamis (OSPF), serta pengenalan keamanan jaringan dan packet inspection menggunakan Wireshark.',
                'sks' => 3,
                'lecturer_id' => $dosen[1]->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2312012',
                'name' => 'Sistem Informasi Manajemen',
                'description' => 'Mengkaji peran strategis sistem informasi dan teknologi digital dalam mentransformasi operasional bisnis, keunggulan kompetitif, dan pengambilan keputusan manajerial. Cakupan materi meliputi sistem enterprise (ERP, CRM, SCM), manajemen pengetahuan, Business Intelligence, dan tata kelola TI.',
                'sks' => 2,
                'lecturer_id' => $dosen[2]->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2413020',
                'name' => 'Analisis dan Perancangan Sistem',
                'description' => 'Mempelajari metodologi dan siklus hidup pengembangan sistem informasi (SDLC), elisitasi dan rekayasa kebutuhan perangkat lunak, pemodelan berorientasi objek dengan Unified Modeling Language (UML), perancangan arsitektur sistem, serta desain pengalaman pengguna (UI/UX).',
                'sks' => 4,
                'lecturer_id' => $dosen[2]->id,
                'status' => 'active',
            ],
        ];

        $courses = collect();
        foreach ($coursesData as $cData) {
            $courses->push(Course::create($cData));
        }

        /*
        |--------------------------------------------------------------------------
        | 5. SETIAP MK MINIMAL 15 MAHASISWA (Mahasiswa Demo terdaftar di MK 1 & 2)
        |--------------------------------------------------------------------------
        */

        foreach ($courses as $index => $course) {
            $students = $mahasiswa->random(15);

            foreach ($students as $student) {
                $course->students()->syncWithoutDetaching([
                    $student->id => ['enrolled_at' => now()],
                ]);
            }

            // Pastikan Mahasiswa Demo selalu terdaftar di Course 1 & 2
            if ($index === 0 || $index === 1) {
                $course->students()->syncWithoutDetaching([
                    $mahasiswaDemo->id => ['enrolled_at' => now()],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 6. TUGAS RIIL PER MATA KULIAH (3 TUGAS PER MK = 15 TUGAS)
        |--------------------------------------------------------------------------
        */

        $assignmentsBlueprint = [
            // MK 1: Pemrograman Web
            0 => [
                [
                    'title' => 'Tugas 1: Perancangan Skema Database & Migrasi Relasi',
                    'instructions' => 'Rancang dan implementasikan skema basis data relasional untuk sistem manajemen pembelajaran kampus. Buat migration file lengkap dengan foreign key constraints, composite unique index pada tabel pivot, serta soft deletes. Kumpulkan berkas migrasi dan dokumentasi ERD dalam format PDF.',
                    'due_at' => now()->subDays(7),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Implementasi Otorisasi Policy & Matriks Keamanan',
                    'instructions' => 'Implementasikan 5 Laravel Policy (Course, Assignment, Material, Submission, Grade) untuk menegakkan kontrol akses tiga peran (Admin, Dosen, Mahasiswa). Pastikan seluruh titik rawan IDOR tertutup rapat dengan respon HTTP 403 atau 404, serta lengkapi dengan pengujian fitur (Feature Test).',
                    'due_at' => now()->addDays(7),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas Akhir: Proyek LMS Terpadu & Endpoint RESTful API',
                    'instructions' => 'Kembangkan modul RESTful API versi 1 untuk KampusLMS yang mencakup autentikasi token Sanctum, CRUD penugasan, pengumpulan berkas, dan penilaian submission. Dokumentasikan seluruh endpoint dan sertakan skrip verifikasi otomatis.',
                    'due_at' => now()->addDays(30),
                    'status' => 'draft',
                ],
            ],
            // MK 2: Basis Data
            1 => [
                [
                    'title' => 'Tugas 1: Normalisasi Skema & Perancangan ERD Rumah Sakit',
                    'instructions' => 'Lakukan analisis terhadap data transaksi registrasi dan rekam medis pasien rumah sakit. Bentuk skema database yang memenuhi bentuk normal ketiga (3NF) dan sertakan diagram relasi antar-entitas.',
                    'due_at' => now()->subDays(5),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Penyusunan Kueri Kompleks & Agregasi SQL',
                    'instructions' => 'Tuliskan kueri SQL untuk menghasilkan laporan statistik performa akademik mahasiswa dengan memanfaatkan multi-table JOIN, GROUP BY, HAVING, dan subquery analitis.',
                    'due_at' => now()->addDays(6),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas Akhir: Desain Basis Data Skala Besar & Optimasi Indeks',
                    'instructions' => 'Rancang arsitektur basis data untuk sistem e-commerce berskala menengah dengan indexing B-Tree, partition table, dan analisis execution plan menggunakan EXPLAIN ANALYZE.',
                    'due_at' => now()->addDays(25),
                    'status' => 'draft',
                ],
            ],
            // MK 3: Jaringan Komputer
            2 => [
                [
                    'title' => 'Tugas 1: Perhitungan Subnetting VLSM Kampus Terpadu',
                    'instructions' => 'Diberikan alokasi blok IP 192.168.10.0/23. Rancang pembagian subnet untuk 5 laboratorium komputer dan 3 ruang dosen dengan efisiensi alamat IP maksimal.',
                    'due_at' => now()->subDays(8),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Simulasi Routing OSPF pada Cisco Packet Tracer',
                    'instructions' => 'Bangun topologi jaringan multi-router di Cisco Packet Tracer. Konfigurasikan routing dinamis OSPF Area 0 dan pastikan seluruh host dapat saling berkomunikasi via ping.',
                    'due_at' => now()->addDays(8),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas Akhir: Analisis Protokol & Forensik Paket Wireshark',
                    'instructions' => 'Lakukan packet capture saat proses handshaking TCP, resolusi DNS, dan pertukaran request HTTP. Jelaskan struktur header paket dan temuan analisis dalam laporan komparasi.',
                    'due_at' => now()->addDays(28),
                    'status' => 'draft',
                ],
            ],
            // MK 4: Sistem Informasi Manajemen
            3 => [
                [
                    'title' => 'Tugas 1: Studi Kasus Transformasi Digital Perusahaan Ritel',
                    'instructions' => 'Analisis studi kasus pergeseran model bisnis ritel konvensional menuju omnichannel. Identifikasi arsitektur TI dan tantangan manajemen perubahan yang dihadapi.',
                    'due_at' => now()->subDays(6),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Evaluasi Kebutuhan Sistem Enterprise CRM',
                    'instructions' => 'Susun rencana adopsi sistem Customer Relationship Management untuk institusi perbankan regional, lengkap dengan analisis cost-benefit dan indikator keberhasilan (KPI).',
                    'due_at' => now()->addDays(5),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas Akhir: Perancangan Roadmap Tata Kelola TI Organisasi',
                    'instructions' => 'Buat dokumen roadmap implementasi TI 5 tahun ke depan untuk sebuah startup logistik, mencakup analisis risiko, alokasi anggaran, dan kepatuhan standar keamanan data.',
                    'due_at' => now()->addDays(35),
                    'status' => 'draft',
                ],
            ],
            // MK 5: Analisis dan Perancangan Sistem
            4 => [
                [
                    'title' => 'Tugas 1: Dokumen Spesifikasi Kebutuhan Perangkat Lunak (SKPL)',
                    'instructions' => 'Susun dokumen SKPL/SRS berstandar IEEE 830 untuk aplikasi pemesanan tiket transportasi online. Sertakan functional requirement dan non-functional requirement yang terukur.',
                    'due_at' => now()->subDays(4),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Pemodelan Diagram UML Lengkap & Kamus Data',
                    'instructions' => 'Buat diagram UML komprehensif (Use Case, Activity, dan Sequence Diagram) untuk skenario transaksi checkout dan pembayaran sistem e-commerce.',
                    'due_at' => now()->addDays(9),
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas Akhir: Perancangan Prototipe UI/UX & User Testing',
                    'instructions' => 'Rancang wireframe dan high-fidelity interactive prototype sistem berbasis prinsip Human-Centered Design. Lakukan usability testing kepada minimal 5 calon pengguna.',
                    'due_at' => now()->addDays(32),
                    'status' => 'draft',
                ],
            ],
        ];

        $allAssignments = collect();
        foreach ($courses as $index => $course) {
            foreach ($assignmentsBlueprint[$index] as $aData) {
                $assignment = Assignment::create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                    'title' => $aData['title'],
                    'instructions' => $aData['instructions'],
                    'due_at' => $aData['due_at'],
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => $aData['status'],
                ]);
                $allAssignments->push($assignment);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. SUBMISSION (Mahasiswa Demo memiliki Submission 1 pada Tugas 1)
        |--------------------------------------------------------------------------
        */

        $submissions = collect();

        // 1. Buat Submission 1 khusus Mahasiswa Demo pada Tugas 1 (Course 1)
        $firstAssignment = $allAssignments->first();
        $submDemo = Submission::create([
            'assignment_id' => $firstAssignment->id,
            'user_id' => $mahasiswaDemo->id,
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'original_name' => 'laporan-tugas-1-pemrograman-web.pdf',
            'file_size' => 1250000,
            'note' => 'Tugas 1 telah diselesaikan secara lengkap sesuai dengan instruksi modul. Seluruh kode program dan laporan pengujian terlampir.',
            'submitted_at' => $firstAssignment->due_at->copy()->subHours(4),
            'is_late' => false,
        ]);
        $submissions->push($submDemo);

        // 2. Buat sisa submission hingga 25 submission per mata kuliah
        foreach ($courses as $course) {
            $students = $course->students()->get();
            $courseAssignments = $course->assignments()->get();

            $target = ($course->id === 1) ? 24 : 25;
            $created = 0;

            while ($created < $target) {
                $student = $students->random();
                $assignment = $courseAssignments->random();

                $exists = Submission::where('assignment_id', $assignment->id)
                    ->where('user_id', $student->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $isLate = fake()->boolean(20);
                $submittedAt = $isLate
                    ? $assignment->due_at->copy()->addDays(1)
                    : $assignment->due_at->copy()->subDays(1);

                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                    'submitted_at' => $submittedAt,
                    'is_late' => $isLate,
                ]);

                $submissions->push($submission);
                $created++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 8. PENILAIAN (GRADE) ±60% SUBMISSION
        |--------------------------------------------------------------------------
        */

        $numberOfGrades = (int) floor($submissions->count() * 0.60);
        $submissionsToGrade = $submissions->random($numberOfGrades);

        // Pastikan Submission 1 milik demo juga mendapat nilai
        if (!$submissionsToGrade->contains('id', $submDemo->id)) {
            $submissionsToGrade->push($submDemo);
        }

        foreach ($submissionsToGrade as $submission) {
            Grade::factory()->create([
                'submission_id' => $submission->id,
                'graded_by' => $submission->assignment->course->lecturer_id,
                'score' => fake()->numberBetween(80, 100),
                'graded_at' => now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 9. MATERI KULIAH RIIL (2-3 MATERI PER MATA KULIAH)
        |--------------------------------------------------------------------------
        */

        $materialsBlueprint = [
            0 => [
                [
                    'title' => 'Pertemuan 1: Arsitektur Modern Web & Ekosistem Laravel 12',
                    'description' => 'Pengenalan arsitektur client-server, siklus request-response HTTP, struktur direktori Laravel 12, dan konfigurasi lingkungan pengembang.',
                ],
                [
                    'title' => 'Pertemuan 2: Routing, Controller, dan Blade Templating Engine',
                    'description' => 'Pembahasan rute web, route model binding, perancangan layout induk Blade, komponen UI reusable, dan penanganan asset bundler Vite.',
                ],
                [
                    'title' => 'Pertemuan 3: Keamanan Otorisasi, Policy, dan Mitigasi IDOR',
                    'description' => 'Prinsip defense-in-depth, implementasi CoursePolicy & SubmissionPolicy, scoped route binding, dan pencegahan Insecure Direct Object References.',
                ],
            ],
            1 => [
                [
                    'title' => 'Pertemuan 1: Pemodelan Data Konseptual & Perancangan ERD',
                    'description' => 'Konsep entitas, relasi, kardinalitas, pemodelan data relasional, dan transformasi ERD ke skema tabel fisik.',
                ],
                [
                    'title' => 'Pertemuan 2: Normalisasi Basis Data & Integritas Relasi',
                    'description' => 'Tahapan normalisasi data 1NF, 2NF, 3NF, dan BCNF untuk mencegah anomali insert, update, dan delete.',
                ],
            ],
            2 => [
                [
                    'title' => 'Pertemuan 1: Arsitektur Model OSI 7 Layer & TCP/IP',
                    'description' => 'Fungsi dan tanggung jawab setiap layer pada komunikasi data, enkapsulasi paket, dan perbandingan OSI vs TCP/IP.',
                ],
                [
                    'title' => 'Pertemuan 2: Pengalamatan Logis IPv4 & Subnetting VLSM',
                    'description' => 'Teknik pembagian blok IP address secara efisien menggunakan Variable Length Subnet Masking (VLSM).',
                ],
            ],
            3 => [
                [
                    'title' => 'Pertemuan 1: Peran Strategis TI dalam Organisasi Modern',
                    'description' => 'Bagaimana sistem informasi menciptakan diferensiasi produk, efisiensi operasional rantai pasok, dan keunggulan kompetitif.',
                ],
                [
                    'title' => 'Pertemuan 2: Arsitektur Enterprise Resource Planning (ERP)',
                    'description' => 'Integrasi modul keuangan, sumber daya manusia, logistik, dan operasional dalam satu sistem terpusat.',
                ],
            ],
            4 => [
                [
                    'title' => 'Pertemuan 1: Siklus Hidup Pengembangan Sistem & Metodologi Agile',
                    'description' => 'Perbandingan model Waterfall, Iteratif, dan Scrum Framework, peran Product Owner, Scrum Master, dan Developer.',
                ],
                [
                    'title' => 'Pertemuan 2: Pemodelan Berorientasi Objek dengan UML',
                    'description' => 'Pembuatan Use Case Diagram, Activity Diagram, Sequence Diagram, dan Class Diagram yang konsisten.',
                ],
            ],
        ];

        foreach ($courses as $index => $course) {
            foreach ($materialsBlueprint[$index] as $mIndex => $mData) {
                $course->materials()->create([
                    'uploaded_by' => $course->lecturer_id,
                    'title' => $mData['title'],
                    'description' => $mData['description'],
                    'type' => 'file',
                    'file_path' => "materials/pertemuan-" . ($mIndex + 1) . "-{$course->code}.pdf",
                    'original_name' => "pertemuan-" . ($mIndex + 1) . "-{$course->code}.pdf",
                    'file_size' => fake()->numberBetween(350000, 1850000),
                    'mime_type' => 'application/pdf',
                    'external_url' => null,
                ]);
            }
        }
    }
}