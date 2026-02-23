<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Проверяем авторизацию
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Не авторизован'
            ], 401);
        }

        // Проверяем права администратора
        if (!auth()->user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ запрещен'
            ], 403);
        }

        return $next($request);
    }
}
