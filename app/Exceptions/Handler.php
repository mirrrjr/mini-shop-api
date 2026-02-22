<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

/**
 * Global exception handler.
 * API exceptions are handled via ApiExceptionRenderer (registered in bootstrap/app.php).
 * This class handles non-API (web) exceptions via the default Laravel handler.
 */
class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
