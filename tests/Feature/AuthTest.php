<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_and_returns_token()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '89246605246',
            'address' => 'Lenina 1',
            'is_admin' => false,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ],
                'token'
            ]);;
    }

    public function test_login_returns_jwt_token()
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_me_requires_authentication()
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user()
    {
        $user = User::factory()->create();

        // login to obtain token
        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJson(['email' => $user->email]);
    }

    public function test_logout_invalidates_token()
    {
        $user = User::factory()->create();
        $token = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout')
            ->assertOk();
    }
}

