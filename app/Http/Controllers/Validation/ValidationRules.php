<?php

namespace App\Http\Controllers\Validation;

class ValidationRules
{
    /**
     * Централизованные правила валидации по контексту
     */
    public static function getRules(string $context): array
    {
        return match ($context) {
            // User rules
            'store_user' => [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'is_admin' => 'boolean',
            ],

            'update_user' => [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,{id}',
                'password' => 'nullable|string|min:6',
                'phone' => 'nullable|string',
                'address' => 'nullable|string',
                'is_admin' => 'boolean',
            ],

            'register' => [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ],

            'login' => [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ],

            // Order rules
            'store_order' => [
                'user_id' => 'required|exists:users,id',
                'status' => 'required|string|in:in_progress,delivering,delivered,canceled',
                'delivery_time' => 'required|date|after:now',
                'delivery_address' => 'required|string|max:500',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0.01',
            ],

            'update_order' => [
                'user_id' => 'sometimes|required|exists:users,id',
                'status' => 'sometimes|required|string|in:in_progress,delivering,delivered,canceled',
                'delivery_time' => 'sometimes|required|date',
                'delivery_address' => 'sometimes|required|string|max:500',
                'items' => 'sometimes|required|array|min:1',
                'items.*.product_id' => 'required_with:items|exists:products,id',
                'items.*.quantity' => 'required_with:items|integer|min:1',
                'items.*.price' => 'required_with:items|numeric|min:0.01',
            ],

            'update_order_status' => [
                'status' => 'required|string|in:in_progress,delivering,delivered,canceled',
            ],

            // Product rules
            'store_product' => [
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0.01',
                'category' => 'required|string|in:pizza,drink,snack,dessert',
                'stock_quantity' => 'required|integer|min:0',
                'image_url' => 'nullable|string|url',
            ],

            'update_product' => [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|numeric|min:0.01',
                'category' => 'sometimes|required|string|in:pizza,drink,snack,dessert',
                'stock_quantity' => 'sometimes|required|integer|min:0',
                'image_url' => 'nullable|string|url',
            ],


            // OrderItem rules
            'store_order_item' => [
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0.01',
            ],

            'update_order_item' => [
                'order_id' => 'sometimes|required|exists:orders,id',
                'product_id' => 'sometimes|required|exists:products,id',
                'quantity' => 'sometimes|required|integer|min:1',
                'price' => 'sometimes|required|numeric|min:0.01',
            ],

            default => [],
        };
    }

    /**
     * Получить кастомные сообщения об ошибках для контекста
     */
    public static function getMessages(string $context): array
    {
        return match ($context) {
            // User messages
            'store_user' => [
                'name.required' => 'Имя обязательно для заполнения',
                'email.required' => 'Email обязателен для заполнения',
                'email.email' => 'Неверный формат email',
                'email.unique' => 'Пользователь с таким email уже существует',
                'password.required' => 'Пароль обязателен для заполнения',
                'password.min' => 'Пароль должен содержать минимум 6 символов',
            ],

            'update_user' => [
                'name.required' => 'Имя обязательно для заполнения',
                'email.required' => 'Email обязателен для заполнения',
                'email.email' => 'Неверный формат email',
                'email.unique' => 'Пользователь с таким email уже существует',
                'password.min' => 'Пароль должен содержать минимум 6 символов',
            ],

            'register' => [
                'name.required' => 'Имя обязательно для заполнения',
                'email.required' => 'Email обязателен для заполнения',
                'email.email' => 'Неверный формат email',
                'email.unique' => 'Пользователь с таким email уже существует',
                'password.required' => 'Пароль обязателен для заполнения',
                'password.min' => 'Пароль должен содержать минимум 6 символов',
                'password.confirmed' => 'Пароли не совпадают',
            ],

            'login' => [
                'email.required' => 'Email обязателен для заполнения',
                'email.email' => 'Неверный формат email',
                'password.required' => 'Пароль обязателен для заполнения',
            ],

            // Order messages
            'store_order', 'update_order' => [
                'user_id.required' => 'ID пользователя обязательно для заполнения',
                'user_id.exists' => 'Пользователь не найден',
                'status.required' => 'Статус обязателен для заполнения',
                'status.in' => 'Недопустимый статус заказа',
                'delivery_time.required' => 'Время доставки обязательно для заполнения',
                'delivery_time.date' => 'Неверный формат времени доставки',
                'delivery_time.after' => 'Время доставки должно быть в будущем',
                'delivery_address.required' => 'Адрес доставки обязателен для заполнения',
                'delivery_address.max' => 'Адрес доставки не должен превышать 500 символов',
                'items.required' => 'Добавьте хотя бы один товар в заказ',
                'items.min' => 'Добавьте хотя бы один товар в заказ',
                'items.*.product_id.required' => 'ID товара обязательно для заполнения',
                'items.*.product_id.exists' => 'Товар не найден',
                'items.*.quantity.required' => 'Количество обязательно для заполнения',
                'items.*.quantity.min' => 'Количество должно быть не менее 1',
                'items.*.price.required' => 'Цена обязательна для заполнения',
                'items.*.price.min' => 'Цена должна быть положительной',
            ],

            'update_order_status' => [
                'status.required' => 'Статус обязателен для заполнения',
                'status.in' => 'Недопустимый статус заказа',
            ],

            // Product messages
            'store_product', 'update_product' => [
                'name.required' => 'Название товара обязательно для заполнения',
                'name.max' => 'Название товара не должно превышать 255 символов',
                'description.required' => 'Описание товара обязательно для заполнения',
                'price.required' => 'Цена обязательна для заполнения',
                'price.numeric' => 'Цена должна быть числом',
                'price.min' => 'Цена должна быть положительной',
                'category.required' => 'Категория обязательна для заполнения',
                'category.in' => 'Недопустимая категория товара',
                'stock_quantity.required' => 'Количество на складе обязательно для заполнения',
                'stock_quantity.integer' => 'Количество должно быть целым числом',
                'stock_quantity.min' => 'Количество не может быть отрицательным',
            ],

            // OrderItem messages
            'store_order_item', 'update_order_item' => [
                'order_id.required' => 'ID заказа обязательно для заполнения',
                'order_id.exists' => 'Заказ не найден',
                'product_id.required' => 'ID товара обязательно для заполнения',
                'product_id.exists' => 'Товар не найден',
                'quantity.required' => 'Количество обязательно для заполнения',
                'quantity.min' => 'Количество должно быть не менее 1',
                'price.required' => 'Цена обязательна для заполнения',
                'price.min' => 'Цена должна быть положительной',
            ],

            default => [],
        };
    }
}
