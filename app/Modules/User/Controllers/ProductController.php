<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Services\ProductService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Products
 *
 * APIs for browsing the product catalog (list with filters, detail, featured, related).
 */
class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->list($request->only([
            'category_id', 'search', 'min_price', 'max_price',
            'featured', 'sort_by', 'sort_dir', 'per_page',
        ]));

        return $this->success($products);
    }

    public function show(string $slug): JsonResponse
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return $this->error('Product not found.', 404);
        }

        return $this->success($product);
    }

    public function featured(): JsonResponse
    {
        $products = $this->productService->featured();

        return $this->success($products);
    }

    public function related(string $slug): JsonResponse
    {
        $product = $this->productService->findBySlug($slug);

        if (!$product) {
            return $this->error('Product not found.', 404);
        }

        $related = $this->productService->relatedProducts($product);

        return $this->success($related);
    }
}
