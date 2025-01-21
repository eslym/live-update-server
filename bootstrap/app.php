<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            HandleInertiaRequests::class,
        ]);
        $middleware->statefulApi();
        $middleware->validateCsrfTokens(except: [
            '/uploads',
            '/uploads/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $exception) {
            $request = request();
            if ($request->wantsJson()) return null;
            if ($exception instanceof HttpException) {
                switch ($exception->getStatusCode()) {
                    case 404:
                        return inertia('errors/404');
                    case 500:
                        return inertia('errors/500');
                }
            }
            return config('app.debug') ? null : inertia('errors/500');
        });
    })->create();
