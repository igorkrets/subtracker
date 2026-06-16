<?php

use App\Http\Controllers\Admin\AdminCatalogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceRenewController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Landing
Route::get('/', function () {
    $presets = \App\Models\CatalogPreset::where('is_active', true)->orderBy('sort_order')->get();
    $stats = \Illuminate\Support\Facades\Cache::remember('landing_stats', 6 * 3600, function () {
        return [
            'users'    => \App\Models\User::count(),
            'services' => \App\Models\Service::count(),
        ];
    });
    return view('landing.index', compact('presets', 'stats'));
})->name('home');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

// Legal pages
Route::get('/privacy', fn() => view('legal.privacy'))->name('privacy');
Route::get('/terms', fn() => view('legal.terms'))->name('terms');

// Sitemap
Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => route('login'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => route('register'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ['loc' => route('privacy'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['loc' => route('terms'), 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['loc' => route('api.docs'), 'changefreq' => 'monthly', 'priority' => '0.6'],
    ];
    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/dashboard/currency', [DashboardController::class, 'updateCurrency'])->name('dashboard.currency');

    // Settings
    Route::get('/dashboard/settings', [SettingsController::class, 'index'])->name('settings');
    Route::put('/dashboard/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/dashboard/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/dashboard/settings/api-token', [SettingsController::class, 'regenerateToken'])->name('settings.token');
    Route::post('/dashboard/settings/tg-unlink', [SettingsController::class, 'unlinkTelegram'])->name('settings.tg.unlink');

    // Groups
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/sort', [GroupController::class, 'sort'])->name('groups.sort');
    Route::patch('/groups/{group}/toggle-notifications', [GroupController::class, 'toggleNotifications'])->name('groups.toggle-notifications');

    // Services
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::post('/services/sort', [ServiceController::class, 'sort'])->name('services.sort');
    Route::patch('/services/{service}/expiry', [ServiceController::class, 'updateExpiry'])->name('services.expiry');
    Route::post('/services/{service}/renew', [ServiceRenewController::class, 'renew'])->name('services.renew');
    Route::post('/services/{service}/duplicate', [ServiceController::class, 'duplicate'])->name('services.duplicate');
    Route::patch('/services/{service}/toggle-notifications', [ServiceController::class, 'toggleNotifications'])->name('services.toggle-notifications');

    // Export & Backup
    Route::get('/services/export', [ExportController::class, 'export'])->name('export');
    Route::get('/services/backup', [BackupController::class, 'download'])->name('backup.download');
    Route::post('/services/import', [BackupController::class, 'import'])->name('backup.import');

    // Catalog
    Route::get('/catalog.json', [CatalogController::class, 'json'])->name('catalog.json');
    Route::get('/catalog/search', [CatalogController::class, 'search'])->name('catalog.search');

    // Webhooks
    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::put('/webhooks/{webhook}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');

    // Notification rules
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    Route::delete('/notifications/{rule}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // API docs
});

// Public API docs (no auth required)
Route::get('/api/docs', fn() => view('api-docs'))->name('api.docs');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users');
    Route::post('/users/{user}/block', [AdminUserController::class, 'block'])->name('users.block');
    Route::post('/users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('users.unblock');
    Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
    Route::patch('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::get('/logs', [AdminLogController::class, 'index'])->name('logs');
    Route::get('/catalog', [AdminCatalogController::class, 'index'])->name('catalog');
    Route::post('/catalog/types', [AdminCatalogController::class, 'storeType'])->name('catalog.types.store');
    Route::put('/catalog/types/{type}', [AdminCatalogController::class, 'updateType'])->name('catalog.types.update');
    Route::delete('/catalog/types/{type}', [AdminCatalogController::class, 'destroyType'])->name('catalog.types.destroy');
    Route::post('/catalog/presets', [AdminCatalogController::class, 'storePreset'])->name('catalog.presets.store');
    Route::put('/catalog/presets/{preset}', [AdminCatalogController::class, 'updatePreset'])->name('catalog.presets.update');
    Route::delete('/catalog/presets/{preset}', [AdminCatalogController::class, 'destroyPreset'])->name('catalog.presets.destroy');
    Route::post('/catalog/build', [AdminCatalogController::class, 'build'])->name('catalog.build');
    Route::post('/catalog/rebuild', [AdminCatalogController::class, 'build'])->name('catalog.rebuild');
});

// Telegram webhook (protected by secret header, no CSRF)
Route::post('/api/telegram/webhook', [TelegramBotController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
