<?php

namespace App\Modules\Seller\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SellerProductService
{
    public function list(User $seller, array $filters = []): LengthAwarePaginator
    {
        $query = Product::where('seller_id', $seller->id)->with(['category', 'primaryImage']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function show(User $seller, int $productId): Product
    {
        return Product::where('seller_id', $seller->id)
            ->with(['category', 'images', 'variants'])
            ->findOrFail($productId);
    }

    public function store(User $seller, array $data): Product
    {
        return DB::transaction(function () use ($seller, $data) {
            $data['seller_id'] = $seller->id;
            $data['slug'] = Str::slug($data['name']);

            // Ensure unique slug
            $slugCount = Product::where('slug', $data['slug'])->count();
            if ($slugCount > 0) {
                $data['slug'] .= '-' . ($slugCount + 1);
            }

            $images = $data['images'] ?? [];
            unset($data['images']);

            $product = Product::create($data);

            foreach ($images as $i => $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => $imageUrl,
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }

            return $product->load(['category', 'images']);
        });
    }

    public function update(User $seller, int $productId, array $data): Product
    {
        $product = Product::where('seller_id', $seller->id)->findOrFail($productId);

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = Str::slug($data['name']);
            $slugCount = Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->count();
            if ($slugCount > 0) {
                $data['slug'] .= '-' . ($slugCount + 1);
            }
        }

        unset($data['images']);
        $product->update($data);

        return $product->fresh(['category', 'images', 'variants']);
    }

    public function delete(User $seller, int $productId): void
    {
        $product = Product::where('seller_id', $seller->id)->findOrFail($productId);
        $product->delete();
    }

    public function toggleActive(User $seller, int $productId): Product
    {
        $product = Product::where('seller_id', $seller->id)->findOrFail($productId);
        $product->update(['is_active' => !$product->is_active]);

        return $product->fresh();
    }
}
