<?php

use App\Modules\Admin\Controllers\AdminBlogController;
use App\Modules\Admin\Controllers\AdminCategoryController;
use App\Modules\Admin\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AdminOrderController;
use App\Modules\Admin\Controllers\AdminSellerController;
use App\Modules\Admin\Controllers\AdminUserController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

// Users
Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');

// Orders
Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

// Sellers
Route::get('/sellers', [AdminSellerController::class, 'sellers'])->name('sellers.index');
Route::patch('/sellers/{profile}/approve', [AdminSellerController::class, 'approveSeller'])->name('sellers.approve');
Route::patch('/sellers/{profile}/suspend', [AdminSellerController::class, 'suspendSeller'])->name('sellers.suspend');

// Delivery Partners
Route::get('/delivery-partners', [AdminSellerController::class, 'deliveryPartners'])->name('delivery.index');
Route::patch('/delivery-partners/{profile}/approve', [AdminSellerController::class, 'approveDeliveryPartner'])->name('delivery.approve');
Route::post('/delivery/assign', [AdminSellerController::class, 'assignDelivery'])->name('delivery.assign');

// Categories
Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

// Blog
Route::get('/blog', [AdminBlogController::class, 'index'])->name('blog.index');
Route::post('/blog', [AdminBlogController::class, 'store'])->name('blog.store');
Route::get('/blog/{post}', [AdminBlogController::class, 'show'])->name('blog.show');
Route::put('/blog/{post}', [AdminBlogController::class, 'update'])->name('blog.update');
Route::delete('/blog/{post}', [AdminBlogController::class, 'destroy'])->name('blog.destroy');
