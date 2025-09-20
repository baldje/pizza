<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRules;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public $model;

    public function __construct(User $user) {
        $this->model = $user;
    }

    /**
     * Регистрация нового пользователя
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRules::getRules('register'),
                ValidationRules::getMessages('register')
            );

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ];

            $user = User::create($userData);
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Аутентификация пользователя
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRules::getRules('login'),
                ValidationRules::getMessages('login')
            );

            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный email или пароль'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Успешный вход в систему',
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Получение данных текущего пользователя
     */
    public function me()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь не аутентифицирован'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Данные пользователя получены',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            Log::error('Me error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Обновление JWT токена
     */
    public function refreshToken(Request $request)
    {
        try {
            $newToken = auth()->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Токен успешно обновлен',
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60,
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Недействительный токен'
            ], 401);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении токена'
            ], 401);

        } catch (\Exception $e) {
            Log::error('Refresh token error: ' . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Выход пользователя из системы
     */
    public function logout()
    {
        try {
            auth()->logout();

            return response()->json([
                'success' => true,
                'message' => 'Вы успешно вышли из системы'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }
}
