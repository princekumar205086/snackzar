<?php

use App\Modules\Seller\Controllers\SellerDashboardController;
use App\Modules\Seller\Controllers\SellerOrderController;
use App\Modules\Seller\Controllers\SellerProductController;
use Illuminate\Support\Facades\Route;

// Dashboard & Profile
Route::get('/dashboard', [SellerDashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/profile', [SellerDashboardController::class, 'profile'])->name('profile.show');
Route::post('/profile', [SellerDashboardController::class, 'createProfile'])->name('profile.store');
Route::put('/profile', [SellerDashboardController::class, 'updateProfile'])->name('profile.update');

// Payouts
Route::get('/payouts', [SellerDashboardController::class, 'payouts'])->name('payouts.index');
Route::post('/payouts', [SellerDashboardController::class, 'requestPayout'])->name('payouts.store');

// Products
Route::apiResource('products', SellerProductController::class)->names([
    'index' => 'products.index',
    'store' => 'products.store',
    'show' => 'products.show',
    'update' => 'products.update',
    'destroy' => 'products.destroy',
]);
Route::patch('/products/{product}/toggle-active', [SellerProductController::class, 'toggleActive'])->name('products.toggle');

// Orders
Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{orderItem}', [SellerOrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{orderItem}/status', [SellerOrderController::class, 'updateStatus'])->name('orders.status');
