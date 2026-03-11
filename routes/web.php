<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [HomeController::class, 'products'])->name('products.index');
Route::get('/products/{slug}', [HomeController::class, 'productShow'])->name('products.show');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Email verification route (opens in browser from email link)
Route::get('/email/verify/{id}/{hash}', function () {
    return Inertia::render('Auth/VerifyEmail');
})->middleware(['signed'])->name('verification.verify');

// Password reset route (opens in browser from email link)
Route::get('/reset-password/{token}', function () {
    return Inertia::render('Auth/ResetPassword');
})->name('password.reset');
