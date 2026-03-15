<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Razorpay\Api\Api as RazorpayApi;

class UserWebController extends Controller
{
    public function dashboard(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        if ($redirect = $this->redirectToRoleDashboard($request->user())) {
            return redirect()->to($redirect);
        }

        return Inertia::render('User/Dashboard');
    }

    public function orders(): Response
    {
        return Inertia::render('User/Orders/Index');
    }

    public function orderShow(int $id): Response
    {
        return Inertia::render('User/Orders/Show', ['id' => $id]);
    }

    public function profile(): Response
    {
        return Inertia::render('User/Profile');
    }

    public function wishlist(): Response
    {
        return Inertia::render('User/Wishlist');
    }

    public function addresses(): Response
    {
        return Inertia::render('User/Addresses/Index');
    }

    public function notifications(): Response
    {
        return Inertia::render('User/Notifications');
    }

    public function cart(): Response
    {
        return Inertia::render('Cart/Index');
    }

    public function checkout()
    {
        if (!Auth::check()) {
            return redirect()->guest('/login');
        }
        return Inertia::render('Checkout', [
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }

    public function createPaymentOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        try {
            $user = $request->user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $api = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $amountInPaise = (int) round((float) $order->total * 100);

            $razorpayOrder = $api->order->create([
                'receipt'  => 'order_' . $request->order_id,
                'amount'   => $amountInPaise,
                'currency' => 'INR',
                'notes'    => ['order_id' => $request->order_id],
            ]);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'razorpay_order_id' => $razorpayOrder->id,
                    'amount'            => $razorpayOrder->amount,
                    'currency'          => $razorpayOrder->currency,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not create payment order. Try again.',
            ], 422);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
            'order_id'            => 'required|integer',
        ]);

        $generated = hash_hmac(
            'sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            config('services.razorpay.secret')
        );

        if (!hash_equals($generated, $request->razorpay_signature)) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        try {
            $user = $request->user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            Payment::updateOrCreate([
                'order_id' => $order->id,
            ], [
                'payment_id' => $request->razorpay_payment_id,
                'method' => 'razorpay',
                'status' => 'paid',
                'amount' => $order->total,
                'currency' => 'INR',
                'paid_at' => now(),
                'gateway_response' => [
                    'gateway' => 'razorpay',
                    'order_id' => $request->razorpay_order_id,
                    'payment_id' => $request->razorpay_payment_id,
                    'signature' => $request->razorpay_signature,
                ],
            ]);

            $order->update(['status' => 'confirmed']);

            return response()->json([
                'status' => 'success',
                'redirect' => '/orders/' . $order->id . '?payment=success',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment recorded but order update failed.'], 422);
        }
    }

    private function redirectToRoleDashboard(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->hasRole('admin')) {
            return '/admin/dashboard';
        }

        if ($user->hasRole('seller') && ! $user->hasRole('user')) {
            return '/seller/dashboard';
        }

        if ($user->hasRole('delivery_partner') && ! $user->hasRole('user')) {
            return '/delivery/dashboard';
        }

        return null;
    }
}
