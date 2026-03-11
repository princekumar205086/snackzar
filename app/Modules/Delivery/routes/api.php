<?php

use App\Modules\Delivery\Controllers\DeliveryAssignmentController;
use App\Modules\Delivery\Controllers\DeliveryDashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard & Profile
Route::get('/dashboard', [DeliveryDashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/profile', [DeliveryDashboardController::class, 'profile'])->name('profile.show');
Route::post('/profile', [DeliveryDashboardController::class, 'createProfile'])->name('profile.store');
Route::put('/profile', [DeliveryDashboardController::class, 'updateProfile'])->name('profile.update');
Route::patch('/availability', [DeliveryDashboardController::class, 'toggleAvailability'])->name('availability.toggle');
Route::patch('/location', [DeliveryDashboardController::class, 'updateLocation'])->name('location.update');

// Assignments
Route::get('/assignments', [DeliveryAssignmentController::class, 'index'])->name('assignments.index');
Route::get('/assignments/{assignment}', [DeliveryAssignmentController::class, 'show'])->name('assignments.show');
Route::patch('/assignments/{assignment}/accept', [DeliveryAssignmentController::class, 'accept'])->name('assignments.accept');
Route::patch('/assignments/{assignment}/pickup', [DeliveryAssignmentController::class, 'pickUp'])->name('assignments.pickup');
Route::patch('/assignments/{assignment}/deliver', [DeliveryAssignmentController::class, 'deliver'])->name('assignments.deliver');
