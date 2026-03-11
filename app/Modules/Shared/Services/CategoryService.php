<?php

namespace App\Modules\Shared\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    public function listActive(): Collection
    {
        return Cache::remember('categories:active', 3600, function () {
            return Category::active()
                ->root()
                ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')])
                ->orderBy('sort_order')
                ->get();
        });
    }

    public function findBySlug(string $slug): ?Category
    {
        return Cache::remember("categories:slug:{$slug}", 3600, function () use ($slug) {
            return Category::where('slug', $slug)
                ->active()
                ->with(['children' => fn ($q) => $q->active()])
                ->first();
        });
    }

    public function store(array $data): Category
    {
        $category = Category::create($data);
        $this->clearCache();

        return $category;
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        $this->clearCache();

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget('categories:active');
        Cache::forget('homepage:categories');
    }
}
