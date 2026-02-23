<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private $validOrderData;
    private $user;
    private $products;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->products = [
            Product::create([
                'name' => 'Pizza Margherita',
                'description' => 'Classic pizza with tomato and mozzarella',
                'price' => 12.99,
                'category' => 'pizza'
            ]),
            Product::create([
                'name' => 'Coca-Cola',
                'description' => 'Cold drink',
                'price' => 3.50,
                'category' => 'drink'
            ])
        ];

        $this->validOrderData = [
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'delivery_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'delivery_address' => '123 Main St, City',
            'items' => [
                [
                    'product_id' => $this->products[0]->id,
                    'quantity' => 2,
                    'price' => 12.99
                ],
                [
                    'product_id' => $this->products[1]->id,
                    'quantity' => 1,
                    'price' => 3.50
                ]
            ]
        ];
    }

    /** @test */
    public function it_can_create_an_order_with_items()
    {
        $response = $this->postJson('/api/orders', $this->validOrderData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Заказ успешно создан',
            ])
            ->assertJsonFragment([
                'user_id' => $this->user->id,
                'status' => 'in_progress',
                'delivery_address' => '123 Main St, City',
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'delivery_address' => '123 Main St, City',
        ]);
    }

    /** @test */
    public function it_creates_order_items_correctly()
    {
        $response = $this->postJson('/api/orders', $this->validOrderData);

        $response->assertStatus(201);

        $orderId = $response->json('order.id');

        // Проверяем, что items создались с правильными данными
        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $this->products[0]->id,
            'quantity' => 2,
            'price' => 12.99
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $this->products[1]->id,
            'quantity' => 1,
            'price' => 3.50
        ]);
    }

    /** @test */
    public function it_validates_items_required()
    {
        $data = array_merge($this->validOrderData, [
            'items' => [] // Пустой массив items
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function it_validates_items_array()
    {
        $data = array_merge($this->validOrderData, [
            'items' => 'not-an-array' // Не массив
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function it_validates_items_product_id_required()
    {
        $invalidItems = $this->validOrderData['items'];
        unset($invalidItems[0]['product_id']); // Убираем product_id

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /** @test */
    public function it_validates_items_product_id_exists()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['product_id'] = 999; // Несуществующий product_id

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /** @test */
    public function it_validates_items_quantity_required()
    {
        $invalidItems = $this->validOrderData['items'];
        unset($invalidItems[0]['quantity']); // Убираем quantity

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_validates_items_quantity_integer()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['quantity'] = 'not-a-number'; // Не число

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_validates_items_quantity_min_value()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['quantity'] = 0; // Меньше 1

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_validates_items_quantity_negative_value()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['quantity'] = -5; // Отрицательное число

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_validates_items_quantity_decimal_value()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['quantity'] = 2.5; // Дробное число

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_validates_items_price_required()
    {
        $invalidItems = $this->validOrderData['items'];
        unset($invalidItems[0]['price']); // Убираем price

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.price']);
    }

    /** @test */
    public function it_validates_items_price_numeric()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['price'] = 'not-a-number'; // Не число

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.price']);
    }

    /** @test */
    public function it_validates_items_price_min_value()
    {
        $invalidItems = $this->validOrderData['items'];
        $invalidItems[0]['price'] = 0; // Меньше 0.01

        $data = array_merge($this->validOrderData, [
            'items' => $invalidItems
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.price']);
    }

    /** @test */
    public function it_validates_delivery_time_after_now()
    {
        $data = array_merge($this->validOrderData, [
            'delivery_time' => now()->subHours(1)->format('Y-m-d H:i:s'), // В прошлом
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_time']);
    }

    /** @test */
    public function it_validates_delivery_address_max_length()
    {
        $data = array_merge($this->validOrderData, [
            'delivery_address' => str_repeat('a', 501), // Более 500 символов
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_address']);
    }

    /** @test */
    public function it_validates_status_in_enum_values()
    {
        $data = array_merge($this->validOrderData, [
            'status' => 'invalid_status', // Недопустимый статус
        ]);

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function it_returns_order_with_items()
    {
        $response = $this->postJson('/api/orders', $this->validOrderData);
        $orderId = $response->json('order.id');

        $response = $this->getJson("/api/orders/{$orderId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Заказ получен',
            ])
            ->assertJsonFragment([
                'product_id' => $this->products[0]->id,
                'price' => '12.99'
            ]);
    }

    /** @test */
    public function it_can_update_order_with_items()
    {
        // Сначала создаем заказ
        $response = $this->postJson('/api/orders', $this->validOrderData);
        $orderId = $response->json('order.id');

        // Обновляем заказ с новыми items
        $updateData = [
            'status' => 'delivering',
            'delivery_address' => '456 New St, City',
            'items' => [
                [
                    'product_id' => $this->products[0]->id,
                    'quantity' => 3, // Изменяем количество
                    'price' => 12.99
                ]
            ]
        ];

        $response = $this->putJson("/api/orders/{$orderId}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Заказ успешно обновлен',
            ]);

        // Проверяем, что items обновились
        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $this->products[0]->id,
            'quantity' => 3,
            'price' => 12.99
        ]);
    }

    /** @test */
    public function it_can_update_order_status()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'delivery_time' => now()->addHours(2),
            'delivery_address' => '123 Main St, City',
        ]);

        $updateData = [
            'status' => 'delivering'
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Заказ успешно обновлен',
            ])
            ->assertJsonFragment(['status' => 'delivering']);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_order()
    {
        $updateData = ['status' => 'delivering'];

        $response = $this->putJson('/api/orders/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Заказ не найден',
            ]);
    }

    /** @test */
    public function it_can_delete_an_order()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'delivery_time' => now()->addHours(2),
            'delivery_address' => '123 Main St, City',
        ]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Заказ успешно удален',
            ]);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_order()
    {
        $response = $this->deleteJson('/api/orders/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Заказ не найден',
            ]);
    }
}
