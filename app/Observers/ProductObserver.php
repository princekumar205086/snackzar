<?php

namespace App\Observers;

use App\Models\Product;
use App\Modules\Shared\Services\ProductService;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function saved(Product $product): void
    {
        $this->clearCache();
    }

    public function deleted(Product $product): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        ProductService::clearCache();
        Cache::forget('sitemap:urls');
    }
}
