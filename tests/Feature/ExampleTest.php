<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $user = \App\Models\User::factory()->create(['role' => 'mahasiswa']);
        $dashboard = $this->actingAs($user)->get('/dashboard');
        $dashboard->assertStatus(200);
    }
}
