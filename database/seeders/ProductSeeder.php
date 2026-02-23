<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Пиццы
        $pizzas = [
            [
                'name' => 'Маргарита',
                'description' => 'Классическая пицца с томатным соусом, моцареллой и базиликом',
                'price' => '450',
                'category' => 'pizza',
            ],
            [
                'name' => 'Пепперони',
                'description' => 'Острая пицца с колбасой пепперони и сыром моцарелла',
                'price' => '550',
                'category' => 'pizza',
            ],
            [
                'name' => 'Четыре сыра',
                'description' => 'Пицца с моцареллой, пармезаном, горгонзолой и чеддером',
                'price' => '600',
                'category' => 'pizza',
            ],
            [
                'name' => 'Гавайская',
                'description' => 'Пицца с ветчиной, ананасом и сыром моцарелла',
                'price' => '520',
                'category' => 'pizza',
            ],
            [
                'name' => 'Мясная',
                'description' => 'Пицца с ветчиной, беконом, колбасой и говядиной',
                'price' => '650',
                'category' => 'pizza',
            ],
            [
                'name' => 'Вегетарианская',
                'description' => 'Пицца с помидорами, перцем, луком, грибами и оливками',
                'price' => '480',
                'category' => 'pizza',
            ],
        ];

        // Напитки
        $drinks = [
            [
                'name' => 'Кока-Кола',
                'description' => 'Газированный напиток 0.5л',
                'price' => '120',
                'category' => 'drink',
            ],
            [
                'name' => 'Пепси',
                'description' => 'Газированный напиток 0.5л',
                'price' => '120',
                'category' => 'drink',
            ],
            [
                'name' => 'Спрайт',
                'description' => 'Лимонно-лаймовый газированный напиток 0.5л',
                'price' => '120',
                'category' => 'drink',
            ],
            [
                'name' => 'Сок апельсиновый',
                'description' => 'Натуральный апельсиновый сок 0.3л',
                'price' => '150',
                'category' => 'drink',
            ],
            [
                'name' => 'Вода минеральная',
                'description' => 'Минеральная вода без газа 0.5л',
                'price' => '80',
                'category' => 'drink',
            ],
        ];

        // Закуски
        $snacks = [
            [
                'name' => 'Картофель фри',
                'description' => 'Хрустящий картофель фри с солью',
                'price' => '200',
                'category' => 'snack',
            ],
            [
                'name' => 'Куриные крылышки',
                'description' => 'Острые куриные крылышки в соусе барбекю',
                'price' => '350',
                'category' => 'snack',
            ],
            [
                'name' => 'Наггетсы',
                'description' => 'Куриные наггетсы с соусом',
                'price' => '280',
                'category' => 'snack',
            ],
            [
                'name' => 'Сырные палочки',
                'description' => 'Хрустящие сырные палочки с чесночным соусом',
                'price' => '250',
                'category' => 'snack',
            ],
        ];

        // Десерты
        $desserts = [
            [
                'name' => 'Тирамису',
                'description' => 'Классический итальянский десерт с кофе и маскарпоне',
                'price' => '300',
                'category' => 'dessert',
            ],
            [
                'name' => 'Чизкейк',
                'description' => 'Нью-Йоркский чизкейк с ягодным соусом',
                'price' => '280',
                'category' => 'dessert',
            ],
            [
                'name' => 'Мороженое',
                'description' => 'Ванильное мороженое с шоколадной крошкой',
                'price' => '150',
                'category' => 'dessert',
            ],
        ];

        // Создаем все продукты
        foreach (array_merge($pizzas, $drinks, $snacks, $desserts) as $product) {
            Product::create($product);
        }
    }
}
