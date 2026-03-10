<?php

use App\Modules\User\Controllers\AddressController;
use App\Modules\User\Controllers\AuthController;
use App\Modules\User\Controllers\CartController;
use App\Modules\User\Controllers\CategoryController;
use App\Modules\User\Controllers\ProductController;
use App\Modules\User\Controllers\ProfileController;
use App\Modules\User\Controllers\ReviewController;
use App\Modules\User\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/otp/send', [AuthController::class, 'sendOtp'])->name('otp.send');
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
});

// Public catalog routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/featured', [ProductController::class, 'featured'])->name('products.featured');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{slug}/related', [ProductController::class, 'related'])->name('products.related');

// Public reviews
Route::get('/products/{product}/reviews', [ReviewController::class, 'index'])->name('reviews.index');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/auth/user', [AuthController::class, 'user'])->name('user');
    Route::post('/auth/email/verify', [AuthController::class, 'verifyEmail'])->name('email.verify');
    Route::post('/auth/email/resend', [AuthController::class, 'resendVerificationEmail'])->name('email.resend');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'deleteAccount'])->name('profile.delete');

    // Addresses
    Route::apiResource('addresses', AddressController::class)->names([
        'index' => 'addresses.index',
        'store' => 'addresses.store',
        'show' => 'addresses.show',
        'update' => 'addresses.update',
        'destroy' => 'addresses.destroy',
    ]);
    Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Reviews
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});
