<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            '/uploads',
            '/uploads/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        function inertiaResponse(\Inertia\Response $responsable, $status): Response
        {
            $request = request();
            $response = $responsable->toResponse($request);
            $response->setStatusCode($status);
            return $response;
        }

        $exceptions->respond(function (Response $response) {
            if (
                !$response->headers->has('content-type') ||
                !str_starts_with($response->headers->get('content-type'), 'text/html')
            ) {
                return $response;
            }
            return match ($response->getStatusCode()) {
                404 => inertiaResponse(inertia('errors/404'), 404),
                500 => config('app.debug') ? $response : inertiaResponse(inertia('errors/500'), 500),
                default => $response,
            };
        });
    })->create();
