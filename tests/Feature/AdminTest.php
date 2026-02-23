<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_admin_dashboard_requires_authentication()
    {
        $this->getJson('/api/admin')
            ->assertStatus(401);
    }

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

    public function test_admin_dashboard_returns_statistics()
    {
        // Создаем тестовые данные
        User::factory()->count(3)->create();
        Product::factory()->count(5)->create();
        Order::factory()->count(2)->create();

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users_count',
                    'products_count',
                    'orders_count',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'users_count' => 4, // 3 + 1 admin
                    'products_count' => 5,
                    'orders_count' => 2,
                ]
            ]);
    }

    public function test_admin_orders_index_requires_authentication()
    {
        $this->getJson('/api/admin/orders')
            ->assertStatus(401);
    }

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

    public function test_admin_orders_index_returns_orders_with_users()
    {
        $order = Order::factory()->create();

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/orders')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'order' => [
                    '*' => [
                        'id',
                        'user_id',
                        'status',
                        'delivery_time',
                        'delivery_address',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ]
                    ]
                ]
            ]);
    }

    public function test_admin_orders_create_returns_form_data()
    {
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/orders/create')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'users',
                'products'
            ]);
    }

    public function test_admin_orders_store_creates_order()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'in_progress',
            'delivery_time' => '2024-12-25 18:00:00',
            'delivery_address' => '123 Admin St, City',
        ];

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/orders', $payload)
            ->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'order'
            ])
            ->assertJsonFragment([
                'user_id' => $user->id,
                'status' => 'in_progress',
                'delivery_address' => '123 Admin St, City',
            ]);
    }

    public function test_admin_products_index_returns_products()
    {
        Product::factory()->count(3)->create();

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/products')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'products'
            ])
            ->assertJsonCount(3, 'products');
    }

    public function test_admin_products_create_returns_form_data()
    {
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/products/create')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'categories'
            ])
            ->assertJsonFragment([
                'categories' => ['pizza', 'drink', 'snack', 'dessert']
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

    public function test_admin_products_edit_returns_product_with_categories()
    {
        $product = Product::factory()->create();

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/products/' . $product->id . '/edit')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'product',
                'categories'
            ])
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => $product->name,
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
