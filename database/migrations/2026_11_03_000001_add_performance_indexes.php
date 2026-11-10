<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * Menambahkan indeks B-Tree pada beberapa kolom yang sering digunakan
     * dalam proses pencarian dan penyaringan data di aplikasi KampusLMS.
     * Indeks ini membantu database menemukan data dengan lebih cepat
     * tanpa harus memeriksa seluruh isi tabel.
     */
    public function up(): void
    {
        // 1. Mempercepat pencarian pengguna berdasarkan peran
        // Digunakan pada query untuk mengambil data mahasiswa maupun dosen.
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
        });

        // 2. Mempercepat penyaringan mata kuliah berdasarkan status
        // Membantu proses pengambilan daftar mata kuliah yang berstatus aktif.
        Schema::table('courses', function (Blueprint $table) {
            $table->index('status', 'idx_courses_status');
        });

        // 3. Mempercepat pencarian pengumpulan tugas milik mahasiswa
        // Indeks ini membantu ketika sistem menampilkan riwayat pengumpulan
        // tugas berdasarkan ID pengguna.
        // 
        // Catatan:
        // Tabel submissions sudah memiliki indeks gabungan dari kolom
        // assignment_id dan user_id. Namun, karena assignment_id berada
        // di posisi pertama, indeks tersebut kurang efektif untuk query
        // yang hanya menggunakan user_id sebagai kondisi pencarian.
        // Oleh karena itu, diperlukan indeks tambahan khusus pada user_id.
        Schema::table('submissions', function (Blueprint $table) {
            $table->index('user_id', 'idx_submissions_user_id');
        });

        // 4. Mempercepat pencarian tugas yang sudah diterbitkan
        // Membantu sistem mengambil daftar tugas dengan status published
        // yang dapat ditampilkan kepada mahasiswa.
        Schema::table('assignments', function (Blueprint $table) {
            $table->index('status', 'idx_assignments_status');
        });

        // 5. Mempercepat pengecekan jumlah notifikasi yang belum dibaca
        // Digunakan ketika sistem menampilkan jumlah notifikasi pada navbar.
        // Indeks gabungan ini mencakup jenis penerima, ID pengguna, dan
        // status waktu dibaca sehingga database dapat menemukan notifikasi
        // yang sesuai dengan lebih efisien.
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notifications_unread');
        });
    }

    /**
     * Menghapus seluruh indeks yang ditambahkan pada proses up()
     * sehingga struktur database kembali seperti sebelum migration dijalankan.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex('idx_courses_status');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('idx_submissions_user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_unread');
        });
    }
};