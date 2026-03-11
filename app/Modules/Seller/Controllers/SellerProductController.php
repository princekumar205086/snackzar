<?php

namespace App\Modules\Seller\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seller\Services\SellerProductService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Seller Products
 *
 * APIs for sellers to manage their products (CRUD, variants, inventory).
 */
class SellerProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SellerProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->list(
            $request->user(),
            $request->only(['search', 'is_active', 'category_id', 'per_page'])
        );

        return $this->success($products);
    }

    public function show(Request $request, int $product): JsonResponse
    {
        $product = $this->productService->show($request->user(), $product);

        return $this->success($product);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'in:piece,kg,g,pack'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:500'],
        ]);

        $product = $this->productService->store($request->user(), $data);

        return $this->created($product, 'Product created.');
    }

    public function update(Request $request, int $product): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'in:piece,kg,g,pack'],
        ]);

        $product = $this->productService->update($request->user(), $product, $data);

        return $this->success($product, 'Product updated.');
    }

    public function destroy(Request $request, int $product): JsonResponse
    {
        $this->productService->delete($request->user(), $product);

        return $this->noContent('Product deleted.');
    }

    public function toggleActive(Request $request, int $product): JsonResponse
    {
        $product = $this->productService->toggleActive($request->user(), $product);

        return $this->success($product, 'Product status toggled.');
    }
}
