<?php

namespace App\Modules\User\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getCart(User $user): Cart
    {
        $cart = $user->getOrCreateCart();
        $cart->load(['items.product.primaryImage', 'items.variant']);

        return $cart;
    }

    public function addItem(User $user, int $productId, int $quantity = 1, ?int $variantId = null): Cart
    {
        $product = Product::active()->findOrFail($productId);

        if (!$product->isInStock()) {
            throw ValidationException::withMessages(['product' => ['Product is out of stock.']]);
        }

        $price = $product->price;
        $stockToCheck = $product->stock;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)->active()->findOrFail($variantId);
            $price = $variant->price;
            $stockToCheck = $variant->stock;
        }

        if ($quantity > $stockToCheck) {
            throw ValidationException::withMessages(['quantity' => ["Only {$stockToCheck} items available."]]);
        }

        $cart = $user->getOrCreateCart();

        $existingItem = $cart->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $quantity;
            if ($newQty > $stockToCheck) {
                throw ValidationException::withMessages(['quantity' => ["Only {$stockToCheck} items available."]]);
            }
            $existingItem->update(['quantity' => $newQty, 'unit_price' => $price]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'unit_price' => $price,
            ]);
        }

        return $this->getCart($user);
    }

    public function updateQuantity(User $user, int $cartItemId, int $quantity): Cart
    {
        $cart = $user->getOrCreateCart();
        $item = $cart->items()->findOrFail($cartItemId);

        $stock = $item->variant ? $item->variant->stock : $item->product->stock;

        if ($quantity > $stock) {
            throw ValidationException::withMessages(['quantity' => ["Only {$stock} items available."]]);
        }

        if ($quantity <= 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $quantity]);
        }

        return $this->getCart($user);
    }

    public function removeItem(User $user, int $cartItemId): Cart
    {
        $cart = $user->getOrCreateCart();
        $cart->items()->findOrFail($cartItemId)->delete();

        return $this->getCart($user);
    }

    public function clear(User $user): void
    {
        $cart = $user->cart;
        if ($cart) {
            $cart->items()->delete();
        }
    }
}
