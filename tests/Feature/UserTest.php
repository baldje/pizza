<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use App\Models\User;
use Tests\TestCase;


class UserTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private $validUserData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validUserData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    /** @test */
    public function it_can_get_all_users()
    {
        // Создаем пользователей вручную
        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователи получены',
            ])
            ->assertJsonCount(2, 'users');
    }

    /** @test */
    public function it_can_get_empty_users_list()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователи получены',
            ])
            ->assertJsonCount(0, 'users');
    }

    /** @test */
    public function it_can_get_specific_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователь получен',
            ])
            ->assertJsonFragment([
                'id' => $user->id,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
    }

    /** @test */
    public function it_returns_404_when_user_not_found()
    {
        $response = $this->getJson('/api/users/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Пользователь не найден',
            ]);
    }

    /** @test */
    public function it_can_create_a_user()
    {
        $response = $this->postJson('/api/users', $this->validUserData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователь успешно создан',
            ])
            ->assertJsonFragment([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_user()
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name', 'email', 'password'
            ]);
    }

    /** @test */
    public function it_validates_email_format()
    {
        $data = array_merge($this->validUserData, [
            'email' => 'invalid-email',
        ]);

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_email_unique()
    {
        // Сначала создаем пользователя
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $data = array_merge($this->validUserData, [
            'email' => 'existing@example.com', // Существующий email
        ]);

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_min_length()
    {
        $data = array_merge($this->validUserData, [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }



    /** @test */
    public function it_can_update_a_user()
    {
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'password' => bcrypt('password'),
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователь успешно обновлен',
            ])
            ->assertJsonFragment([
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $this->assertDatabaseHas('users', array_merge(['id' => $user->id], $updateData));
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_user()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson('/api/users/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Пользователь не найден',
            ]);
    }

    /** @test */
    public function it_validates_fields_when_updating_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->putJson("/api/users/{$user->id}", [
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователь успешно удален',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_user()
    {
        $response = $this->deleteJson('/api/users/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Пользователь не найден',
            ]);
    }

    /** @test */
    public function it_can_update_user_password()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('old-password'),
        ]);

        $updateData = [
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Пользователь успешно обновлен',
            ]);
    }
}
