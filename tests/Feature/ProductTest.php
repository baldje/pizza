<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_products(): void
    {
        Product::factory()->count(3)->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_store_creates_product(): void
    {
        $payload = [
            'name' => 'Margherita',
            'description' => 'Classic pizza',
            'price' => 9.99,
            'category' => 'pizza',
        ];

        $this->postJson('/api/products', $payload)
            ->assertCreated()
            ->assertJsonFragment(['name' => 'Margherita']);
    }

    public function test_show_returns_product(): void
    {
        $product = Product::factory()->create();

        $this->getJson('/api/products/' . $product->id)
            ->assertOk()
            ->assertJson(['id' => $product->id]);
    }

    public function test_update_updates_product(): void
    {
        $product = Product::factory()->create();

        $this->putJson('/api/products/' . $product->id, ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'New Name']);
    }

    public function test_destroy_deletes_product(): void
    {
        $product = Product::factory()->create();

        $this->deleteJson('/api/products/' . $product->id)
            ->assertNoContent();
    }
}

