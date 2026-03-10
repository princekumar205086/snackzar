<?php

namespace App\Modules\Delivery;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DeliveryServiceProvider extends ServiceProvider
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
        $modulePath = app_path('Modules/Delivery');

        if (file_exists("{$modulePath}/routes/web.php")) {
            Route::middleware(['web', 'auth', 'role:delivery_partner'])
                ->prefix('delivery')
                ->name('delivery.')
                ->group("{$modulePath}/routes/web.php");
        }

        if (file_exists("{$modulePath}/routes/api.php")) {
            Route::middleware(['api', 'auth:sanctum', 'role:delivery_partner'])
                ->prefix('api/v1/delivery')
                ->name('api.delivery.')
                ->group("{$modulePath}/routes/api.php");
        }
    }

    protected function loadMigrations(): void
    {
        $migrationsPath = app_path('Modules/Delivery/database/migrations');
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
