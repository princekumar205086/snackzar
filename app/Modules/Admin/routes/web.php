<?php

use App\Modules\Admin\Controllers\AdminWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin/dashboard');

// Dashboard
Route::get('/dashboard', [AdminWebController::class, 'dashboard'])->name('dashboard');

// Users
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [AdminWebController::class, 'users'])->name('index');
    Route::get('/{id}', [AdminWebController::class, 'userShow'])->name('show');
});

// Orders
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/', [AdminWebController::class, 'orders'])->name('index');
    Route::get('/{id}', [AdminWebController::class, 'orderShow'])->name('show');
});

// Sellers
Route::get('/sellers', [AdminWebController::class, 'sellers'])->name('sellers.index');

// Delivery Partners
Route::get('/delivery-partners', [AdminWebController::class, 'deliveryPartners'])->name('delivery.index');

// Categories
Route::get('/categories', [AdminWebController::class, 'categories'])->name('categories.index');

// Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [AdminWebController::class, 'blog'])->name('index');
    Route::get('/create', [AdminWebController::class, 'blogCreate'])->name('create');
    Route::get('/{id}/edit', [AdminWebController::class, 'blogEdit'])->name('edit');
});
