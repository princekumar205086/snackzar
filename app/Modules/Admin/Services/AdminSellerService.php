<?php

namespace App\Modules\Admin\Services;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryProfile;
use App\Models\Order;
use App\Models\SellerProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminSellerService
{
    public function listSellers(array $filters = []): LengthAwarePaginator
    {
        $query = SellerProfile::with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function approveSeller(int $profileId): SellerProfile
    {
        $profile = SellerProfile::findOrFail($profileId);
        $profile->update(['status' => 'approved', 'approved_at' => now()]);

        return $profile->fresh('user');
    }

    public function suspendSeller(int $profileId): SellerProfile
    {
        $profile = SellerProfile::findOrFail($profileId);
        $profile->update(['status' => 'suspended']);

        return $profile->fresh('user');
    }

    public function listDeliveryPartners(array $filters = []): LengthAwarePaginator
    {
        $query = DeliveryProfile::with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function approveDeliveryPartner(int $profileId): DeliveryProfile
    {
        $profile = DeliveryProfile::findOrFail($profileId);
        $profile->update(['status' => 'approved', 'approved_at' => now()]);

        return $profile->fresh('user');
    }

    public function assignDelivery(int $orderId, int $deliveryProfileId): DeliveryAssignment
    {
        $order = Order::findOrFail($orderId);
        $profile = DeliveryProfile::findOrFail($deliveryProfileId);

        return DeliveryAssignment::create([
            'order_id' => $order->id,
            'delivery_profile_id' => $profile->id,
            'status' => 'assigned',
            'earning' => 50, // Base delivery fee
        ]);
    }

    public function getDashboardStats(): array
    {
        return [
            'total_users' => \App\Models\User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => (float) Order::where('status', '!=', 'cancelled')->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_sellers' => SellerProfile::count(),
            'pending_sellers' => SellerProfile::where('status', 'pending')->count(),
            'total_delivery_partners' => DeliveryProfile::count(),
            'total_products' => \App\Models\Product::count(),
        ];
    }
}
