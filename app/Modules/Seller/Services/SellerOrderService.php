<?php

namespace App\Modules\Seller\Services;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SellerOrderService
{
    public function listOrders(User $seller, array $filters = []): LengthAwarePaginator
    {
        $query = OrderItem::where('seller_id', $seller->id)
            ->with(['order.user', 'order.payment', 'product.primaryImage', 'variant']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getOrderItem(User $seller, int $orderItemId): OrderItem
    {
        return OrderItem::where('seller_id', $seller->id)
            ->with(['order.user', 'order.address', 'order.payment', 'product', 'variant'])
            ->findOrFail($orderItemId);
    }

    public function updateStatus(User $seller, int $orderItemId, string $status): OrderItem
    {
        $item = OrderItem::where('seller_id', $seller->id)->findOrFail($orderItemId);
        $item->update(['status' => $status]);

        return $item->fresh(['order', 'product']);
    }
}
