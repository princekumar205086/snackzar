<?php

namespace App\Modules\Delivery\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Delivery\Services\DeliveryProfileService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DeliveryProfileService $profileService
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
            'vehicle_type' => ['required', 'string', 'in:bike,scooter,car'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:20'],
            'aadhar_number' => ['nullable', 'string', 'max:12'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:20'],
            'bank_ifsc' => ['nullable', 'string', 'max:15'],
            'upi_id' => ['nullable', 'string', 'max:50'],
        ]);

        $profile = $this->profileService->createProfile($request->user(), $data);

        return $this->created($profile, 'Delivery profile created. Pending approval.');
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_type' => ['sometimes', 'string', 'in:bike,scooter,car'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'license_number' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:20'],
            'bank_ifsc' => ['nullable', 'string', 'max:15'],
            'upi_id' => ['nullable', 'string', 'max:50'],
        ]);

        $profile = $this->profileService->updateProfile($request->user(), $data);

        return $this->success($profile, 'Profile updated.');
    }

    public function toggleAvailability(Request $request): JsonResponse
    {
        $profile = $this->profileService->toggleAvailability($request->user());

        return $this->success($profile, 'Availability toggled.');
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $profile = $this->profileService->updateLocation(
            $request->user(),
            (float) $data['latitude'],
            (float) $data['longitude']
        );

        return $this->success($profile, 'Location updated.');
    }
}
