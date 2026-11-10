<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah tabel sessions untuk mendukung SESSION_DRIVER=database di produksi.
     *
     * Migrasi awal 0001_01_01_000000_create_users_table.php hanya membuat tabel
     * users, padahal .env.example dan konfigurasi Railway memakai database session
     * driver. Guard Schema::hasTable() menjaga idempotensi lintas environment.
     */
    public function up(): void
    {
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
