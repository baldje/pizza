<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    // database/factories/OrderFactory.php
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['delivering','delivered', 'in_progress', 'canceled']),
            'delivery_time' => now()->addDays(2),
            'delivery_address' => $this->faker->address,
        ];
    }
}

