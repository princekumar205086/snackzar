<?php

namespace App\Modules\Admin\Services;

use App\Models\Order;
use App\Notifications\OrderStatusNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminOrderService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Order::with(['user', 'payment', 'deliveryAssignment.deliveryProfile.user']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function show(int $orderId): Order
    {
        return Order::with(['user', 'items.product', 'payment', 'address', 'deliveryAssignment.deliveryProfile.user'])
            ->findOrFail($orderId);
    }

    public function updateStatus(int $orderId, string $status): Order
    {
        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;

        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
        ];

        $allowed = $validTransitions[$order->status] ?? [];
        if (!in_array($status, $allowed)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from '{$order->status}' to '{$status}'."],
            ]);
        }

        $timestamps = [
            'confirmed' => ['confirmed_at' => now()],
            'shipped' => ['shipped_at' => now()],
            'delivered' => ['delivered_at' => now()],
            'cancelled' => ['cancelled_at' => now()],
        ];

        $order->update(array_merge(
            ['status' => $status],
            $timestamps[$status] ?? []
        ));

        $order->user?->notify(new OrderStatusNotification($order, $oldStatus));

        return $order->fresh(['user', 'payment']);
    }
}
