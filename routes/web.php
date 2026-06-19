<?php

use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\CashierTransactionController;
use App\Http\Controllers\Admin\AdminDashboardProductController;
use App\Http\Controllers\Admin\AdminBesarDashboardController;
use App\Http\Controllers\Admin\AdminBesarTransactionController;
use App\Http\Controllers\Admin\AdminCreditController;
use App\Http\Controllers\Admin\AdminSalesController;
use App\Http\Controllers\Admin\AdminSupplierInvoiceRecapController;
use App\Http\Controllers\Admin\ProductGroupCsvExportController;
use App\Http\Controllers\Admin\ProductGroupXlsxExportController;
use App\Http\Controllers\Admin\AdminCreditXlsxExportController;
use App\Http\Controllers\Admin\ProductGroupController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user?->isAdminBesar()) {
            return redirect('/admin/admin-besar');
        }

        return redirect('/admin/admin-dashboard');
    }

    return redirect('/login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [WebLoginController::class, 'create'])
        ->name('filament.admin.auth.login');
    Route::get('/login', [WebLoginController::class, 'create'])->name('login');
    Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->post('/logout', [WebLoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'role:admin,admin_besar'])->prefix('admin/dashboard')->group(function (): void {
    Route::get('/products/suggestions', [AdminDashboardProductController::class, 'suggestions'])
        ->name('admin.dashboard.products.suggestions');
    Route::post('/products', [AdminDashboardProductController::class, 'store'])
        ->name('admin.dashboard.products.store');
    Route::put('/products/{product}', [AdminDashboardProductController::class, 'update'])
        ->name('admin.dashboard.products.update');
});

Route::middleware(['auth', 'role:admin,admin_besar'])->prefix('admin')->group(function (): void {
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/sales/{sale}/receipt', [AdminSalesController::class, 'receipt'])->name('admin.sales.receipt');
    Route::get('/product-groups/export', ProductGroupXlsxExportController::class)->name('admin.product-groups.export');
    Route::get('/product-groups/export-csv', ProductGroupCsvExportController::class)->name('admin.product-groups.export.csv');
    Route::get('/credits/export-xlsx', AdminCreditXlsxExportController::class)->name('admin.credits.export.xlsx');
    Route::get('/product-groups/{product}/detail', [ProductGroupController::class, 'show'])->name('admin.product-groups.show');
    Route::get('/product-groups/{product}/receipt', [ProductGroupController::class, 'receipt'])->name('admin.product-groups.receipt');
    Route::get('/credits/{batch}/detail', [AdminCreditController::class, 'detail'])->name('admin.credits.detail');
    Route::post('/credits/{batch}/installment', [AdminCreditController::class, 'payInstallment'])->name('admin.credits.installment');
    Route::post('/credits/{batch}/settle', [AdminCreditController::class, 'settle'])->name('admin.credits.settle');
    Route::get('/credits/{batch}/receipt', [AdminCreditController::class, 'receipt'])->name('admin.credits.receipt');
    Route::get('/credits/{batch}/installments/{installment}/receipt', [AdminCreditController::class, 'installmentReceipt'])->name('admin.credits.installment.receipt');
    Route::get('/suppliers/{supplier}/invoice-recap', [AdminSupplierInvoiceRecapController::class, 'show'])->name('admin.suppliers.invoice-recap');
});

Route::middleware(['auth', 'role:admin,admin_besar'])
    ->get('/admin/transaksi', CashierDashboardController::class)
    ->name('admin.transaksi.dashboard');

Route::middleware(['auth', 'role:admin,admin_besar'])
    ->prefix('admin/transaksi')
    ->group(function (): void {
        Route::get('/history', [CashierTransactionController::class, 'history'])->name('admin.transactions.history');
        Route::get('/history-supplier', [CashierTransactionController::class, 'historyBySupplier'])->name('admin.transactions.history.supplier');
        Route::get('/history-supplier/detail', [CashierTransactionController::class, 'historyBySupplierDetail'])->name('admin.transactions.history.supplier.detail');
        Route::get('/drafts', [CashierTransactionController::class, 'drafts'])->name('admin.transactions.drafts');
        Route::get('/history/{sale}/receipt', [CashierTransactionController::class, 'receipt'])->name('admin.transactions.receipt');
        Route::get('/history/{sale}/installment', [CashierTransactionController::class, 'installmentForm'])->name('admin.transactions.history.installment.form');
        Route::post('/history/{sale}/installment', [CashierTransactionController::class, 'storeInstallment'])->name('admin.transactions.history.installment.store');
        Route::get('/history/{sale}/installments/{installment}/receipt', [CashierTransactionController::class, 'installmentReceipt'])->name('admin.transactions.history.installment.receipt');
        Route::get('/history/{sale}/edit', [CashierTransactionController::class, 'editHistory'])->name('admin.transactions.history.edit');
        Route::put('/history/{sale}/edit', [CashierTransactionController::class, 'updateHistory'])->name('admin.transactions.history.update');
        Route::delete('/history/{sale}/delete', [CashierTransactionController::class, 'destroyHistory'])->name('admin.transactions.history.destroy');
        Route::get('/returns/replacement-options', [CashierTransactionController::class, 'replacementOptions'])->name('admin.transactions.return.options');
        Route::get('/history/{sale}/return', [CashierTransactionController::class, 'returnForm'])->name('admin.transactions.return.form');
        Route::post('/history/{sale}/return', [CashierTransactionController::class, 'storeReturn'])->name('admin.transactions.return.store');
        Route::get('/returns/{salesReturn}/receipt', [CashierTransactionController::class, 'returnReceipt'])->name('admin.transactions.return.receipt');

        Route::post('/cart/add/{batch}', [CashierTransactionController::class, 'add'])->name('admin.transactions.cart.add');
        Route::post('/cart/update/{batch}', [CashierTransactionController::class, 'update'])->name('admin.transactions.cart.update');
        Route::post('/cart/remove/{batch}', [CashierTransactionController::class, 'remove'])->name('admin.transactions.cart.remove');
        Route::post('/cart/merge/{batch}', [CashierTransactionController::class, 'toggleMergeStock'])->name('admin.transactions.cart.merge');
        Route::post('/cart/clear', [CashierTransactionController::class, 'clear'])->name('admin.transactions.cart.clear');
        Route::post('/cart/hold', [CashierTransactionController::class, 'hold'])->name('admin.transactions.cart.hold');

        Route::post('/drafts/{draft}/resume', [CashierTransactionController::class, 'resume'])->name('admin.transactions.drafts.resume');
        Route::post('/drafts/{draft}/delete', [CashierTransactionController::class, 'destroyDraft'])->name('admin.transactions.drafts.delete');
        Route::post('/checkout', [CashierTransactionController::class, 'checkout'])->name('admin.transactions.checkout');
    });

Route::middleware(['auth', 'role:admin_besar'])
    ->prefix('admin/admin-besar')
    ->group(function (): void {
        Route::get('/', AdminBesarDashboardController::class)->name('admin.admin-besar.index');
        Route::get('/history', [AdminBesarTransactionController::class, 'history'])->name('admin.admin-besar.history');
        Route::get('/history-supplier', [AdminBesarTransactionController::class, 'historyBySupplier'])->name('admin.admin-besar.history.supplier');
        Route::get('/history-supplier/detail', [AdminBesarTransactionController::class, 'historyBySupplierDetail'])->name('admin.admin-besar.history.supplier.detail');
        Route::get('/history/{sale}/receipt', [CashierTransactionController::class, 'receipt'])->name('admin.admin-besar.receipt');
        Route::get('/history/{sale}/installment', [CashierTransactionController::class, 'installmentForm'])->name('admin.admin-besar.history.installment.form');
        Route::post('/history/{sale}/installment', [CashierTransactionController::class, 'storeInstallment'])->name('admin.admin-besar.history.installment.store');
        Route::get('/history/{sale}/installments/{installment}/receipt', [CashierTransactionController::class, 'installmentReceipt'])->name('admin.admin-besar.history.installment.receipt');
    });

Route::middleware(['auth', 'role:admin,admin_besar'])
    ->prefix('cashier')
    ->group(function (): void {
        Route::get('/dashboard', CashierDashboardController::class)->name('cashier.dashboard');
        Route::get('/history', [CashierTransactionController::class, 'history'])->name('cashier.history');
        Route::get('/history-supplier', [CashierTransactionController::class, 'historyBySupplier'])->name('cashier.history.supplier');
        Route::get('/history-supplier/detail', [CashierTransactionController::class, 'historyBySupplierDetail'])->name('cashier.history.supplier.detail');
        Route::get('/drafts', [CashierTransactionController::class, 'drafts'])->name('cashier.drafts');
        Route::get('/history/{sale}/receipt', [CashierTransactionController::class, 'receipt'])->name('cashier.receipt');
        Route::get('/history/{sale}/installment', [CashierTransactionController::class, 'installmentForm'])->name('cashier.history.installment.form');
        Route::post('/history/{sale}/installment', [CashierTransactionController::class, 'storeInstallment'])->name('cashier.history.installment.store');
        Route::get('/history/{sale}/installments/{installment}/receipt', [CashierTransactionController::class, 'installmentReceipt'])->name('cashier.history.installment.receipt');
        Route::get('/history/{sale}/edit', [CashierTransactionController::class, 'editHistory'])->name('cashier.history.edit');
        Route::put('/history/{sale}/edit', [CashierTransactionController::class, 'updateHistory'])->name('cashier.history.update');
        Route::delete('/history/{sale}/delete', [CashierTransactionController::class, 'destroyHistory'])->name('cashier.history.destroy');
        Route::get('/returns/replacement-options', [CashierTransactionController::class, 'replacementOptions'])->name('cashier.return.options');
        Route::get('/history/{sale}/return', [CashierTransactionController::class, 'returnForm'])->name('cashier.return.form');
        Route::post('/history/{sale}/return', [CashierTransactionController::class, 'storeReturn'])->name('cashier.return.store');
        Route::get('/returns/{salesReturn}/receipt', [CashierTransactionController::class, 'returnReceipt'])->name('cashier.return.receipt');
        Route::post('/cart/add/{batch}', [CashierTransactionController::class, 'add'])->name('cashier.cart.add');
        Route::post('/cart/update/{batch}', [CashierTransactionController::class, 'update'])->name('cashier.cart.update');
        Route::post('/cart/remove/{batch}', [CashierTransactionController::class, 'remove'])->name('cashier.cart.remove');
        Route::post('/cart/merge/{batch}', [CashierTransactionController::class, 'toggleMergeStock'])->name('cashier.cart.merge');
        Route::post('/cart/clear', [CashierTransactionController::class, 'clear'])->name('cashier.cart.clear');
        Route::post('/cart/hold', [CashierTransactionController::class, 'hold'])->name('cashier.cart.hold');
        Route::post('/drafts/{draft}/resume', [CashierTransactionController::class, 'resume'])->name('cashier.drafts.resume');
        Route::post('/drafts/{draft}/delete', [CashierTransactionController::class, 'destroyDraft'])->name('cashier.drafts.delete');
        Route::post('/checkout', [CashierTransactionController::class, 'checkout'])->name('cashier.checkout');
    });
