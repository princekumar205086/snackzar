<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\AdminOrderService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminOrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->list(
            $request->only(['status', 'search', 'per_page'])
        );

        return $this->success($orders);
    }

    public function show(int $order): JsonResponse
    {
        $order = $this->orderService->show($order);

        return $this->success($order);
    }

    public function updateStatus(Request $request, int $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,processing,shipped,out_for_delivery,delivered,cancelled'],
        ]);

        $order = $this->orderService->updateStatus($order, $data['status']);

        return $this->success($order, 'Order status updated.');
    }
}
