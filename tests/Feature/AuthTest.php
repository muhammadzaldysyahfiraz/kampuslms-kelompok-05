<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertSee('Akun Demo Pengujian');
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create([
            'role' => 'mahasiswa',
        ]);

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@kampuslms.test',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        $response = $this->post('/login', [
            'email' => 'mahasiswa@kampuslms.test',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
        $this->assertEquals('mahasiswa', session('active_role'));
    }

    public function test_user_cannot_login_with_invalid_credentials_and_prevents_user_enumeration(): void
    {
        User::factory()->create([
            'email' => 'registered@kampuslms.test',
            'password' => bcrypt('password'),
        ]);

        // Uji email terdaftar dengan password salah
        $response1 = $this->from('/login')->post('/login', [
            'email' => 'registered@kampuslms.test',
            'password' => 'wrong-password',
        ]);

        $response1->assertRedirect('/login');
        $response1->assertSessionHasErrors('email');
        $this->assertGuest();

        // Uji email tidak terdaftar
        $response2 = $this->from('/login')->post('/login', [
            'email' => 'nonexistent@kampuslms.test',
            'password' => 'random-password',
        ]);

        $response2->assertRedirect('/login');
        $response2->assertSessionHasErrors('email');

        // Pastikan kedua pesan error identik (mencegah user enumeration)
        $msg1 = session('errors')->get('email')[0];
        $this->assertEquals('Email atau kata sandi yang Anda masukkan salah.', $msg1);
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create([
            'role' => 'dosen',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
