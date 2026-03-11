<?php

namespace App\Modules\Seller\Services;

use App\Models\SellerPayout;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SellerProfileService
{
    public function getProfile(User $user): SellerProfile
    {
        return $user->sellerProfile ?? throw ValidationException::withMessages([
            'profile' => ['Seller profile not found.'],
        ]);
    }

    public function createProfile(User $user, array $data): SellerProfile
    {
        if ($user->sellerProfile) {
            throw ValidationException::withMessages([
                'profile' => ['Seller profile already exists.'],
            ]);
        }

        return SellerProfile::create(array_merge($data, [
            'user_id' => $user->id,
            'status' => 'pending',
        ]));
    }

    public function updateProfile(User $user, array $data): SellerProfile
    {
        $profile = $this->getProfile($user);
        $profile->update($data);

        return $profile->fresh();
    }

    public function getDashboard(User $user): array
    {
        $profile = $this->getProfile($user);

        return [
            'profile' => $profile,
            'total_earnings' => (float) $profile->total_earnings,
            'pending_payout' => (float) $profile->pending_payout,
            'total_products' => $user->products()->count(),
            'active_products' => $user->products()->active()->count(),
            'total_orders' => $user->sellerOrderItems()->count(),
            'pending_orders' => $user->sellerOrderItems()->where('status', 'pending')->count(),
        ];
    }

    public function listPayouts(User $user): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $profile = $this->getProfile($user);

        return $profile->payouts()->latest()->paginate(15);
    }

    public function requestPayout(User $user, float $amount): SellerPayout
    {
        $profile = $this->getProfile($user);

        if ($amount > (float) $profile->pending_payout) {
            throw ValidationException::withMessages([
                'amount' => ['Insufficient balance. Available: ₹' . $profile->pending_payout],
            ]);
        }

        if ($amount < 100) {
            throw ValidationException::withMessages([
                'amount' => ['Minimum payout amount is ₹100.'],
            ]);
        }

        $payout = $profile->payouts()->create([
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $profile->decrement('pending_payout', $amount);

        return $payout;
    }
}
