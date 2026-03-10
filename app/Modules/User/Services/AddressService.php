<?php

namespace App\Modules\User\Services;

use App\Models\Address;
use App\Models\User;

class AddressService
{
    public function list(User $user)
    {
        return $user->addresses()->orderByDesc('is_default')->latest()->get();
    }

    public function store(User $user, array $data): Address
    {
        if (!empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        if ($user->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        return $user->addresses()->create($data);
    }

    public function update(Address $address, array $data): Address
    {
        if (!empty($data['is_default'])) {
            $address->user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return $address->fresh();
    }

    public function delete(Address $address): void
    {
        $wasDefault = $address->is_default;
        $userId = $address->user_id;

        $address->delete();

        // If deleted address was default, make the most recent one default
        if ($wasDefault) {
            Address::where('user_id', $userId)->latest()->first()?->update(['is_default' => true]);
        }
    }

    public function setDefault(Address $address): Address
    {
        $address->user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return $address->fresh();
    }
}
