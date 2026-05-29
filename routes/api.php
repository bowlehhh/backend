<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashierDashboardController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductSearchController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Product API
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    Route::prefix('pos')
        ->middleware('role:cashier')
        ->group(function (): void {
            Route::get('/products/search', [ProductSearchController::class, 'index']);
            Route::get('/dashboard', [CashierDashboardController::class, 'show']);
            Route::get('/transactions/today', [TransactionController::class, 'today']);
            Route::post('/checkout', [CheckoutController::class, 'store']);
            Route::get('/invoices/{sale}', [TransactionController::class, 'invoice']);
        });
});
