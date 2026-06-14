<?php

use App\Http\Middleware\ApiRateLimit;
use App\Http\Middleware\CheckBlocked;
use App\Http\Middleware\LogRequest;
use App\Models\ErrorLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CheckBlocked::class,
            LogRequest::class,
        ]);

        $middleware->alias([
            'check.blocked' => CheckBlocked::class,
            'api.rate' => ApiRateLimit::class,
            'log.request' => LogRequest::class,
            'auth.api_token' => \App\Http\Middleware\ApiTokenAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->report(function (\Throwable $e) {
            if ($e instanceof ValidationException || $e->getCode() === 404) {
                return false;
            }
            try {
                ErrorLog::create([
                    'user_id' => auth()->id(),
                    'message' => substr($e->getMessage(), 0, 500),
                    'trace' => substr($e->getTraceAsString(), 0, 5000),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'created_at' => now(),
                ]);
            } catch (\Exception) {}
            return false;
        });
    })->create();
