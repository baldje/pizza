<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем админа и получаем токен
        $this->admin = User::factory()->create(['is_admin' => true]);
        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);
        $this->token = $loginResponse->json('token');
    }

//    public function test_admin_dashboard_requires_authentication()
//    {
//        $this->getJson('/api/admin')
//            ->assertStatus(401);
//    }

    public function test_admin_dashboard_requires_admin_role()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userToken = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/admin')
            ->assertStatus(403);
    }


//    public function test_admin_orders_index_requires_authentication()
//    {
//        $this->getJson('/api/admin/orders')
//            ->assertStatus(401);
//    }

    public function test_admin_orders_index_requires_admin_role()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $userToken = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $userToken)
            ->getJson('/api/admin/orders')
            ->assertStatus(403);
    }


    public function test_admin_orders_store_creates_order()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'in_progress',
            'delivery_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'delivery_address' => '123 Admin St, City',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'price' => $product->price
                ]
            ]
        ];

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/orders', $payload)
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'order'
            ]);
    }


    public function test_admin_products_store_creates_product()
    {
        $payload = [
            'name' => 'Admin Pizza',
            'description' => 'Created by admin',
            'price' => '25.00',
            'category' => 'pizza',
        ];

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/products', $payload)
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'product'
            ])
            ->assertJsonFragment([
                'name' => 'Admin Pizza',
                'description' => 'Created by admin',
                'price' => '25.00',
                'category' => 'pizza',
            ]);
    }


    public function test_admin_products_update_updates_product()
    {
        $product = Product::factory()->create();

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => '30.00',
            'category' => 'drink',
        ];

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/admin/products/' . $product->id, $updateData)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'product'
            ])
            ->assertJsonFragment([
                'name' => 'Updated Product',
                'price' => '30.00',
                'category' => 'drink',
            ]);
    }

    public function test_admin_products_destroy_deletes_product()
    {
        $product = Product::factory()->create();

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/admin/products/' . $product->id)
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}


