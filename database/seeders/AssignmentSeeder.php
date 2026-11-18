<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    /**
     * Jalankan seeder penugasan dan materi perkuliahan.
     *
     * Refaktor M13: Memisahkan penugasan dan materi perkuliahan dari class utama
     * agar modular dan menjaga Single Responsibility Principle.
     */
    public function run(): void
    {
        $courses = Course::orderBy('id')->get();

        /*
        |--------------------------------------------------------------------------
        | 1. PENUGASAN (3 TUGAS PER MATA KULIAH = 15 TUGAS)
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
                    'instructions' => 'Kembangkan mockup antarmuka pengguna berbasis Figma dengan prinsip usability Nielsen. Lampirkan rancangan kamus data fisik yang mendukung prototipe tersebut.',
                    'due_at' => now()->addDays(22),
                    'max_score' => 100,
                    'allow_late' => false,
                    'status' => 'draft',
                ],
            ],
        ];

        foreach ($courses as $courseIndex => $course) {
            foreach ($assignmentsBlueprint[$courseIndex] as $assignData) {
                $course->assignments()->create([
                    'created_by' => $course->lecturer_id,
                    'title' => $assignData['title'],
                    'instructions' => $assignData['instructions'],
                    'due_at' => $assignData['due_at'],
                    'max_score' => $assignData['max_score'],
                    'allow_late' => $assignData['allow_late'],
                    'status' => $assignData['status'],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. MATERI KULIAH RIIL (2-3 MATERI PER MATA KULIAH)
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
