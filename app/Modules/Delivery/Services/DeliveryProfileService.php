<?php

namespace App\Modules\Delivery\Services;

use App\Models\DeliveryProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeliveryProfileService
{
    public function getProfile(User $user): DeliveryProfile
    {
        return $user->deliveryProfile ?? throw ValidationException::withMessages([
            'profile' => ['Delivery profile not found.'],
        ]);
    }

    public function createProfile(User $user, array $data): DeliveryProfile
    {
        if ($user->deliveryProfile) {
            throw ValidationException::withMessages([
                'profile' => ['Delivery profile already exists.'],
            ]);
        }

        return DeliveryProfile::create(array_merge($data, [
            'user_id' => $user->id,
            'status' => 'pending',
        ]));
    }

    public function updateProfile(User $user, array $data): DeliveryProfile
    {
        $profile = $this->getProfile($user);
        $profile->update($data);

        return $profile->fresh();
    }

    public function toggleAvailability(User $user): DeliveryProfile
    {
        $profile = $this->getProfile($user);
        $profile->update(['is_available' => !$profile->is_available]);

        return $profile->fresh();
    }

    public function updateLocation(User $user, float $latitude, float $longitude): DeliveryProfile
    {
        $profile = $this->getProfile($user);
        $profile->update([
            'current_latitude' => $latitude,
            'current_longitude' => $longitude,
        ]);

        return $profile->fresh();
    }

    public function getDashboard(User $user): array
    {
        $profile = $this->getProfile($user);

        return [
            'profile' => $profile,
            'total_earnings' => (float) $profile->total_earnings,
            'pending_payout' => (float) $profile->pending_payout,
            'total_deliveries' => $profile->total_deliveries,
            'avg_rating' => (float) $profile->avg_rating,
            'is_available' => $profile->is_available,
            'active_assignments' => $profile->assignments()
                ->whereNotIn('status', ['delivered', 'failed'])
                ->count(),
        ];
    }
}
