<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Получаем пользователей (кроме админа)
        $users = User::where('is_admin', false)->get();

        if ($users->isEmpty()) {
            $this->command->warn('Нет пользователей для создания заказов. Сначала запустите UserSeeder.');
            return;
        }

        // Создаем несколько заказов для каждого пользователя
        foreach ($users as $user) {
            // Заказ в процессе
            Order::create([
                'user_id' => $user->id,
                'status' => 'in_progress',
                'delivery_time' => Carbon::now()->addHours(1),
                'delivery_address' => $user->address,
            ]);

            // Заказ в доставке
            Order::create([
                'user_id' => $user->id,
                'status' => 'delivering',
                'delivery_time' => Carbon::now()->addMinutes(30),
                'delivery_address' => $user->address,
            ]);

            // Доставленный заказ
            Order::create([
                'user_id' => $user->id,
                'status' => 'delivered',
                'delivery_time' => Carbon::now()->subHours(2),
                'delivery_address' => $user->address,
            ]);

            // Отмененный заказ
            Order::create([
                'user_id' => $user->id,
                'status' => 'canceled',
                'delivery_time' => Carbon::now()->subHours(1),
                'delivery_address' => $user->address,
            ]);
        }

        // Создаем дополнительные заказы для демонстрации
        $firstUser = $users->first();

        // Заказ с другим адресом доставки
        Order::create([
            'user_id' => $firstUser->id,
            'status' => 'in_progress',
            'delivery_time' => Carbon::now()->addHours(2),
            'delivery_address' => 'ул. Рабочая, д. 15, офис 203',
        ]);

        // Заказ на завтра
        Order::create([
            'user_id' => $firstUser->id,
            'status' => 'in_progress',
            'delivery_time' => Carbon::tomorrow()->setTime(19, 0),
            'delivery_address' => $firstUser->address,
        ]);
    }
}
