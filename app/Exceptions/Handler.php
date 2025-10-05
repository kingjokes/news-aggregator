<?php

namespace App\Exceptions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Custom exception handler for the application.
 */
class Handler extends ExceptionHandler
{
    /**
     * Fields excluded from flash session (e.g., passwords).
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registers exception handling callbacks.
     */
    public function register(): void
    {
        // Custom reporting (extend as needed)
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle 404 for API routes
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found'
                ], 404);
            }
        });

        // Handle model not found for API routes
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found'
                ], 404);
            }
        });

        // Handle validation errors for API routes
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
        });
    }
}