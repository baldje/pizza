<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRules;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Список пользователей
     */
    public function index()
    {
        try {
            $users = User::all();

            return response()->json([
                'success' => true,
                'message' => 'Пользователи получены',
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('UserController index error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Просмотр конкретного пользователя
     */
    public function show($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Пользователь получен',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error("UserController show error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Создание пользователя
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRules::getRules('store_user'),
                ValidationRules::getMessages('store_user')
            );

            $validated['password'] = Hash::make($validated['password']);
            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно создан',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error("UserController store error: " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Обновление пользователя
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            // Заменяем {id} в правиле unique
            $rules = ValidationRules::getRules('update_user');
            $rules['email'] = str_replace('{id}', $id, $rules['email']);

            $validated = $request->validate(
                $rules,
                ValidationRules::getMessages('update_user')
            );

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно обновлен',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error("UserController update error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Удаление пользователя
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не найден'
                ], 404);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно удален'
            ], 200);
        } catch (\Exception $e) {
            Log::error("UserController destroy error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }
}
