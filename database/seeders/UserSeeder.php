<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Создаем администратора
        User::create([
            'name' => 'Администратор',
            'email' => 'admin@pizza.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 123-45-67',
            'address' => 'ул. Административная, д. 1',
            'is_admin' => true,
        ]);

        // Создаем тестового пользователя
        User::create([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 234-56-78',
            'address' => 'ул. Пользовательская, д. 10, кв. 5',
            'is_admin' => false,
        ]);

        // Создаем еще несколько пользователей
        User::create([
            'name' => 'Мария Сидорова',
            'email' => 'maria@example.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 345-67-89',
            'address' => 'ул. Тестовая, д. 25, кв. 12',
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Алексей Козлов',
            'email' => 'alexey@example.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 456-78-90',
            'address' => 'ул. Примерная, д. 7, кв. 3',
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Елена Волкова',
            'email' => 'elena@example.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 567-89-01',
            'address' => 'ул. Образцовая, д. 15, кв. 8',
            'is_admin' => false,
        ]);

        // Дополнительные пользователи с паролем 123456
        User::create([
            'name' => 'Сергей Николаев',
            'email' => 'sergey@example.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 678-90-12',
            'address' => 'ул. Новая, д. 5, кв. 7',
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Ольга Иванова',
            'email' => 'olga@example.com',
            'password' => Hash::make('123456'),
            'phone' => '+7 (999) 789-01-23',
            'address' => 'ул. Центральная, д. 12, кв. 4',
            'is_admin' => false,
        ]);
    }
}
