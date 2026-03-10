<?php

namespace App\Modules\User\Services;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;

class WishlistService
{
    public function list(User $user): Collection
    {
        return $user->wishlists()->with('product.primaryImage')->latest()->get();
    }

    public function toggle(User $user, int $productId): array
    {
        $existing = $user->wishlists()->where('product_id', $productId)->first();

        if ($existing) {
            $existing->delete();
            return ['added' => false];
        }

        $user->wishlists()->create(['product_id' => $productId]);
        return ['added' => true];
    }

    public function remove(User $user, int $productId): void
    {
        $user->wishlists()->where('product_id', $productId)->delete();
    }
}
