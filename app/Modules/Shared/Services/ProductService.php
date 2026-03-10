<?php

namespace App\Modules\Shared\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Product::active()->inStock()->with(['category', 'primaryImage', 'seller']);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['featured'])) {
            $query->featured();
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $allowedSorts = ['price', 'name', 'created_at', 'avg_rating', 'total_sold'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return $query->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)
            ->active()
            ->with(['category', 'images', 'variants' => fn ($q) => $q->active(), 'seller'])
            ->first();
    }

    public function featured(int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()
            ->inStock()
            ->featured()
            ->with(['primaryImage', 'category'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function relatedProducts(Product $product, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        return Product::active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage'])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
