<?php

namespace App\Modules\Seller;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SellerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
    }

    protected function loadRoutes(): void
    {
        $modulePath = app_path('Modules/Seller');

        if (file_exists("{$modulePath}/routes/web.php")) {
            Route::middleware(['web', 'auth', 'role:seller'])
                ->prefix('seller')
                ->name('seller.')
                ->group("{$modulePath}/routes/web.php");
        }

        if (file_exists("{$modulePath}/routes/api.php")) {
            Route::middleware(['api', 'auth:sanctum', 'role:seller'])
                ->prefix('api/v1/seller')
                ->name('api.seller.')
                ->group("{$modulePath}/routes/api.php");
        }
    }

    protected function loadMigrations(): void
    {
        $migrationsPath = app_path('Modules/Seller/database/migrations');
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
