<?php

use App\Http\Middleware\HandleInertiaRequests;
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
            if (in_array($response->getStatusCode(), [400, 404, 405, 419, 429, 503])) {
                return inertiaResponse(inertia('errors', [
                    'status' => $response->getStatusCode(),
                ]), $response->getStatusCode());
            }
            if ($response->getStatusCode() === 500 && !config('app.debug')) {
                return inertiaResponse(inertia('errors', [
                    'status' => 500,
                ]), 500);
            }
            return $response;
        });
    })->create();
