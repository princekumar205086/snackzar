<?php

namespace Database\Factories;

use App\Models\DeliveryAssignment;
use App\Models\DeliveryProfile;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryAssignmentFactory extends Factory
{
    protected $model = DeliveryAssignment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'delivery_profile_id' => DeliveryProfile::factory(),
            'status' => 'assigned',
            'earning' => fake()->randomFloat(2, 30, 100),
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn () => [
            'status' => 'delivered',
            'accepted_at' => now()->subHours(2),
            'picked_up_at' => now()->subHour(),
            'delivered_at' => now(),
        ]);
    }
}
