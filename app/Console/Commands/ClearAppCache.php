<?php

namespace App\Console\Commands;

use App\Modules\Shared\Services\CategoryService;
use App\Modules\Shared\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearAppCache extends Command
{
    protected $signature = 'app:clear-cache';

    protected $description = 'Clear all application-level caches (homepage, products, categories, sitemap)';

    public function handle(): int
    {
        ProductService::clearCache();
        app(CategoryService::class)->clearCache();

        Cache::forget('homepage:reviews');
        Cache::forget('sitemap:urls');

        $this->info('Application cache cleared successfully.');

        return self::SUCCESS;
    }
}
