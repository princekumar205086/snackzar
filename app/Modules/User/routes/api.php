<?php

use App\Modules\User\Controllers\AddressController;
use App\Modules\User\Controllers\AuthController;
use App\Modules\User\Controllers\CartController;
use App\Modules\User\Controllers\CategoryController;
use App\Modules\User\Controllers\CouponController;
use App\Modules\User\Controllers\MediaController;
use App\Modules\User\Controllers\NotificationController;
use App\Modules\User\Controllers\OrderController;
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

    Route::middleware('prevent_admin_customer')->group(function () {
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
        Route::get('/pincode/{pincode}', [AddressController::class, 'lookupPincode'])->name('addresses.pincode.lookup');

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

        // Coupon validation (public — or auth, either is fine)
        Route::post('/coupon/validate', [CouponController::class, 'validate'])->name('coupon.validate');
        Route::get('/coupon/my-coupons', [CouponController::class, 'myCoupons'])->name('coupon.mine');

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

        // Media
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
    });
});
