<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Razorpay\Api\Api as RazorpayApi;

class UserWebController extends Controller
{
    public function dashboard(): Response
    {
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

    public function checkout(): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        return Inertia::render('Checkout', [
            'razorpayKey' => config('services.razorpay.key'),
        ]);
    }

    public function createPaymentOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'amount'   => 'required|integer|min:1', // in paise
        ]);

        try {
            $api = new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );

            $razorpayOrder = $api->order->create([
                'receipt'  => 'order_' . $request->order_id,
                'amount'   => $request->amount,
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
            return back()->withErrors(['payment' => 'Payment verification failed.']);
        }

        // Store payment record — call the existing API payment endpoint internally
        try {
            $user = $request->user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            Payment::create([
                'order_id'           => $order->id,
                'user_id'            => $user->id,
                'gateway'            => 'razorpay',
                'gateway_order_id'   => $request->razorpay_order_id,
                'gateway_payment_id' => $request->razorpay_payment_id,
                'amount'             => $order->total_amount,
                'status'             => 'completed',
            ]);

            $order->update(['status' => 'confirmed']);

            return redirect('/orders/' . $order->id . '?payment=success');
        } catch (\Exception $e) {
            return back()->withErrors(['payment' => 'Payment recorded but order update failed.']);
        }
    }
}
