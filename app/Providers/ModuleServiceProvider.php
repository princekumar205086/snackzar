<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    protected array $modules = [
        'Admin',
        'User',
        'Seller',
        'Delivery',
    ];

    public function register(): void
    {
        foreach ($this->modules as $module) {
            $providerClass = "App\\Modules\\{$module}\\{$module}ServiceProvider";
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    public function boot(): void
    {
        //
    }
}
