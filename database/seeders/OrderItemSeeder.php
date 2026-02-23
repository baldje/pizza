<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $orders = Order::all();
        $products = Product::all();

        if ($orders->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Нет заказов или продуктов для создания элементов заказов. Сначала запустите OrderSeeder и ProductSeeder.');
            return;
        }

        // Создаем элементы заказов для каждого заказа
        foreach ($orders as $order) {
            // Количество товаров в заказе (от 1 до 4)
            $itemCount = rand(1, 4);

            // Получаем случайные продукты
            $randomProducts = $products->random($itemCount);

            foreach ($randomProducts as $product) {
                // Количество товара (от 1 до 3)
                $quantity = rand(1, 3);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }
        }

        // Создаем дополнительные элементы для демонстрации популярных комбинаций
        $pizzaProducts = $products->where('category', 'pizza');
        $drinkProducts = $products->where('category', 'drink');
        $snackProducts = $products->where('category', 'snack');

        // Для некоторых заказов добавляем типичные комбинации
        $sampleOrders = $orders->take(3);

        foreach ($sampleOrders as $order) {
            // Очищаем существующие элементы для этих заказов
            OrderItem::where('order_id', $order->id)->delete();

            // Добавляем пиццу + напиток
            if ($pizzaProducts->isNotEmpty() && $drinkProducts->isNotEmpty()) {
                $pizza = $pizzaProducts->random();
                $drink = $drinkProducts->random();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $pizza->id,
                    'quantity' => 1,
                    'price' => $pizza->price,
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $drink->id,
                    'quantity' => 1,
                    'price' => $drink->price,
                ]);
            }
        }

        // Для одного заказа создаем большой заказ
        if ($orders->isNotEmpty()) {
            $bigOrder = $orders->last();
            OrderItem::where('order_id', $bigOrder->id)->delete();

            // Добавляем несколько пицц
            $pizzas = $pizzaProducts->take(2);
            foreach ($pizzas as $pizza) {
                OrderItem::create([
                    'order_id' => $bigOrder->id,
                    'product_id' => $pizza->id,
                    'quantity' => 2,
                    'price' => $pizza->price,
                ]);
            }

            // Добавляем напитки
            $drinks = $drinkProducts->take(2);
            foreach ($drinks as $drink) {
                OrderItem::create([
                    'order_id' => $bigOrder->id,
                    'product_id' => $drink->id,
                    'quantity' => 1,
                    'price' => $drink->price,
                ]);
            }

            // Добавляем закуску
            if ($snackProducts->isNotEmpty()) {
                $snack = $snackProducts->random();
                OrderItem::create([
                    'order_id' => $bigOrder->id,
                    'product_id' => $snack->id,
                    'quantity' => 1,
                    'price' => $snack->price,
                ]);
            }
        }
    }
}
