<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ApiResponse;

    /**
     * Validate and preview a coupon discount for the current user.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'         => ['required', 'string'],
            'order_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (!$coupon) {
            return $this->error('Invalid coupon code.', 422);
        }

        $user = $request->user();

        if (!$coupon->isValidForUser($user, (float) $request->order_amount)) {
            // Provide helpful specific error messages
            if (!$coupon->is_active || ($coupon->expires_at?->isPast())) {
                $msg = 'This coupon has expired.';
            } elseif ($coupon->max_uses > 0 && $coupon->used_count >= $coupon->max_uses) {
                $msg = 'This coupon has reached its usage limit.';
            } elseif ((float) $request->order_amount < (float) $coupon->min_order_amount) {
                $msg = "Minimum order amount of ₹{$coupon->min_order_amount} required.";
            } elseif (in_array($coupon->scope, ['individual', 'bulk', 'enterprise'])) {
                $msg = 'This coupon is not available for your account.';
            } else {
                $msg = 'Invalid or expired coupon code.';
            }

            return $this->error($msg, 422);
        }

        $discount = $coupon->calculateDiscount((float) $request->order_amount);

        return $this->success([
            'code'        => $coupon->code,
            'type'        => $coupon->type,
            'value'       => $coupon->value,
            'discount'    => $discount,
            'description' => $coupon->description,
            'scope'       => $coupon->scope,
        ], 'Coupon applied successfully!');
    }

    /**
     * List all coupons available to the authenticated user.
     */
    public function myCoupons(Request $request): JsonResponse
    {
        $user = $request->user();

        $coupons = Coupon::active()
            ->forUser($user->id)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(fn ($q) => $q->where('max_uses', 0)->orWhereColumn('used_count', '<', 'max_uses'))
            ->get(['id', 'code', 'scope', 'type', 'value', 'max_discount', 'min_order_amount', 'expires_at', 'description']);

        return $this->success($coupons);
    }
}
