<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForecastController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReorderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', EnsureAccountIsActive::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/products/{id}', [ProductController::class, 'show'])
        ->whereNumber('id')
        ->name('products.show');

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->name('inventory.index');

    Route::get('/inventory/transactions/{id}', [InventoryController::class, 'show'])
        ->whereNumber('id')
        ->name('inventory.show');

    Route::get('/inventory/{type}/create', [InventoryController::class, 'create'])
        ->whereIn('type', ['import', 'export'])
        ->name('inventory.create');

    Route::post('/inventory/{type}', [InventoryController::class, 'store'])
        ->whereIn('type', ['import', 'export'])
        ->name('inventory.store');

    Route::get('/dss', [ReorderController::class, 'index'])
        ->name('dss.index');

    Route::get('/dss/history', [ReorderController::class, 'history'])->name('dss.history');

    Route::post('/dss/generate', [ReorderController::class, 'generate'])
        ->name('dss.generate');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/bc01', [ReportController::class, 'alerts'])
        ->name('reports.bc01');

    Route::get('/reports/bc02', [ReportController::class, 'stockFlow'])
        ->name('reports.bc02');

    Route::get('/reports/bc03', [ReportController::class, 'reorder'])
        ->name('reports.bc03');

    Route::get('/reports/best-selling', [ReportController::class, 'bestSelling'])->name('reports.best-selling');

    Route::get('/reports/bc04', [ReportController::class, 'slowMoving'])
        ->name('reports.bc04');

    Route::get('/reports/bc05', [ForecastController::class, 'index'])
        ->name('reports.bc05');

    Route::get('/reports/olap', [ReportController::class, 'olap'])
        ->name('reports.olap');

    Route::post('/dss/recommendations/{id}/order', [ReorderController::class, 'confirmOrder'])
        ->whereNumber('id')
        ->name('dss.order');

    Route::middleware('role:admin')->group(function () {
        Route::post('/dss/recommendations/{id}/cancel', [ReorderController::class, 'cancel'])
            ->whereNumber('id')
            ->name('dss.cancel');
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
        Route::put('/inventory/transactions/{id}/purpose', [InventoryController::class, 'reviewPurpose'])->name('inventory.purpose');
        Route::post('/forecasts/run', [ForecastController::class, 'store'])->name('forecast.run');
        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('products.create');

        Route::post('/products/store', [ProductController::class, 'store'])
            ->name('products.store');

        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
            ->whereNumber('id')
            ->name('products.edit');

        Route::put('/products/{id}', [ProductController::class, 'update'])
            ->whereNumber('id')
            ->name('products.update');

        Route::put('/products/{id}/status', [ProductController::class, 'updateStatus'])
            ->whereNumber('id')
            ->name('products.status');

        Route::delete('/products/{id}', [ProductController::class, 'destroy'])
            ->whereNumber('id')
            ->name('products.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('categories.index');

        Route::get('/categories/create', [CategoryController::class, 'create'])
            ->name('categories.create');

        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('categories.store');

        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])
            ->whereNumber('id')
            ->name('categories.edit');

        Route::put('/categories/{id}', [CategoryController::class, 'update'])
            ->whereNumber('id')
            ->name('categories.update');

        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])
            ->whereNumber('id')
            ->name('categories.destroy');

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->whereNumber('id')
            ->name('users.edit');

        Route::put('/users/{id}', [UserController::class, 'update'])
            ->whereNumber('id')
            ->name('users.update');

        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->whereNumber('id')
            ->name('users.destroy');

        Route::put('/dss/settings/{productId}', [ReorderController::class, 'updateSettings'])
            ->whereNumber('productId')
            ->name('dss.settings');

        Route::post('/dss/products/{id}/sync-lead-time', [ReorderController::class, 'syncLeadTime'])
            ->whereNumber('id')
            ->name('dss.sync-lead-time');

        Route::post('/dss/recommendations/{id}/decision', [ReorderController::class, 'decide'])
            ->whereNumber('id')
            ->name('dss.decide');
    });
});
