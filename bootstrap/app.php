<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/telegram/*',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\adminMiddleware::class,
            'operator' => \App\Http\Middleware\OperatorMidlleware::class,
            'it_support' => \App\Http\Middleware\ItSupportMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            try {
                app(\App\Services\TelegramNotifierService::class)->report($e);
            } catch (\Throwable $ignored) {}
        });
    })->create();
