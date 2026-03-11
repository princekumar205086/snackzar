<?php

namespace App\Modules\Delivery\Services;

use App\Models\DeliveryAssignment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryAssignmentService
{
    public function listAssignments(User $user, array $filters = []): LengthAwarePaginator
    {
        $profile = $user->deliveryProfile;

        if (!$profile) {
            throw ValidationException::withMessages(['profile' => ['Delivery profile not found.']]);
        }

        $query = DeliveryAssignment::where('delivery_profile_id', $profile->id)
            ->with(['order.user', 'order.payment']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function getAssignment(User $user, int $assignmentId): DeliveryAssignment
    {
        $profile = $user->deliveryProfile;

        return DeliveryAssignment::where('delivery_profile_id', $profile->id)
            ->with(['order.user', 'order.items.product', 'order.payment'])
            ->findOrFail($assignmentId);
    }

    public function acceptAssignment(User $user, int $assignmentId): DeliveryAssignment
    {
        $assignment = $this->getAssignment($user, $assignmentId);

        if ($assignment->status !== 'assigned') {
            throw ValidationException::withMessages([
                'status' => ['This assignment cannot be accepted.'],
            ]);
        }

        $assignment->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $assignment->fresh(['order']);
    }

    public function pickUp(User $user, int $assignmentId): DeliveryAssignment
    {
        $assignment = $this->getAssignment($user, $assignmentId);

        if ($assignment->status !== 'accepted') {
            throw ValidationException::withMessages([
                'status' => ['Order must be accepted before pickup.'],
            ]);
        }

        $assignment->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);

        $assignment->order->update(['status' => 'out_for_delivery']);

        return $assignment->fresh(['order']);
    }

    public function markDelivered(User $user, int $assignmentId): DeliveryAssignment
    {
        $assignment = $this->getAssignment($user, $assignmentId);

        if (!in_array($assignment->status, ['picked_up', 'in_transit'])) {
            throw ValidationException::withMessages([
                'status' => ['Order must be picked up before marking as delivered.'],
            ]);
        }

        return DB::transaction(function () use ($assignment) {
            $assignment->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            $assignment->order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);

            // Credit earnings to delivery partner
            $profile = $assignment->deliveryProfile;
            $profile->increment('total_earnings', $assignment->earning);
            $profile->increment('pending_payout', $assignment->earning);
            $profile->increment('total_deliveries');

            return $assignment->fresh(['order']);
        });
    }
}
