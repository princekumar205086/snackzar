<?php

use App\Modules\User\Controllers\UserWebController;
use App\Modules\User\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);

    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [WebAuthController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [WebAuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('password.update');

    Route::get('/auth/google', [WebAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [WebAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::post('/auth/google/one-tap', [WebAuthController::class, 'handleGoogleOneTap'])->name('auth.google.one_tap');

    Route::get('/login/otp', [WebAuthController::class, 'showOtpLogin'])->name('login.otp');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    // User account pages
    Route::get('/dashboard', [UserWebController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [UserWebController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{id}', [UserWebController::class, 'orderShow'])->name('orders.show');
    Route::get('/profile', [UserWebController::class, 'profile'])->name('profile');
    Route::get('/wishlist', [UserWebController::class, 'wishlist'])->name('wishlist');
    Route::get('/addresses', [UserWebController::class, 'addresses'])->name('addresses');
    Route::get('/notifications', [UserWebController::class, 'notifications'])->name('notifications');

    // Payment
    Route::post('/payment/create-order', [UserWebController::class, 'createPaymentOrder'])->name('payment.create');
    Route::post('/payment/verify', [UserWebController::class, 'verifyPayment'])->name('payment.verify');
});

// Public pages (auth optional)
Route::get('/cart', [UserWebController::class, 'cart'])->name('cart');
Route::get('/checkout', [UserWebController::class, 'checkout'])->name('checkout');

