<?php

namespace App\Modules\Seller\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seller\Services\SellerOrderService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Seller Orders
 *
 * APIs for sellers to manage incoming orders (list, detail, update status).
 */
class SellerOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SellerOrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listOrders(
            $request->user(),
            $request->only(['status', 'per_page'])
        );

        return $this->success($orders);
    }

    public function show(Request $request, int $orderItem): JsonResponse
    {
        $item = $this->orderService->getOrderItem($request->user(), $orderItem);

        return $this->success($item);
    }

    public function updateStatus(Request $request, int $orderItem): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:processing,shipped,delivered'],
        ]);

        $item = $this->orderService->updateStatus($request->user(), $orderItem, $data['status']);

        return $this->success($item, 'Order item status updated.');
    }
}
