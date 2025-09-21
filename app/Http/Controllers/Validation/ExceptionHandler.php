<?php

namespace App\Http\Controllers\Validation;

use Illuminate\Foundation\Exceptions\Handler as BaseExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExceptionHandler extends BaseExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Логгирование, Sentry и т.п.
        });
    }

    /**
     * Глобальная обработка исключений
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Неавторизован',
            ], 401);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Ресурс не найден',
            ], 404);
        }

        if ($exception instanceof HttpException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'HTTP ошибка',
            ], $exception->getStatusCode());
        }

        return response()->json([
            'success' => false,
            'message' => 'Внутренняя ошибка сервера',
            'error' => config('app.debug') ? $exception->getMessage() : null,
        ], 500);
    }

    /**
     * Статический метод для обработки исключений из контроллеров
     */
    public static function handle(Request $request, Throwable $exception): JsonResponse
    {
        $handler = new self(app());
        return $handler->render($request, $exception);
    }
}
