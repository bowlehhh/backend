<?php

use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\CashierTransactionController;
use App\Http\Controllers\Admin\AdminDashboardProductController;
use App\Http\Controllers\Admin\AdminSalesController;
use App\Http\Controllers\Admin\AdminTaxonomyController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebLoginController::class, 'create'])->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [WebLoginController::class, 'create'])->name('login');
    Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->post('/logout', [WebLoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin/dashboard')->group(function (): void {
    Route::post('/products', [AdminDashboardProductController::class, 'store'])
        ->name('admin.dashboard.products.store');
    Route::put('/products/{product}', [AdminDashboardProductController::class, 'update'])
        ->name('admin.dashboard.products.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (): void {
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/sales/{sale}/receipt', [AdminSalesController::class, 'receipt'])->name('admin.sales.receipt');
    Route::post('/taxonomy', [AdminTaxonomyController::class, 'store'])->name('admin.taxonomy.store');
    Route::put('/taxonomy', [AdminTaxonomyController::class, 'update'])->name('admin.taxonomy.update');
    Route::delete('/taxonomy', [AdminTaxonomyController::class, 'destroy'])->name('admin.taxonomy.destroy');
});

Route::middleware(['auth', 'role:cashier'])
    ->get('/cashier/dashboard', CashierDashboardController::class)
    ->name('cashier.dashboard');

Route::middleware(['auth', 'role:cashier'])
    ->prefix('cashier')
    ->group(function (): void {
        // History & draft transaksi kasir.
        Route::get('/history', [CashierTransactionController::class, 'history'])->name('cashier.history');
        Route::get('/drafts', [CashierTransactionController::class, 'drafts'])->name('cashier.drafts');
        Route::get('/history/{sale}/receipt', [CashierTransactionController::class, 'receipt'])->name('cashier.receipt');

        // Operasi keranjang.
        Route::post('/cart/add/{product}', [CashierTransactionController::class, 'add'])->name('cashier.cart.add');
        Route::post('/cart/update/{batch}', [CashierTransactionController::class, 'update'])->name('cashier.cart.update');
        Route::post('/cart/remove/{batch}', [CashierTransactionController::class, 'remove'])->name('cashier.cart.remove');
        Route::post('/cart/clear', [CashierTransactionController::class, 'clear'])->name('cashier.cart.clear');
        Route::post('/cart/hold', [CashierTransactionController::class, 'hold'])->name('cashier.cart.hold');

        // Operasi draft dan checkout final.
        Route::post('/drafts/{draft}/resume', [CashierTransactionController::class, 'resume'])->name('cashier.drafts.resume');
        Route::post('/drafts/{draft}/delete', [CashierTransactionController::class, 'destroyDraft'])->name('cashier.drafts.delete');
        Route::post('/checkout', [CashierTransactionController::class, 'checkout'])->name('cashier.checkout');
    });
