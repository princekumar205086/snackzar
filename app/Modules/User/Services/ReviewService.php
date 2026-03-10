<?php

namespace App\Modules\User\Services;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function listForProduct(int $productId): LengthAwarePaginator
    {
        return Review::where('product_id', $productId)
            ->approved()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);
    }

    public function store(User $user, int $productId, array $data): Review
    {
        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'product' => ['You have already reviewed this product.'],
            ]);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => false,
        ]);

        $this->updateProductRating($productId);

        return $review;
    }

    public function update(Review $review, array $data): Review
    {
        $review->update([
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? $review->comment,
            'is_approved' => false, // Re-approve after edit
        ]);

        $this->updateProductRating($review->product_id);

        return $review->fresh();
    }

    public function delete(Review $review): void
    {
        $productId = $review->product_id;
        $review->delete();
        $this->updateProductRating($productId);
    }

    private function updateProductRating(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $reviews = Review::where('product_id', $productId)->approved();

        $product->update([
            'avg_rating' => $reviews->avg('rating') ?? 0,
            'total_reviews' => $reviews->count(),
        ]);
    }
}
