<?php

use Illuminate\Support\Facades\Route;
use Rajibbinalam\BagistoCourier\Http\Controllers\Admin\CourierBalanceController;
use Rajibbinalam\BagistoCourier\Http\Controllers\Admin\CourierOrderController;
use Rajibbinalam\BagistoCourier\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Admin routes — behind Bagisto's own admin auth + ACL middleware
|--------------------------------------------------------------------------
*/
Route::group([
    'middleware' => ['web', 'admin'],
    'prefix'     => config('app.admin_url'),
], function () {

    Route::prefix('orders')->name('admin.courier.')->group(function () {
        Route::get('{orderId}/courier/create', [CourierOrderController::class, 'create'])->name('create.form');
        Route::post('{orderId}/courier/create', [CourierOrderController::class, 'store'])->name('create');
        Route::post('{orderId}/courier/sync', [CourierOrderController::class, 'sync'])->name('sync');
        Route::post('{orderId}/status', [CourierOrderController::class, 'updateOrderStatus'])->name('status.update');
    });

    Route::prefix('courier/balance')->name('admin.courier.balance.')->group(function () {
        Route::get('', [CourierBalanceController::class, 'index'])->name('index');
        Route::post('{code}', [CourierBalanceController::class, 'check'])->name('check');
        Route::post('{code}/test', [CourierBalanceController::class, 'test'])->name('test');
    });
});

/*
|--------------------------------------------------------------------------
| Public webhook routes — called by courier servers directly, NOT browsers.
| Intentionally outside the "web" group: no CSRF token/session required.
| Each handler verifies the request itself (see WebhookController).
|--------------------------------------------------------------------------
*/
Route::post('courier/webhook/steadfast', [WebhookController::class, 'steadfast'])->name('courier.webhook.steadfast');
Route::post('courier/webhook/pathao', [WebhookController::class, 'pathao'])->name('courier.webhook.pathao');
