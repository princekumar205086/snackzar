<?php

namespace App\Modules\User\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        if (isset($data['email']) && $data['email'] !== $user->getOriginal('email')) {
            $user->update(['email_verified_at' => null]);
            $user->sendEmailVerificationNotification();
        }

        return $user->fresh();
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->update(['password' => $newPassword]);
    }

    public function deleteAccount(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }
}
