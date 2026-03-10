<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());

        return $this->success([
            'cart' => $cart,
            'total' => $cart->totalAmount(),
            'total_items' => $cart->totalItems(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
        ]);

        $cart = $this->cartService->addItem(
            $request->user(),
            $request->product_id,
            $request->integer('quantity', 1),
            $request->product_variant_id
        );

        return $this->success([
            'cart' => $cart,
            'total' => $cart->totalAmount(),
            'total_items' => $cart->totalItems(),
        ], 'Item added to cart.');
    }

    public function update(Request $request, int $cartItem): JsonResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = $this->cartService->updateQuantity(
            $request->user(),
            $cartItem,
            $request->integer('quantity')
        );

        return $this->success([
            'cart' => $cart,
            'total' => $cart->totalAmount(),
            'total_items' => $cart->totalItems(),
        ], 'Cart updated.');
    }

    public function destroy(Request $request, int $cartItem): JsonResponse
    {
        $cart = $this->cartService->removeItem($request->user(), $cartItem);

        return $this->success([
            'cart' => $cart,
            'total' => $cart->totalAmount(),
            'total_items' => $cart->totalItems(),
        ], 'Item removed from cart.');
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request->user());

        return $this->success(null, 'Cart cleared.');
    }
}
