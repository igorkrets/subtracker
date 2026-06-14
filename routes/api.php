<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Middleware\ApiRateLimit;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.api_token', ApiRateLimit::class])->prefix('v1')->group(function () {
    Route::get('/me', [ApiController::class, 'me']);
    Route::get('/services', [ApiController::class, 'services']);
    Route::post('/services', [ApiController::class, 'storeService']);
    Route::get('/services/expiring', [ApiController::class, 'expiringServices']);
    Route::get('/services/{id}', [ApiController::class, 'showService']);
    Route::put('/services/{id}', [ApiController::class, 'updateService']);
    Route::delete('/services/{id}', [ApiController::class, 'destroyService']);
    Route::get('/groups', [ApiController::class, 'groups']);
    Route::post('/groups', [ApiController::class, 'storeGroup']);
    Route::put('/groups/{id}', [ApiController::class, 'updateGroup']);
    Route::delete('/groups/{id}', [ApiController::class, 'destroyGroup']);
});
