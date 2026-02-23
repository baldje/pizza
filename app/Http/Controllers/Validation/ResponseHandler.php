<?php
// app/Http/Handlers/ResponseHandler.php

namespace App\Http\Handlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ResponseHandler
{
    /**
     * Успешный ответ с данными
     */
    public static function success(
        string $message,
        mixed $data = null,
        int $statusCode = 200
    ): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Ответ об ошибке
     */
    public static function error(
        string $message,
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Ответ для созданного ресурса (201 Created)
     */
    public static function created(
        string $message,
        mixed $data = null
    ): JsonResponse
    {
        return self::success($message, $data, 201);
    }

    /**
     * Ответ для обновленного ресурса
     */
    public static function updated(
        string $message,
        mixed $data = null
    ): JsonResponse
    {
        return self::success($message, $data);
    }

    /**
     * Ответ для удаленного ресурса
     */
    public static function deleted(string $message): JsonResponse
    {
        return self::success($message);
    }

    /**
     * Ответ с пагинацией
     */
    public static function pagination(
        string $message,
        LengthAwarePaginator $paginator,
        array $additionalData = null
    ): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ];

        if ($additionalData !== null) {
            $response = array_merge($response, $additionalData);
        }

        return response()->json($response);
    }

    /**
     * Ответ без содержимого (204 No Content)
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Ответ для валидационных ошибок (422 Unprocessable Entity)
     */
    public static function validationError(
        string $message = 'Ошибка валидации',
        mixed $errors = null
    ): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Ответ "Не авторизован" (401 Unauthorized)
     */
    public static function unauthorized(
        string $message = 'Не авторизован'
    ): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Ответ "Запрещено" (403 Forbidden)
     */
    public static function forbidden(
        string $message = 'Доступ запрещен'
    ): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Ответ "Не найдено" (404 Not Found)
     */
    public static function notFound(
        string $message = 'Ресурс не найден'
    ): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Ответ "Внутренняя ошибка сервера" (500 Internal Server Error)
     */
    public static function serverError(
        string $message = 'Внутренняя ошибка сервера'
    ): JsonResponse
    {
        return self::error($message, null, 500);
    }
}
