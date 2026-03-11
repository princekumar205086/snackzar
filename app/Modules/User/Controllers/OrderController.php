<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Orders
 *
 * APIs for placing orders, viewing order history, and cancelling orders.
 */
class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listOrders($request->user());

        return $this->success($orders);
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $order = $this->orderService->getOrder($request->user(), $order);

        return $this->success($order);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['sometimes', 'in:cod,razorpay,upi'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $order = $this->orderService->placeOrder($request->user(), $data);

        return $this->created($order, 'Order placed successfully.');
    }

    public function cancel(Request $request, int $order): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order = $this->orderService->cancelOrder($request->user(), $order, $data['reason']);

        return $this->success($order, 'Order cancelled successfully.');
    }
}
