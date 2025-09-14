<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_users(): void
    {
        User::factory()->count(2)->create();

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure([
                ['id','name','email']
            ]);
    }

    public function test_store_creates_user(): void
    {
        $payload = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password',
        ];

        $this->postJson('/api/users', $payload)
            ->assertCreated()
            ->assertJsonFragment(['email' => 'alice@example.com']);
    }

    public function test_show_returns_user(): void
    {
        $user = User::factory()->create();

        $this->getJson('/api/users/' . $user->id)
            ->assertOk()
            ->assertJson(['id' => $user->id]);
    }

    public function test_update_updates_user(): void
    {
        $user = User::factory()->create();

        $this->putJson('/api/users/' . $user->id, ['name' => 'Bob'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Bob']);
    }

    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();

        $this->deleteJson('/api/users/' . $user->id)
            ->assertNoContent();
    }
}

