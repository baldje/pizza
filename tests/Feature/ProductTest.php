<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use App\Models\Product;
use Tests\TestCase;


class ProductTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private $validProductData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validProductData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 19.99,
            'category' => 'pizza'
        ];
    }

    /** @test */
    public function it_can_get_all_products()
    {
        Product::create($this->validProductData);
        Product::create(array_merge($this->validProductData, ['name' => 'Another Product']));

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Продукты получены',
            ])
            ->assertJsonCount(2, 'products');
    }

    /** @test */
    public function it_can_get_empty_products_list()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Продукты получены',
            ])
            ->assertJsonCount(0, 'products');
    }

    /** @test */
    public function it_can_get_specific_product()
    {
        $product = Product::create($this->validProductData);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Продукт получен',
            ])
            ->assertJsonFragment([
                'id' => $product->id,
                'name' => $this->validProductData['name'],
                'price' => (string)$this->validProductData['price']
            ]);
    }

    /** @test */
    public function it_returns_404_when_product_not_found()
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Продукт не найден',
            ]);
    }

    /** @test */
    public function it_can_create_a_product()
    {
        $response = $this->postJson('/api/products', $this->validProductData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Продукт успешно создан',
            ])
            ->assertJsonFragment($this->validProductData);

        $this->assertDatabaseHas('products', $this->validProductData);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_product()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'category']);
    }

    /** @test */
    public function it_validates_price_numeric()
    {
        $data = array_merge($this->validProductData, ['price' => 'not-a-number']);

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_validates_price_min_value()
    {
        $data = array_merge($this->validProductData, ['price' => 0]);

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_validates_category_in_allowed_values()
    {
        $data = array_merge($this->validProductData, ['category' => 'invalid-category']);

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    /** @test */

    /** @test */
    public function it_returns_404_when_updating_nonexistent_product()
    {
        $response = $this->putJson('/api/products/999', ['name' => 'Updated']);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Продукт не найден',
            ]);
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        $product = Product::create($this->validProductData);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Продукт успешно удален',
            ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_product()
    {
        $response = $this->deleteJson('/api/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Продукт не найден',
            ]);
    }

    /** @test */
}
