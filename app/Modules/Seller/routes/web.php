<?php

use App\Modules\Seller\Controllers\SellerWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/seller/dashboard');

Route::get('/dashboard', [SellerWebController::class, 'dashboard'])->name('dashboard');
Route::get('/profile', [SellerWebController::class, 'profile'])->name('profile');
Route::get('/payouts', [SellerWebController::class, 'payouts'])->name('payouts');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [SellerWebController::class, 'products'])->name('index');
    Route::get('/create', [SellerWebController::class, 'productCreate'])->name('create');
    Route::get('/{id}/edit', [SellerWebController::class, 'productEdit'])->name('edit');
});

Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [SellerWebController::class, 'orders'])->name('index');
    Route::get('/{id}', [SellerWebController::class, 'orderShow'])->name('show');
});
