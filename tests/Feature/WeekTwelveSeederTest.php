<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class WeekTwelveSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji 1: DatabaseSeeder berjalan sukses tanpa faker dan memenuhi seluruh kontrak data spesifikasi.
     */
    public function test_database_seeder_populates_required_data_without_faker(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verifikasi pengguna
        $this->assertGreaterThanOrEqual(1, User::where('role', 'admin')->count());
        $this->assertGreaterThanOrEqual(3, User::where('role', 'dosen')->count());
        $this->assertGreaterThanOrEqual(30, User::where('role', 'mahasiswa')->count());

        // Verifikasi 3 akun demo wajib
        $this->assertDatabaseHas('users', ['email' => 'admin@kampuslms.test', 'role' => 'admin']);
        $this->assertDatabaseHas('users', ['email' => 'dosen@kampuslms.test', 'role' => 'dosen']);
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@kampuslms.test', 'role' => 'mahasiswa']);

        // Verifikasi 5 mata kuliah dan seluruhnya status 'active'
        $this->assertEquals(5, Course::count());
        $this->assertEquals(5, Course::where('status', 'active')->count());

        // Verifikasi 15 penugasan
        $this->assertEquals(15, Assignment::count());

        // Verifikasi tepat 125 submission
        $this->assertEquals(125, Submission::count());

        // Verifikasi submission pertama milik demo
        $firstSubmission = Submission::find(1);
        $this->assertNotNull($firstSubmission);
        $mahasiswaDemo = User::where('email', 'mahasiswa@kampuslms.test')->first();
        $this->assertEquals($mahasiswaDemo->id, $firstSubmission->user_id);

        // Verifikasi penilaian (>= 70 grades) dan submission demo dinilai
        $this->assertGreaterThanOrEqual(70, Grade::count());
        $this->assertNotNull($firstSubmission->grade);
    }

    /**
     * Uji 2: DemoAccountSeeder melempar RuntimeException di produksi jika password tidak aman / kosong.
     */
    public function test_demo_account_seeder_throws_exception_on_weak_password_in_production(): void
    {
        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            // Skenario 1: Password kosong
            Config::set('demo.admin_password', null);
            Config::set('demo.dosen_password', 'KuatSekali123!');
            Config::set('demo.mahasiswa_password', 'KuatSekali123!');

            $this->expectException(RuntimeException::class);
            $this->artisan('db:seed', [
                '--class' => DemoAccountSeeder::class,
                '--force' => true,
            ]);
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }

    /**
     * Uji 3: DemoAccountSeeder melempar RuntimeException di produksi jika password masih default 'password'.
     */
    public function test_demo_account_seeder_throws_exception_on_default_password_in_production(): void
    {
        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            Config::set('demo.admin_password', 'password');
            Config::set('demo.dosen_password', 'KuatSekali123!');
            Config::set('demo.mahasiswa_password', 'KuatSekali123!');

            $this->expectException(RuntimeException::class);
            $this->artisan('db:seed', [
                '--class' => DemoAccountSeeder::class,
                '--force' => true,
            ]);
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }

    /**
     * Uji 4: DemoAccountSeeder berhasil memperbarui password 3 akun demo dengan kata sandi kuat di produksi.
     */
    public function test_demo_account_seeder_successfully_updates_demo_passwords_in_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $originalEnv = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            Config::set('demo.admin_password', 'AdminProd@2026Secure!');
            Config::set('demo.dosen_password', 'DosenProd#2026Lms!');
            Config::set('demo.mahasiswa_password', 'MhsProd$2026Itk!');

            $this->artisan('db:seed', [
                '--class' => DemoAccountSeeder::class,
                '--force' => true,
            ])->assertSuccessful();

            $admin = User::where('email', 'admin@kampuslms.test')->first();
            $dosen = User::where('email', 'dosen@kampuslms.test')->first();
            $mahasiswa = User::where('email', 'mahasiswa@kampuslms.test')->first();

            $this->assertTrue(Hash::check('AdminProd@2026Secure!', $admin->password));
            $this->assertTrue(Hash::check('DosenProd#2026Lms!', $dosen->password));
            $this->assertTrue(Hash::check('MhsProd$2026Itk!', $mahasiswa->password));
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }
}
