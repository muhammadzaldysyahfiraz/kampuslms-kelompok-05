<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit migrasi 2026_11_03_000001_add_performance_indexes:
     * dari 5 index, hanya idx_users_role yang benar-benar duplikat.
     *
     * - idx_users_role → users.role SUDAH punya users_role_index dari migrasi
     *   awal (0001_01_01_000000_create_users_table.php baris 24,
     *   ->default('mahasiswa')->index()). Menyimpan keduanya hanya membebani
     *   INSERT/UPDATE tanpa mempercepat SELECT.
     *
     * - idx_submissions_user_id → DIPERTAHANKAN. Setelah migrasi Farin jalan,
     *   MySQL memakai index ini sebagai supporting index untuk FK
     *   submissions_user_id_foreign (constraint-nya masih aktif, dibuktikan
     *   lewat information_schema.KEY_COLUMN_USAGE). Dropping-nya akan
     *   melanggar foreign key constraint.
     *
     * - idx_courses_status, idx_assignments_status, idx_notifications_unread
     *   → dipertahankan, semuanya melayani query nyata (filter status,
     *   badge lonceng notifikasi).
     *
     * Menggunakan Schema::hasIndex() supaya idempoten (aman dijalankan ulang
     * di mesin yang berbeda state-nya).
     */
    public function up(): void
    {
        if (Schema::hasIndex('users', 'idx_users_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_role');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
        });
    }
};