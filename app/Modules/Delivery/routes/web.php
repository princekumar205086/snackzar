<?php

use App\Modules\Delivery\Controllers\DeliveryWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/delivery/dashboard');

Route::get('/dashboard', [DeliveryWebController::class, 'dashboard'])->name('dashboard');
Route::get('/profile', [DeliveryWebController::class, 'profile'])->name('profile');

Route::prefix('assignments')->name('assignments.')->group(function () {
    Route::get('/', [DeliveryWebController::class, 'assignments'])->name('index');
    Route::get('/{id}', [DeliveryWebController::class, 'assignmentShow'])->name('show');
});
