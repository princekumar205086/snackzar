<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Email verification route (opens in browser from email link)
Route::get('/email/verify/{id}/{hash}', function () {
    return Inertia::render('Auth/VerifyEmail');
})->middleware(['signed'])->name('verification.verify');

// Password reset route (opens in browser from email link)
Route::get('/reset-password/{token}', function () {
    return Inertia::render('Auth/ResetPassword');
})->name('password.reset');
