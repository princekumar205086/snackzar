<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\AdminSellerService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin Sellers & Delivery
 *
 * APIs for admin management of sellers and delivery partners (list, approve, suspend, assign delivery).
 */
class AdminSellerController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminSellerService $sellerService
    ) {}

    public function sellers(Request $request): JsonResponse
    {
        $sellers = $this->sellerService->listSellers($request->only(['status', 'per_page']));

        return $this->success($sellers);
    }

    public function approveSeller(int $profile): JsonResponse
    {
        $seller = $this->sellerService->approveSeller($profile);

        return $this->success($seller, 'Seller approved.');
    }

    public function suspendSeller(int $profile): JsonResponse
    {
        $seller = $this->sellerService->suspendSeller($profile);

        return $this->success($seller, 'Seller suspended.');
    }

    public function deliveryPartners(Request $request): JsonResponse
    {
        $partners = $this->sellerService->listDeliveryPartners($request->only(['status', 'per_page']));

        return $this->success($partners);
    }

    public function approveDeliveryPartner(int $profile): JsonResponse
    {
        $partner = $this->sellerService->approveDeliveryPartner($profile);

        return $this->success($partner, 'Delivery partner approved.');
    }

    public function assignDelivery(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'delivery_profile_id' => ['required', 'exists:delivery_profiles,id'],
        ]);

        $assignment = $this->sellerService->assignDelivery(
            $data['order_id'],
            $data['delivery_profile_id']
        );

        return $this->created($assignment, 'Delivery assigned.');
    }
}
