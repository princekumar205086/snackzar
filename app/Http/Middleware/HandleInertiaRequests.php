<?php

namespace App\Http\Middleware;

use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $cartCount = 0;
        $cartTotal = 0;

        if ($request->user()) {
            $cart = Cart::with('items')
                ->where('user_id', $request->user()->id)
                ->first();

            if ($cart) {
                $cartCount = $cart->items->sum('quantity');
                $cartTotal = (int) $cart->items->sum(fn ($item) => $item->quantity * $item->unit_price);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
        ];
    }
}
