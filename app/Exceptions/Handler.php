<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            switch (true) {
                case $exception instanceof ValidationException:
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation errors',
                        'errors' => $exception->errors()
                    ], 422);
    
                case $exception instanceof ModelNotFoundException:
                    return response()->json([
                        'success' => false,
                        'message' => 'Resource Not Found'
                    ], 404);
    
                case $exception instanceof NotFoundHttpException:
                    return response()->json([
                        'success' => false,
                        'message' => 'Route Not Found',
                    ], 404);
    
                case $exception instanceof TokenMismatchException:
                case $exception instanceof HttpException && $exception->getStatusCode() === 419:
                    return response()->json([
                        'success' => false,
                        'message' => 'Session expired. Please refresh and try again.',
                    ], 419);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            // Return a JSON response when the request expects a JSON response
            return response()->json(['success' => false, 'message' => 'Unauthenticated Access'], 401);
        }

        // Default behavior if the request does not expect JSON (optional)
        return redirect()->route('login');
    }
}
