<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ReviewService $reviewService
    ) {}

    public function index(int $productId): JsonResponse
    {
        $reviews = $this->reviewService->listForProduct($productId);

        return $this->success($reviews);
    }

    public function store(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $this->reviewService->store($request->user(), $productId, $data);

        return $this->created($review, 'Review submitted for approval.');
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $this->reviewService->update($review, $data);

        return $this->success($review, 'Review updated.');
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return $this->error('Unauthorized.', 403);
        }

        $this->reviewService->delete($review);

        return $this->noContent('Review deleted.');
    }
}
