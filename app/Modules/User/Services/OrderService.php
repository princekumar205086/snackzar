<?php

namespace App\Modules\User\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderService
{
    public function listOrders(User $user): LengthAwarePaginator
    {
        return $user->orders()
            ->with(['items.product.primaryImage', 'payment'])
            ->latest()
            ->paginate(10);
    }

    public function getOrder(User $user, int $orderId): Order
    {
        return $user->orders()
            ->with(['items.product.primaryImage', 'items.variant', 'payment', 'address'])
            ->findOrFail($orderId);
    }

    public function placeOrder(User $user, array $data): Order
    {
        $cart = Cart::where('user_id', $user->id)->with('items.product', 'items.variant')->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => ['Your cart is empty.']]);
        }

        $address = Address::where('user_id', $user->id)->findOrFail($data['address_id']);

        // Validate stock availability
        foreach ($cart->items as $item) {
            $product = $item->product;
            $stock = $item->variant ? $item->variant->stock : $product->stock;

            if ($item->quantity > $stock) {
                throw ValidationException::withMessages([
                    'stock' => ["{$product->name} only has {$stock} units available."],
                ]);
            }
        }

        // Resolve coupon if provided
        $coupon   = null;
        $discount = 0;
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper(trim($data['coupon_code'])))->first();
            if (!$coupon) {
                throw ValidationException::withMessages(['coupon_code' => ['Invalid coupon code.']]);
            }
        }

        return DB::transaction(function () use ($user, $cart, $address, $data, $coupon) {
            $subtotal      = $cart->items->sum(fn ($item) => $item->unit_price * $item->quantity);
            $shippingCharge = $subtotal >= 500 ? 0 : 50;
            $tax           = round($subtotal * 0.05, 2);

            // Apply coupon discount
            $discount = 0;
            if ($coupon && $coupon->isValidForUser($user, $subtotal)) {
                $discount = $coupon->calculateDiscount($subtotal);
            }

            $total = max(0, $subtotal + $shippingCharge + $tax - $discount);

            $shippingAddress = [
                'name'         => $address->name,
                'phone'        => $address->phone,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city'         => $address->city,
                'state'        => $address->state,
                'pincode'      => $address->pincode,
                'landmark'     => $address->landmark,
            ];

            $order = Order::create([
                'order_number'    => Order::generateOrderNumber(),
                'user_id'         => $user->id,
                'address_id'      => $address->id,
                'status'          => 'pending',
                'subtotal'        => $subtotal,
                'shipping_charge' => $shippingCharge,
                'tax'             => $tax,
                'discount'        => $discount,
                'coupon_code'     => $coupon?->code,
                'total'           => $total,
                'notes'           => $data['notes'] ?? null,
                'shipping_address'=> $shippingAddress,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'seller_id'          => $item->product->seller_id,
                    'product_name'       => $item->product->name,
                    'variant_name'       => $item->variant?->name,
                    'sku'                => $item->variant?->sku ?? $item->product->sku,
                    'quantity'           => $item->quantity,
                    'unit_price'         => $item->unit_price,
                    'total'              => $item->unit_price * $item->quantity,
                ]);

                // Deduct stock
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }

                $item->product->increment('total_sold', $item->quantity);
            }

            // Increment coupon usage
            if ($coupon && $discount > 0) {
                $coupon->increment('used_count');
                // Track per-user usage in pivot (for bulk/enterprise coupons)
                if (in_array($coupon->scope, ['bulk', 'enterprise'])) {
                    $coupon->assignedUsers()->updateExistingPivot($user->id, [
                        'used_count' => DB::raw('used_count + 1'),
                    ]);
                }
            }

            // Create payment record
            $paymentMethod = $data['payment_method'] ?? 'cod';
            Payment::create([
                'order_id' => $order->id,
                'method'   => $paymentMethod,
                'status'   => 'pending',
                'amount'   => $total,
            ]);

            // Clear cart
            $cart->items()->delete();

            DB::afterCommit(function () use ($user, $order) {
                try {
                    $user->notify(new OrderPlacedNotification($order));
                } catch (Throwable $e) {
                    report($e);
                }
            });

            return $order->load(['items', 'payment']);
        });
    }

    public function cancelOrder(User $user, int $orderId, string $reason): Order
    {
        $order = $user->orders()->findOrFail($orderId);

        if (!$order->isCancellable()) {
            throw ValidationException::withMessages([
                'order' => ['This order cannot be cancelled.'],
            ]);
        }

        return DB::transaction(function () use ($order, $reason) {
            // Restore stock
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant?->increment('stock', $item->quantity);
                } else {
                    $item->product->increment('stock', $item->quantity);
                }
                $item->product->decrement('total_sold', $item->quantity);
                $item->update(['status' => 'cancelled']);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            if ($order->payment && $order->payment->isPaid()) {
                $order->payment->update(['status' => 'refunded']);
            }

            return $order->fresh(['items', 'payment']);
        });
    }
}
