<?php

namespace Bakirov\Protokit\Exceptions;

use Throwable;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as BaseExceptionHandler;

class Handler extends BaseExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        //
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $e)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, $e)
    {
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        switch (get_class($e)) {
            case AuthenticationException::class:
                return response()->json(['message' => 'Unauthenticated'], 401);

            case NotFoundHttpException::class:
            case ModelNotFoundException::class:
                return response()->json(['message' => 'Not found'], 404);

            case QueryException::class:
                if (app()->environment('production')) {
                    return response()->json(['message' => 'Invalid input data type'], 400);
                }

                break;

            case ValidationException::class:
                return $this->invalidJson($request, $e);

            case ThrottleRequestsException::class:
                /** @var ThrottleRequestsException $e */
                return response()->json(
                    [
                        'message' => $e->getMessage(),
                        'seconds_left' => Arr::get($e->getHeaders(), 'Retry-After', 0),
                    ],
                    $e->getStatusCode(),
                );
        }

        $defaultStatus = app()->environment('testing') ? 400 : 500;

        return response()->json(
            [
                'message' => $e->getMessage() ?: 'An error occurred',
            ],
            $defaultStatus,
        );
    }

    /**
     * @param Throwable $e
     * @return Response
     */
    public function handleExceptions($e)
    {
        if ($e instanceof ValidationException) {
            return redirect()->back()->withErrors($e->validator->getMessageBag()->toArray());
        }

        return response()->view('errors.500', [], 500);
    }
}
