<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $courses = [
            [
                'name' => 'Pemrograman Web',
                'description' => 'Mempelajari konsep dan praktik pengembangan aplikasi web modern berbasis arsitektur client-server, protokol HTTP, framework Laravel 12, otorisasi RBAC, pengujian otomatis, dan integrasi RESTful API.',
            ],
            [
                'name' => 'Basis Data',
                'description' => 'Membahas prinsip sistem basis data relasional, pemodelan Entity-Relationship Diagram (ERD), normalisasi 1NF hingga BCNF, sintaks SQL tingkat lanjut, transaksi ACID, dan optimasi query.',
            ],
            [
                'name' => 'Jaringan Komputer',
                'description' => 'Mempelajari arsitektur jaringan komputer, model OSI 7 Layer, protokol TCP/IP, pengalamatan IPv4/IPv6, subnetting VLSM, routing dinamis OSPF, serta analisis paket data dengan Wireshark.',
            ],
            [
                'name' => 'Sistem Informasi Manajemen',
                'description' => 'Mengkaji peran strategis sistem informasi dalam proses bisnis, sistem enterprise (ERP, SCM, CRM), Business Intelligence, serta transformasi digital tata kelola organisasi modern.',
            ],
            [
                'name' => 'Analisis dan Perancangan Sistem',
                'description' => 'Mempelajari siklus hidup pengembangan sistem informasi (SDLC), metodologi Agile Scrum, rekayasa kebutuhan perangkat lunak, serta pemodelan berorientasi objek dengan diagram UML.',
            ],
        ];

        $selected = fake()->randomElement($courses);

        return [
            'code' => 'SI' . fake()->unique()->numerify('#######'),
            'name' => $selected['name'],
            'description' => $selected['description'],
            'sks' => fake()->numberBetween(2, 4),
            'lecturer_id' => User::where('role', 'dosen')
                ->inRandomOrder()
                ->value('id'),
            'status' => 'active',
        ];
    }
}