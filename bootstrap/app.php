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
        $middleware->validateCsrfTokens(except: [
            'api/telegram/webhook',
        ]);

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
            fn (Request $request) => $request->is('api/v1/*') || $request->wantsJson(),
        );

        // Clean JSON errors for API — never expose stack traces or file paths
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!($request->is('api/v1/*') || $request->wantsJson())) {
                return null; // let default handler render HTML for web routes
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Ошибка валидации',
                    'errors'  => $e->errors(),
                ], 422);
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            $message = match ($status) {
                400 => 'Неверный запрос',
                401 => 'Требуется авторизация',
                403 => 'Доступ запрещён',
                404 => 'Не найдено',
                405 => 'Метод не разрешён',
                422 => 'Ошибка валидации',
                429 => 'Слишком много запросов',
                default => 'Внутренняя ошибка сервера',
            };

            return response()->json(['message' => $message], $status);
        });

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
