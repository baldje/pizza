<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_orders()
    {
        Order::factory()->count(3)->create();

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_store_creates_order()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'in_progress',
            'delivery_time' => '2024-12-25 18:00:00',
            'delivery_address' => '123 Main St, City',
        ];

        $this->postJson('/api/orders', $payload)
            ->assertCreated()
            ->assertJsonFragment([
                'user_id' => $user->id,
                'status' => 'in_progress',
                'delivery_address' => '123 Main St, City',
            ]);
    }

    public function test_show_returns_order()
    {
        $order = Order::factory()->create();

        $this->getJson('/api/orders/' . $order->id)
            ->assertOk()
            ->assertJson(['id' => $order->id]);
    }

    public function test_update_updates_order()
    {
        $order = Order::factory()->create();

        $updateData = [
            'status' => 'delivering',
            'delivery_address' => '456 New St, City',
        ];

        $this->putJson('/api/orders/' . $order->id, $updateData)
            ->assertOk()
            ->assertJsonFragment([
                'status' => 'delivering',
                'delivery_address' => '456 New St, City',
            ]);
    }

    public function test_destroy_deletes_order()
    {
        $order = Order::factory()->create();

        $this->deleteJson('/api/orders/' . $order->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_store_requires_valid_user_id()
    {
        $payload = [
            'user_id' => 999, // Несуществующий пользователь
            'status' => 'in_progress',
            'delivery_time' => '2024-12-25 18:00:00',
            'delivery_address' => '123 Main St, City',
        ];

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    public function test_store_requires_valid_status()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'invalid_status', // Недопустимый статус
            'delivery_time' => '2024-12-25 18:00:00',
            'delivery_address' => '123 Main St, City',
        ];

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_requires_delivery_time()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'in_progress',
            // delivery_time отсутствует
            'delivery_address' => '123 Main St, City',
        ];

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_time']);
    }

    public function test_store_requires_delivery_address()
    {
        $user = User::factory()->create();

        $payload = [
            'user_id' => $user->id,
            'status' => 'in_progress',
            'delivery_time' => '2024-12-25 18:00:00',
            // delivery_address отсутствует
        ];

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_address']);
    }

    public function test_order_belongs_to_user()
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($order->user_id, $order->user->id);
    }

    public function test_order_has_many_order_items()
    {
        $order = Order::factory()->create();
        $orderItems = \App\Models\OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->orderItems);
        $this->assertInstanceOf(\App\Models\OrderItem::class, $order->orderItems->first());
    }

    public function test_user_has_many_orders()
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->orders);
        $this->assertInstanceOf(Order::class, $user->orders->first());
    }

    public function test_cascade_delete_when_user_deleted()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_valid_status_values()
    {
        $user = User::factory()->create();
        $validStatuses = ['in_progress', 'delivering', 'delivered', 'canceled'];

        foreach ($validStatuses as $status) {
            $payload = [
                'user_id' => $user->id,
                'status' => $status,
                'delivery_time' => '2024-12-25 18:00:00',
                'delivery_address' => '123 Main St, City',
            ];

            $this->postJson('/api/orders', $payload)
                ->assertCreated()
                ->assertJsonFragment(['status' => $status]);
        }
    }
}
