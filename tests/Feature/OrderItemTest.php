<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private array $validOrderItemData;
    private $user;
    private $order;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'status' => 'in_progress',
            'delivery_time' => now()->addHours(2),
            'delivery_address' => '123 Main St, City',
        ]);

        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 15.99,
            'category' => 'pizza'
        ]);

        $this->validOrderItemData = [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 15.99
        ];
    }

    /** @test */
    public function it_can_get_all_order_items()
    {
        $orderItem1 = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 15.99
        ]);

        $orderItem2 = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 15.99
        ]);

        $response = $this->getJson('/api/order-items');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Элементы заказов получены',
            ])
            ->assertJsonCount(2, 'order_items');
    }

    /** @test */
    public function it_can_get_empty_order_items_list()
    {
        $response = $this->getJson('/api/order-items');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Элементы заказов получены',
            ])
            ->assertJsonCount(0, 'order_items');
    }

    /** @test */
    public function it_can_update_an_order_item()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $updateData = [
            'quantity' => 5,
            'price' => 25.99,
        ];

        $response = $this->putJson("/api/order-items/{$orderItem->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Элемент заказа успешно обновлен',
            ])
            ->assertJsonFragment([
                'quantity' => 5,
                'price' => 25.99
            ]);

        $this->assertDatabaseHas('order_items', array_merge(['id' => $orderItem->id], $updateData));
    }

    /** @test */
    public function it_can_get_specific_order_item()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $response = $this->getJson("/api/order-items/{$orderItem->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Элемент заказа получен',
            ])
            ->assertJsonFragment([
                'id' => $orderItem->id,
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'quantity' => 2,
                'price' => '15.99'
            ]);
    }

    /** @test */
    public function it_returns_404_when_order_item_not_found()
    {
        $response = $this->getJson('/api/order-items/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Элемент заказа не найден',
            ]);
    }

    /** @test */
    public function it_can_create_an_order_item()
    {
        $response = $this->postJson('/api/order-items', $this->validOrderItemData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Элемент заказа успешно создан',
            ])
            ->assertJsonFragment($this->validOrderItemData);

        $this->assertDatabaseHas('order_items', $this->validOrderItemData);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_order_item()
    {
        $response = $this->postJson('/api/order-items', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'order_id', 'product_id', 'quantity', 'price'
            ]);
    }

    /** @test */
    public function it_validates_order_id_exists()
    {
        $data = array_merge($this->validOrderItemData, [
            'order_id' => 999, // Несуществующий заказ
        ]);

        $response = $this->postJson('/api/order-items', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    /** @test */
    public function it_validates_product_id_exists()
    {
        $data = array_merge($this->validOrderItemData, [
            'product_id' => 999, // Несуществующий продукт
        ]);

        $response = $this->postJson('/api/order-items', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function it_validates_quantity_min_value()
    {
        $data = array_merge($this->validOrderItemData, [
            'quantity' => 0, // Меньше 1
        ]);

        $response = $this->postJson('/api/order-items', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_validates_quantity_integer()
    {
        $data = array_merge($this->validOrderItemData, [
            'quantity' => 'not-a-number', // Не число
        ]);

        $response = $this->postJson('/api/order-items', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function it_validates_price_min_value()
    {
        $data = array_merge($this->validOrderItemData, [
            'price' => 0, // Меньше 0.01
        ]);

        $response = $this->postJson('/api/order-items', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_validates_price_numeric()
    {
        $data = array_merge($this->validOrderItemData, [
            'price' => 'invalid-price', // Не число
        ]);

        $response = $this->postJson('/api/order-items', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_order_item()
    {
        $updateData = [
            'quantity' => 5,
            'price' => 25.99,
        ];

        $response = $this->putJson('/api/order-items/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Элемент заказа не найден',
            ]);
    }

    /** @test */
    public function it_can_delete_an_order_item()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $response = $this->deleteJson("/api/order-items/{$orderItem->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Элемент заказа успешно удален',
            ]);

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_order_item()
    {
        $response = $this->deleteJson('/api/order-items/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Элемент заказа не найден',
            ]);
    }

    /** @test */
    public function it_validates_fields_when_updating_order_item()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $response = $this->putJson("/api/order-items/{$orderItem->id}", [
            'quantity' => 0,
            'price' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity', 'price']);
    }

    /** @test */
    public function test_order_item_belongs_to_order()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertEquals($orderItem->order_id, $orderItem->order->id);
    }

    /** @test */
    public function test_order_item_belongs_to_product()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $this->assertInstanceOf(Product::class, $orderItem->product);
        $this->assertEquals($orderItem->product_id, $orderItem->product->id);
    }

    /** @test */
    public function test_order_has_many_order_items()
    {
        $orderItem1 = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 15.99
        ]);

        $orderItem2 = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 15.99
        ]);

        $this->assertCount(2, $this->order->orderItems);
        $this->assertInstanceOf(OrderItem::class, $this->order->orderItems->first());
    }

    /** @test */
    public function test_product_has_many_order_items()
    {
        $orderItem1 = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 15.99
        ]);

        $orderItem2 = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 15.99
        ]);

        $this->assertCount(2, $this->product->orderItems);
        $this->assertInstanceOf(OrderItem::class, $this->product->orderItems->first());
    }

    /** @test */
    public function test_cascade_delete_when_order_deleted()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $this->order->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    /** @test */
    public function test_cascade_delete_when_product_deleted()
    {
        $orderItem = OrderItem::create($this->validOrderItemData);

        $this->product->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }
}
