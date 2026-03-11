<?php

namespace App\Observers;

use App\Models\Category;
use App\Modules\Shared\Services\CategoryService;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        $this->clearCache($category);
    }

    public function deleted(Category $category): void
    {
        $this->clearCache($category);
    }

    protected function clearCache(Category $category): void
    {
        app(CategoryService::class)->clearCache();
        Cache::forget("categories:slug:{$category->slug}");
        Cache::forget('sitemap:urls');
    }
}
