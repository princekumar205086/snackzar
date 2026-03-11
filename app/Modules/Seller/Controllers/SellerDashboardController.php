<?php

namespace App\Modules\Seller\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seller\Services\SellerProfileService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SellerProfileService $profileService
    ) {}

    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->profileService->getDashboard($request->user());

        return $this->success($data);
    }

    public function profile(Request $request): JsonResponse
    {
        $profile = $this->profileService->getProfile($request->user());

        return $this->success($profile);
    }

    public function createProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:15'],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:20'],
            'bank_ifsc' => ['nullable', 'string', 'max:15'],
            'upi_id' => ['nullable', 'string', 'max:50'],
        ]);

        $profile = $this->profileService->createProfile($request->user(), $data);

        return $this->created($profile, 'Seller profile created. Pending approval.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'business_name' => ['sometimes', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'pan_number' => ['nullable', 'string', 'max:15'],
            'business_address' => ['nullable', 'string', 'max:1000'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:20'],
            'bank_ifsc' => ['nullable', 'string', 'max:15'],
            'upi_id' => ['nullable', 'string', 'max:50'],
        ]);

        $profile = $this->profileService->updateProfile($request->user(), $data);

        return $this->success($profile, 'Profile updated.');
    }

    public function payouts(Request $request): JsonResponse
    {
        $payouts = $this->profileService->listPayouts($request->user());

        return $this->success($payouts);
    }

    public function requestPayout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        $payout = $this->profileService->requestPayout($request->user(), (float) $data['amount']);

        return $this->created($payout, 'Payout request submitted.');
    }
}
