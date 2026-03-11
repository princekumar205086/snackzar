<?php

namespace App\Modules\User;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
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
        $modulePath = app_path('Modules/User');

        if (file_exists("{$modulePath}/routes/web.php")) {
            Route::middleware(['web'])
                ->group("{$modulePath}/routes/web.php");
        }

        if (file_exists("{$modulePath}/routes/api.php")) {
            Route::middleware(['api'])
                ->prefix('api/v1/user')
                ->name('api.user.')
                ->group("{$modulePath}/routes/api.php");
        }
    }

    protected function loadMigrations(): void
    {
        $migrationsPath = app_path('Modules/User/database/migrations');
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }
}
