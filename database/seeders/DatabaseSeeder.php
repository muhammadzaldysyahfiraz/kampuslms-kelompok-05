<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeder.
     *
     * Refaktor Minggu 12 (Bebas fakerphp/faker):
     * Semua pemanggilan factory() dan fake() dihilangkan sepenuhnya agar
     * perintah 'php artisan db:seed --force' dapat berjalan di container
     * produksi (Docker/Railway) dengan 'composer install --no-dev'.
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

        $dosenList = collect();
        foreach ($dosenNames as $dosenData) {
            $d = new User();
            $d->name = $dosenData['name'];
            $d->email = $dosenData['email'];
            $d->nim_nip = $dosenData['nim_nip'];
            $d->role = 'dosen';
            $d->password = 'password';
            $d->email_verified_at = now();
            $d->save();

            $dosenList->push($d);
        }

        $dosenDemo = $dosenList->first();

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

        $mahasiswaList = collect();
        foreach ($mahasiswaNames as $mhsData) {
            $m = new User();
            $m->name = $mhsData['name'];
            $m->email = $mhsData['email'];
            $m->nim_nip = $mhsData['nim_nip'];
            $m->role = 'mahasiswa';
            $m->password = 'password';
            $m->email_verified_at = now();
            $m->save();

            $mahasiswaList->push($m);
        }

        $mahasiswaDemo = $mahasiswaList->first();

        /*
        |--------------------------------------------------------------------------
        | 4. 5 MATA KULIAH RIIL ITK (SEMUANYA STATUS 'active')
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
                'description' => 'Mengkaji arsitektur jaringan komputer dan komunikasi data berbasis model referensi OSI 7-Layer dan TCP/IP stack. Topik mencakup pengalamatan IPv4/IPv6, subnetting VLSM, routing dinamis (OSPF/BGP), switching VLAN, konfigurasi DNS/DHCP, analisis lalu lintas dengan Wireshark, serta dasar keamanan jaringan.',
                'sks' => 3,
                'lecturer_id' => $dosenList[1]->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2312012',
                'name' => 'Sistem Informasi Manajemen',
                'description' => 'Mempelajari pemanfaatan teknologi informasi dan sistem informasi untuk mendukung pengambilan keputusan strategis, taktis, dan operasional dalam organisasi bisnis modern.',
                'sks' => 2,
                'lecturer_id' => $dosenList[2]->id,
                'status' => 'active',
            ],
            [
                'code' => 'SI2413020',
                'name' => 'Analisis dan Perancangan Sistem Informasi',
                'description' => 'Mata kuliah terapan yang berfokus pada metodologi pengembangan perangkat lunak (SDLC, Waterfall, Agile Scrum). Mahasiswa mempelajari teknik elisitasi kebutuhan pengguna, pemodelan proses bisnis, perancangan antarmuka pengguna (UI/UX wireframing), dan penyusunan dokumen SRS.',
                'sks' => 3,
                'lecturer_id' => $dosenList[2]->id,
                'status' => 'active',
            ],
        ];

        $courses = collect();
        foreach ($coursesData as $cData) {
            $courses->push(Course::create($cData));
        }

        /*
        |--------------------------------------------------------------------------
        | 5. ENROLLMENT (MINIMAL 15 MAHASISWA PER MATA KULIAH)
        |--------------------------------------------------------------------------
        */

        foreach ($courses as $course) {
            $enrolled = collect([$mahasiswaDemo]);

            $otherStudents = $mahasiswaList
                ->where('id', '!=', $mahasiswaDemo->id)
                ->shuffle()
                ->take(17);

            $allEnrolled = $enrolled->merge($otherStudents);

            foreach ($allEnrolled as $student) {
                $course->students()->attach($student->id, [
                    'enrolled_at' => now()->subDays(random_int(20, 40)),
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 6. PENUGASAN (3 TUGAS PER MATA KULIAH = 15 TUGAS)
        |--------------------------------------------------------------------------
        */

        $assignmentsBlueprint = [
            0 => [
                [
                    'title' => 'Tugas 1: Implementasi CRUD & Route Binding',
                    'instructions' => 'Bangun sistem modul manajemen perkuliahan dengan operasi Create, Read, Update, dan Delete penuh menggunakan arsitektur Resource Controller Laravel 12. Pastikan seluruh rute menggunakan Route Model Binding implisit.',
                    'due_at' => now()->subDays(10),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Validasi Form Request & Mitigasi Mass-Assignment',
                    'instructions' => 'Rancang Form Request terpisah untuk validasi data masukan pembuatan dan pembaruan materi perkuliahan. Terapkan proteksi properti $fillable pada model Eloquent dan tangani pesan error multibahasa.',
                    'due_at' => now()->addDays(5),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 3: Arsitektur RESTful API & Endpoint Keamanan (Draft)',
                    'instructions' => 'Susun spesifikasi endpoint API publik dan privat untuk modul KampusLMS. Rancang struktur JSON Resource yang konsisten dan siapkan middleware autentikasi Sanctum token.',
                    'due_at' => now()->addDays(14),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ],
            ],
            1 => [
                [
                    'title' => 'Tugas 1: Pemodelan Data Relasional & Perancangan Skema ERD',
                    'instructions' => 'Rancang Entity-Relationship Diagram (ERD) lengkap untuk studi kasus sistem perpustakaan digital kampus. Identifikasi entitas, atribut primer, atribut komposit, dan relasi multi-kardinalitas.',
                    'due_at' => now()->subDays(12),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Implementasi DDL Migration & Relasi Foreign Key',
                    'instructions' => 'Terjemahkan rancangan ERD Tugas 1 ke dalam berkas migrasi basis data Laravel. Konfigurasikan seluruh constraint foreign key dengan aturan cascadeOnDelete() atau restrictOnDelete() yang tepat.',
                    'due_at' => now()->addDays(3),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 3: Optimasi Query SQL Kompleks & Indexing (Draft)',
                    'instructions' => 'Lakukan profiling performa query database menggunakan EXPLAIN ANALYZE. Identifikasi slow query pada operasi relasi join bersarang dan tambahkan index B-Tree yang sesuai.',
                    'due_at' => now()->addDays(18),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ],
            ],
            2 => [
                [
                    'title' => 'Tugas 1: Perhitungan Subnetting IPv4 & Skema Alokasi VLSM',
                    'instructions' => 'Diberikan blok alamat jaringan 192.168.10.0/24 untuk 4 departemen fakultas dengan kebutuhan host berbeda. Hitung pembagian subnet menggunakan metode Variable Length Subnet Masking (VLSM).',
                    'due_at' => now()->subDays(15),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Simulasi Topologi Jaringan & Routing Dinamis OSPF',
                    'instructions' => 'Bangun simulasi topologi jaringan multi-router menggunakan Cisco Packet Tracer. Konfigurasikan protokol routing dinamis OSPF Area 0 dan uji konvergensi rute saat salah satu jalur putus.',
                    'due_at' => now()->addDays(7),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 3: Analisis Paket Protokol HTTP/HTTPS dengan Wireshark (Draft)',
                    'instructions' => 'Lakukan penangkapan paket data transaksi HTTP GET/POST dan amati proses TLS Handshake pada koneksi HTTPS terenkripsi. Laporkan perbandingan payload plaintext vs cipher.',
                    'due_at' => now()->addDays(20),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ],
            ],
            3 => [
                [
                    'title' => 'Tugas 1: Analisis Rantai Nilai Porter pada Perusahaan Digital',
                    'instructions' => 'Pilih satu perusahaan teknologi terkemuka di Indonesia dan lakukan analisis Value Chain Model Porter. Identifikasi aktivitas utama dan pendukung yang didorong oleh integrasi sistem informasi.',
                    'due_at' => now()->subDays(8),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Evaluasi Kelayakan Investasi SI & Analisis Cost-Benefit',
                    'instructions' => 'Susun proposal analisis kelayakan finansial implementasi sistem CRM baru. Hitung metrik Payback Period (PP), Net Present Value (NPV), dan Return on Investment (ROI) selama horizon 5 tahun.',
                    'due_at' => now()->addDays(4),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 3: Perancangan Dashboard Eksekutif Berbasis KPI (Draft)',
                    'instructions' => 'Rancang wireframe dashboard Business Intelligence untuk jajaran dekanat. Tentukan 5 indikator kinerja utama (Key Performance Indicators) yang relevan untuk monitoring operasional perkuliahan.',
                    'due_at' => now()->addDays(15),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ],
            ],
            4 => [
                [
                    'title' => 'Tugas 1: Elisitasi Kebutuhan & Penyusunan Dokumen SRS',
                    'instructions' => 'Lakukan elisitasi kebutuhan sistem perpustakaan terintegrasi. Susun dokumen Software Requirements Specification (SRS) terstruktur mencakup kebutuhan fungsional dan non-fungsional.',
                    'due_at' => now()->subDays(6),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 2: Pemodelan Berorientasi Objek dengan Diagram UML',
                    'instructions' => 'Buat diagram Use Case lengkap dengan skenario naratif, Activity Diagram untuk alur bisnis peminjaman buku, dan Sequence Diagram yang menggambarkan interaksi antar-objek.',
                    'due_at' => now()->addDays(6),
                    'max_score' => 100,
                    'allow_late' => true,
                    'status' => 'published',
                ],
                [
                    'title' => 'Tugas 3: Perancangan Arsitektur Basis Data Fisik & UI Mockup (Draft)',
                    'instructions' => 'Kembangkan mockup antarmuka antarmuka pengguna berbasis Figma dengan prinsip usability Nielsen. Lampirkan rancangan kamus data fisik yang mendukung prototipe tersebut.',
                    'due_at' => now()->addDays(22),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ],
            ],
        ];

        $assignments = collect();
        // Simpan pemetaan assignment_id => lecturer_id di memori untuk mencegah N+1 lazy loading
        $assignmentLecturerMap = [];

        foreach ($courses as $courseIndex => $course) {
            foreach ($assignmentsBlueprint[$courseIndex] as $assignData) {
                $assignment = $course->assignments()->create([
                    'created_by' => $course->lecturer_id,
                    'title' => $assignData['title'],
                    'instructions' => $assignData['instructions'],
                    'due_at' => $assignData['due_at'],
                    'max_score' => $assignData['max_score'],
                    'allow_late' => $assignData['allow_late'],
                    'status' => $assignData['status'],
                ]);

                $assignments->push($assignment);
                $assignmentLecturerMap[$assignment->id] = $course->lecturer_id;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 7. SUBMISSION (TOTAL TEPAT 125 BERKAS PENGUMPULAN)
        |--------------------------------------------------------------------------
        */

        $submissions = collect();

        // SUBMISSION 1: Wajib milik $mahasiswaDemo pada MK 1 Tugas 1
        $submDemo = new Submission();
        $submDemo->assignment_id = $assignments[0]->id;
        $submDemo->user_id = $mahasiswaDemo->id;
        $submDemo->file_path = "submissions/demo-mahasiswa-tugas-1-" . Str::uuid() . ".pdf";
        $submDemo->original_name = "Laporan_Tugas1_Web_10241000.pdf";
        $submDemo->file_size = 1048576;
        $submDemo->submitted_at = now()->subDays(11);
        $submDemo->is_late = false;
        $submDemo->note = 'Tugas 1 Pemrograman Web telah selesai diimplementasikan sesuai seluruh kriteria.';
        $submDemo->save();

        $submissions->push($submDemo);

        // MK 1 menambah 24 submission lagi (total MK 1 = 25)
        // MK 2, 3, 4, 5 masing-masing menambah 25 submission
        // Total keseluruhan = 1 + 24 + 25 + 25 + 25 + 25 = 125 submission
        $sampleNotes = [
            'Berikut laporan tugas telah diselesaikan dan diuji pada lingkungan lokal.',
            'Pengumpulan berkas penugasan tepat waktu, seluruh requirement terpenuhi.',
            'Tugas telah direview dan seluruh unit testing lulus tanpa error.',
            'Mohon feedback dan masukan untuk pengembangan tugas berikutnya, Pak.',
            'Kode sumber dan dokumentasi telah diunggah sesuai format.',
        ];

        foreach ($courses as $courseIndex => $course) {
            $courseAssignments = $assignments->where('course_id', $course->id)->values();
            $targetCount = ($courseIndex === 0) ? 24 : 25;

            // Load mahasiswa terdaftar
            $enrolledStudents = $course->students()->get();

            $createdForCourse = 0;
            while ($createdForCourse < $targetCount) {
                // Pilih tugas acak
                $assignment = $courseAssignments[array_rand($courseAssignments->all())];

                // PENTING UNTUK UJI IDOR (submissions/79):
                // Pada MK 3, 4, dan 5 ($courseIndex >= 2), kecualikan $mahasiswaDemo
                // agar Submission ID 79 dipastikan bukan milik $mahasiswaDemo!
                if ($courseIndex >= 2) {
                    $candidateStudents = $enrolledStudents->where('id', '!=', $mahasiswaDemo->id)->values();
                } else {
                    $candidateStudents = $enrolledStudents;
                }

                $student = $candidateStudents[array_rand($candidateStudents->all())];

                // Cegah duplikasi unique(['assignment_id', 'user_id'])
                $exists = $submissions->contains(function ($s) use ($assignment, $student) {
                    return $s->assignment_id === $assignment->id && $s->user_id === $student->id;
                });

                if ($exists) {
                    continue;
                }

                $isLate = (random_int(1, 100) <= 20); // 20% kemungkinan terlambat
                $submittedAt = $isLate
                    ? (clone $assignment->due_at)->addHours(random_int(2, 48))
                    : (clone $assignment->due_at)->subHours(random_int(2, 72));

                $subm = new Submission();
                $subm->assignment_id = $assignment->id;
                $subm->user_id = $student->id;
                $subm->file_path = "submissions/" . Str::uuid() . ".pdf";
                $subm->original_name = "Tugas_{$assignment->id}_{$student->nim_nip}.pdf";
                $subm->file_size = random_int(450000, 2500000);
                $subm->submitted_at = $submittedAt;
                $subm->is_late = $isLate;
                $subm->note = $sampleNotes[array_rand($sampleNotes)];
                $subm->save();

                $submissions->push($subm);
                $createdForCourse++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 8. PENILAIAN (GRADE) ±60% SUBMISSION
        |--------------------------------------------------------------------------
        */

        $sampleFeedbacks = [
            'Struktur kode sangat rapi, pemisahan concern arsitektur terjaga dengan baik.',
            'Implementasi sudah memenuhi spesifikasi, pertahankan konsistensi dokumentasi.',
            'Hasil pengujian memuaskan, seluruh validasi masukan ditangani dengan benar.',
            'Pekerjaan luar biasa! Solusi yang diterapkan sangat elegan dan efisien.',
            'Secara umum bagus, perhatikan kembali optimasi query pada relasi bersarang.',
        ];

        $numberOfGrades = (int) floor($submissions->count() * 0.60); // 75 grade
        $submissionsToGrade = $submissions->shuffle()->take($numberOfGrades);

        // Pastikan Submission 1 milik demo pasti mendapat nilai
        if (!$submissionsToGrade->contains('id', $submDemo->id)) {
            $submissionsToGrade->push($submDemo);
        }

        foreach ($submissionsToGrade as $submission) {
            $graderId = $assignmentLecturerMap[$submission->assignment_id] ?? $dosenDemo->id;

            $grade = new Grade();
            $grade->submission_id = $submission->id;
            $grade->graded_by = $graderId;
            $grade->score = random_int(80, 100);
            $grade->feedback = $sampleFeedbacks[array_rand($sampleFeedbacks)];
            $grade->graded_at = now()->subDays(random_int(1, 5));
            $grade->save();
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
                $material = new Material();
                $material->course_id = $course->id;
                $material->uploaded_by = $course->lecturer_id;
                $material->title = $mData['title'];
                $material->description = $mData['description'];
                $material->type = 'file';
                $material->file_path = "materials/pertemuan-" . ($mIndex + 1) . "-{$course->code}.pdf";
                $material->original_name = "pertemuan-" . ($mIndex + 1) . "-{$course->code}.pdf";
                $material->file_size = random_int(350000, 1850000);
                $material->mime_type = 'application/pdf';
                $material->external_url = null;
                $material->save();
            }
        }
    }
}
