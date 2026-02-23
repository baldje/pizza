<?php

namespace App\Http\Controllers\Validation;

class ValidationRulesHandler
{
    /**
     * Централизованные правила валидации по контексту
     */
    public static function getRules(string $context): array
    {
        $validationData = self::getValidationData($context);

        $rules = $validationData['rules'] ?? [];
        $messages = $validationData['messages'] ?? [];

        // Добавляем сообщения к правилам в формате Laravel
        foreach ($rules as $field => &$rule) {
            if (isset($messages[$field])) {
                $rule .= '|' . $messages[$field];
            }
        }

        return $rules;
    }

    /**
     * Получить кастомные сообщения об ошибках для контекста
     */
    public static function getMessages(string $context): array
    {
        $validationData = self::getValidationData($context);
        $messages = $validationData['messages'] ?? [];

        // Фильтруем только сообщения (убираем правила)
        $filteredMessages = [];
        foreach ($messages as $field => $message) {
            if (is_string($message) && !str_contains($message, '|')) {
                $filteredMessages[$field] = $message;
            }
        }

        return $filteredMessages;
    }

    /**
     * База данных валидации
     */
    protected static function getValidationData(string $context): array
    {
        return match ($context) {
            // User validation
            'store_user' => [
                'rules' => [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:6',
                    'phone' => 'nullable|string',
                    'address' => 'nullable|string',
                    'is_admin' => 'boolean',
                ],
                'messages' => [
                    'name' => 'message:Имя обязательно для заполнения',
                    'email' => 'message:Email обязателен для заполнения',
                    'email.email' => 'Неверный формат email',
                    'email.unique' => 'Пользователь с таким email уже существует',
                    'password' => 'message:Пароль обязателен для заполнения',
                    'password.min' => 'Пароль должен содержать минимум 6 символов',
                ]
            ],

            'update_user' => [
                'rules' => [
                    'name' => 'sometimes|required|string|max:255',
                    'email' => 'sometimes|required|string|email|max:255|unique:users,email,{id}',
                    'password' => 'sometimes|required|string|min:6',
                    'phone' => 'nullable|string',
                    'address' => 'nullable|string',
                    'is_admin' => 'boolean',
                ],
                'messages' => [
                    'name' => 'message:Имя обязательно для заполнения',
                    'email' => 'message:Email обязателен для заполнения',
                    'email.email' => 'Неверный формат email',
                    'email.unique' => 'Пользователь с таким email уже существует',
                    'password.min' => 'Пароль должен содержать минимум 6 символов',
                ]
            ],

            'register' => [
                'rules' => [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255|unique:users',
                    'password' => 'required|string|min:6|confirmed',
                ],
                'messages' => [
                    'name' => 'message:Имя обязательно для заполнения',
                    'email' => 'message:Email обязателен для заполнения',
                    'email.email' => 'Неверный формат email',
                    'email.unique' => 'Пользователь с таким email уже существует',
                    'password' => 'message:Пароль обязателен для заполнения',
                    'password.min' => 'Пароль должен содержать минимум 6 символов',
                    'password.confirmed' => 'Пароли не совпадают',
                ]
            ],

            'login' => [
                'rules' => [
                    'email' => 'required|string|email',
                    'password' => 'required|string',
                ],
                'messages' => [
                    'email' => 'message:Email обязателен для заполнения',
                    'email.email' => 'Неверный формат email',
                    'password' => 'message:Пароль обязателен для заполнения',
                ]
            ],

            // Order validation
            'store_order' => [
                'rules' => [
                    'user_id' => 'required|exists:users,id',
                    'status' => 'required|string|in:in_progress,delivering,delivered,canceled',
                    'delivery_time' => 'required|date|after:now',
                    'delivery_address' => 'required|string|max:500',
                    'items' => 'required|array|min:1',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.quantity' => 'required|integer|min:1',
                    'items.*.price' => 'required|numeric|min:0.01',
                ],
                'messages' => [
                    'user_id' => 'message:ID пользователя обязательно для заполнения',
                    'user_id.exists' => 'Пользователь не найден',
                    'status' => 'message:Статус обязателен для заполнения',
                    'status.in' => 'Недопустимый статус заказа',
                    'delivery_time' => 'message:Время доставки обязательно для заполнения',
                    'delivery_time.date' => 'Неверный формат времени доставки',
                    'delivery_time.after' => 'Время доставки должно быть в будущем',
                    'delivery_address' => 'message:Адрес доставки обязателен для заполнения',
                    'delivery_address.max' => 'Адрес доставки не должен превышать 500 символов',
                    'items' => 'message:Добавьте хотя бы один товар в заказ',
                    'items.min' => 'Добавьте хотя бы один товар в заказ',
                    'items.*.product_id' => 'message:ID товара обязательно для заполнения',
                    'items.*.product_id.exists' => 'Товар не найден',
                    'items.*.quantity' => 'message:Количество обязательно для заполнения',
                    'items.*.quantity.integer' => 'Количество должно быть целым числом',
                    'items.*.quantity.min' => 'Количество должно быть не менее 1',
                    'items.*.price' => 'message:Цена обязательна для заполнения',
                    'items.*.price.min' => 'Цена должна быть положительной',
                ]
            ],

            'update_order' => [
                'rules' => [
                    'user_id' => 'sometimes|required|exists:users,id',
                    'status' => 'sometimes|required|string|in:in_progress,delivering,delivered,canceled',
                    'delivery_time' => 'sometimes|required|date',
                    'delivery_address' => 'sometimes|required|string|max:500',
                    'items' => 'sometimes|required|array|min:1',
                    'items.*.product_id' => 'required_with:items|exists:products,id',
                    'items.*.quantity' => 'required|integer|min:1',
                    'items.*.price' => 'required_with:items|numeric|min:0.01',
                ],
                'messages' => [
                    'user_id' => 'message:ID пользователя обязательно для заполнения',
                    'user_id.exists' => 'Пользователь не найден',
                    'status' => 'message:Статус обязателен для заполнения',
                    'status.in' => 'Недопустимый статус заказа',
                    'delivery_time' => 'message:Время доставки обязательно для заполнения',
                    'delivery_time.date' => 'Неверный формат времени доставки',
                    'delivery_address' => 'message:Адрес доставки обязателен для заполнения',
                    'delivery_address.max' => 'Адрес доставки не должен превышать 500 символов',
                    'items' => 'message:Добавьте хотя бы один товар в заказ',
                    'items.min' => 'Добавьте хотя бы один товар в заказ',
                    'items.*.product_id' => 'message:ID товара обязательно для заполнения',
                    'items.*.product_id.exists' => 'Товар не найден',
                    'items.*.quantity' => 'message:Количество обязательно для заполнения',
                    'items.*.quantity.integer' => 'Количество должно быть целым числом',
                    'items.*.quantity.min' => 'Количество должно быть не менее 1',
                    'items.*.price' => 'message:Цена обязательна для заполнения',
                    'items.*.price.min' => 'Цена должна быть положительной',
                ]
            ],

            'update_order_status' => [
                'rules' => [
                    'status' => 'required|string|in:in_progress,delivering,delivered,canceled',
                ],
                'messages' => [
                    'status' => 'message:Статус обязателен для заполнения',
                    'status.in' => 'Недопустимый статус заказа',
                ]
            ],

            // Product validation
            'store_product' => [
                'rules' => [
                    'name' => 'required|string|max:255',
                    'description' => 'required|string',
                    'price' => 'required|numeric|min:0.01',
                    'category' => 'required|string|in:pizza,drink,snack,dessert',
                ],
                'messages' => [
                    'name' => 'message:Название товара обязательно для заполнения',
                    'name.max' => 'Название товара не должно превышать 255 символов',
                    'description' => 'message:Описание товара обязательно для заполнения',
                    'price' => 'message:Цена обязательна для заполнения',
                    'price.numeric' => 'Цена должна быть числом',
                    'price.min' => 'Цена должна быть положительной',
                    'category' => 'message:Категория обязательна для заполнения',
                    'category.in' => 'Недопустимая категория товара',
                ]
            ],

            'update_product' => [
                'rules' => [
                    'name' => 'sometimes|required|string|max:255',
                    'description' => 'sometimes|required|string',
                    'price' => 'sometimes|required|numeric|min:0.01',
                    'category' => 'sometimes|required|string|in:pizza,drink,snack,dessert',
                ],
                'messages' => [
                    'name' => 'message:Название товара обязательно для заполнения',
                    'name.max' => 'Название товара не должно превышать 255 символов',
                    'description' => 'message:Описание товара обязательно для заполнения',
                    'price' => 'message:Цена обязательна для заполнения',
                    'price.numeric' => 'Цена должна быть числом',
                    'price.min' => 'Цена должна быть положительной',
                    'category' => 'message:Категория обязательна для заполнения',
                    'category.in' => 'Недопустимая категория товара',
                ]
            ],

            // OrderItem validation
            'store_order_item' => [
                'rules' => [
                    'order_id' => 'required|exists:orders,id',
                    'product_id' => 'required|exists:products,id',
                    'quantity' => 'required|integer|min:1',
                    'price' => 'required|numeric|min:0.01',
                ],
                'messages' => [
                    'order_id' => 'message:ID заказа обязательно для заполнения',
                    'order_id.exists' => 'Заказ не найден',
                    'product_id' => 'message:ID товара обязательно для заполнения',
                    'product_id.exists' => 'Товар не найден',
                    'quantity' => 'message:Количество обязательно для заполнения',
                    'quantity.min' => 'Количество должно быть не менее 1',
                    'price' => 'message:Цена обязательна для заполнения',
                    'price.min' => 'Цена должна быть положительной',
                ]
            ],

            'update_order_item' => [
                'rules' => [
                    'order_id' => 'sometimes|required|exists:orders,id',
                    'product_id' => 'sometimes|required|exists:products,id',
                    'quantity' => 'sometimes|required|integer|min:1',
                    'price' => 'sometimes|required|numeric|min:0.01',
                ],
                'messages' => [
                    'order_id' => 'message:ID заказа обязательно для заполнения',
                    'order_id.exists' => 'Заказ не найден',
                    'product_id' => 'message:ID товара обязательно для заполнения',
                    'product_id.exists' => 'Товар не найден',
                    'quantity' => 'message:Количество обязательно для заполнения',
                    'quantity.min' => 'Количество должно быть не менее 1',
                    'price' => 'message:Цена обязательна для заполнения',
                    'price.min' => 'Цена должна быть положительной',
                ]
            ],

            default => [],
        };
    }

    /**
     * Альтернативный метод: получить правила и сообщения вместе
     */
    public static function getWithMessages(string $context)
    {
        $validationData = self::getValidationData($context);

        return [
            'rules' => $validationData['rules'] ?? [],
            'messages' => $validationData['messages'] ?? [],
        ];
    }

    /**
     * Получить все доступные контексты валидации
     */
    public static function getAvailableContexts()
    {
        return [
            'store_user',
            'update_user',
            'register',
            'login',
            'store_order',
            'update_order',
            'update_order_status',
            'store_product',
            'update_product',
            'store_order_item',
            'update_order_item',
        ];
    }
}
