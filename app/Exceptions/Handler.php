<?php

namespace App\Exceptions;

use App\Dto\ApiErrorResponseDto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

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

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() && $e->getPrevious() instanceof ModelNotFoundException) {

                $error = new ApiErrorResponseDto(
                    $e->getPrevious()->getModel().' not found by id ' . implode(',', $e->getPrevious()->getIds())
                );

                return response()->json((array)$error, Response::HTTP_NOT_FOUND);
            }
        });
    }
}
