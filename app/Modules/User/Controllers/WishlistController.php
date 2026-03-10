<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly WishlistService $wishlistService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $wishlists = $this->wishlistService->list($request->user());

        return $this->success($wishlists);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $result = $this->wishlistService->toggle($request->user(), $request->product_id);

        $message = $result['added'] ? 'Added to wishlist.' : 'Removed from wishlist.';

        return $this->success($result, $message);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $this->wishlistService->remove($request->user(), $productId);

        return $this->success(null, 'Removed from wishlist.');
    }
}
