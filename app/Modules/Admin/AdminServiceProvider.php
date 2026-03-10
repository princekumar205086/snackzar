<?php

namespace App\Modules\Admin;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
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
        $modulePath = app_path('Modules/Admin');

        if (file_exists("{$modulePath}/routes/web.php")) {
            Route::middleware(['web', 'auth', 'role:admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group("{$modulePath}/routes/web.php");
        }

        if (file_exists("{$modulePath}/routes/api.php")) {
            Route::middleware(['api', 'auth:sanctum', 'role:admin'])
                ->prefix('api/v1/admin')
                ->name('api.admin.')
                ->group("{$modulePath}/routes/api.php");
        }
    }

    protected function loadMigrations(): void
    {
        $migrationsPath = app_path('Modules/Admin/database/migrations');
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
