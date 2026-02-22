<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public function render(Throwable $e): JsonResponse
    {
        // 422 Validation
        if ($e instanceof ValidationException) {
            return $this->error('Validation failed', 422, $e->errors());
        }

        // 404 Eloquent model not found
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return $this->error("{$model} not found", 404);
        }

        // 404 Route not found
        if ($e instanceof NotFoundHttpException) {
            return $this->error('The requested endpoint was not found', 404);
        }

        // 401 Unauthenticated
        if ($e instanceof AuthenticationException) {
            return $this->error($e->getMessage() ?: 'Unauthenticated. Please login.', 401);
        }

        // 403 Unauthorized
        if ($e instanceof AuthorizationException) {
            return $this->error($e->getMessage() ?: 'You are not authorized to perform this action.', 403);
        }

        // Other HTTP exceptions (e.g. 405 Method Not Allowed)
        if ($e instanceof HttpException) {
            return $this->error($e->getMessage() ?: 'HTTP Error', $e->getStatusCode());
        }

        // 500 Server Error
        return $this->error(
            config('app.debug') ? $e->getMessage() : 'An unexpected server error occurred.',
            500
        );
    }

    private function error(string $message, int $code, array $errors = []): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'code'    => $code,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }
}
