<?php

namespace App\Modules\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::with('roles');

        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function show(int $userId): User
    {
        return User::with(['roles', 'sellerProfile', 'deliveryProfile'])->findOrFail($userId);
    }

    public function updateStatus(int $userId, string $status): User
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => $status]);

        return $user->fresh('roles');
    }
}
