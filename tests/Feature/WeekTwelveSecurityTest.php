<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WeekTwelveSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Rute /tentang merender layout via @vite; manifest Vite tidak ada di test.
        $this->withoutVite();
    }

    /**
     * Celah eskalasi hak akses: GET /switch-role/{role} dulu langsung
     * auth()->login($user). Setelah ditutup, pada environment produksi
     * rute ini wajib menghasilkan 404 dan tidak mengubah status tamu.
     */
    public function test_switch_role_route_is_blocked_outside_local_and_testing_environments(): void
    {
        $originalEnv = $this->app['env'];

        try {
            // Simulasikan produksi. Rute sudah terdaftar saat boot dengan env
            // 'testing' (lapis pertama lolos), tetapi abort_unless() di dalam
            // closure harus menolak saat env berubah jadi 'production'.
            $this->app['env'] = 'production';

            $response = $this->get('/switch-role/admin');

            $response->assertNotFound();
            $this->assertGuest();
        } finally {
            $this->app['env'] = $originalEnv;
        }
    }

    /**
     * Regresi: View::composer('*') dulu mengisi $activeUser untuk tamu dengan
     * User pertama dari DB, sehingga tamu melihat tautan "Nilai Saya"
     * (role mahasiswa) atau "Pengguna" (role admin) di navbar. Setelah
     * diperbaiki, role tamu = 'guest' dan halaman publik tidak memicu query.
     */
    public function test_guest_tentang_page_renders_without_authenticated_role_links(): void
    {
        $response = $this->get('/tentang');

        $response->assertOk();
        $response->assertSee('Masuk');
        $response->assertDontSee('Nilai Saya');
        $response->assertDontSee('Pengguna');
    }

    /**
     * Reverse proxy Railway/Nginx menghentikan TLS di edge lalu meneruskan
     * request dengan X-Forwarded-Proto: https. Dengan trustProxies(at: '*'),
     * Laravel wajib membaca request sebagai secure dan mengambil host dari
     * X-Forwarded-Host.
     */
    public function test_reverse_proxy_https_headers_are_trusted(): void
    {
        $response = $this->get('/up', [
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Host' => 'kampuslms.up.railway.app',
        ]);

        $response->assertOk();
        $this->assertTrue(request()->isSecure());
        $this->assertSame('kampuslms.up.railway.app', request()->getHost());
    }

    /**
     * Produksi memakai SESSION_DRIVER=database sementara phpunit.xml memakai
     * array. Pastikan tabel sessions tersedia dan rute web dapat menyimpan
     * sesi tanpa error 500.
     */
    public function test_database_session_driver_can_serve_web_routes(): void
    {
        $this->assertTrue(Schema::hasTable('sessions'));

        config(['session.driver' => 'database']);
        app('session')->forgetDrivers();

        $response = $this->get('/login');

        $response->assertOk();
        $this->assertSame(1, DB::table('sessions')->count());
    }
}
