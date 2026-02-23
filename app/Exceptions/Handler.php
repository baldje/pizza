<?php

namespace App\Http\Controllers\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Http\Controllers\Validation\ExceptionHandler as ValidationExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            $validationHandler = new ValidationExceptionHandler(app());
            return $validationHandler->render($request, $exception);
        }

        return parent::render($request, $exception);
    }
}
