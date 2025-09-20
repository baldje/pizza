<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_order_items()
    {
        OrderItem::factory()->count(3)->create();

        $this->getJson('/api/order-items')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_store_creates_order_item()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();

        $payload = [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => '15.50',
        ];

        $this->postJson('/api/order-items', $payload)
            ->assertCreated()
            ->assertJsonFragment([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => '15.50',
            ]);
    }

    public function test_show_returns_order_item()
    {
        $orderItem = OrderItem::factory()->create();

        $this->getJson('/api/order-items/' . $orderItem->id)
            ->assertOk()
            ->assertJson(['id' => $orderItem->id]);
    }

    public function test_update_updates_order_item()
    {
        $orderItem = OrderItem::factory()->create();

        $updateData = [
            'quantity' => 5,
            'price' => '25.00',
        ];

        $this->putJson('/api/order-items/' . $orderItem->id, $updateData)
            ->assertOk()
            ->assertJsonFragment([
                'quantity' => 5,
                'price' => '25.00',
            ]);
    }

    public function test_destroy_deletes_order_item()
    {
        $orderItem = OrderItem::factory()->create();

        $this->deleteJson('/api/order-items/' . $orderItem->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function test_store_requires_valid_order_id()
    {
        $product = Product::factory()->create();

        $payload = [
            'order_id' => 999, // Несуществующий заказ
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => '10.00',
        ];

        $this->postJson('/api/order-items', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_store_requires_valid_product_id()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $payload = [
            'order_id' => $order->id,
            'product_id' => 999, // Несуществующий продукт
            'quantity' => 1,
            'price' => '10.00',
        ];

        $this->postJson('/api/order-items', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_store_requires_positive_quantity()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();

        $payload = [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 0, // Недопустимое количество
            'price' => '10.00',
        ];

        $this->postJson('/api/order-items', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_store_requires_price()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create();

        $payload = [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            // price отсутствует
        ];

        $this->postJson('/api/order-items', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_order_item_belongs_to_order()
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertEquals($orderItem->order_id, $orderItem->order->id);
    }

    public function test_order_item_belongs_to_product()
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertInstanceOf(Product::class, $orderItem->product);
        $this->assertEquals($orderItem->product_id, $orderItem->product->id);
    }

    public function test_order_has_many_order_items()
    {
        $order = Order::factory()->create();
        $orderItems = OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->orderItems);
        $this->assertInstanceOf(OrderItem::class, $order->orderItems->first());
    }

    public function test_product_has_many_order_items()
    {
        $product = Product::factory()->create();
        $orderItems = OrderItem::factory()->count(2)->create(['product_id' => $product->id]);

        $this->assertCount(2, $product->orderItems);
        $this->assertInstanceOf(OrderItem::class, $product->orderItems->first());
    }

    public function test_cascade_delete_when_order_deleted()
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $order->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }

    public function test_cascade_delete_when_product_deleted()
    {
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_id' => $product->id]);

        $product->delete();

        $this->assertDatabaseMissing('order_items', ['id' => $orderItem->id]);
    }
}
