<?php

namespace Database\Factories;

use App\Models\DeliveryProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryProfileFactory extends Factory
{
    protected $model = DeliveryProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'vehicle_type' => fake()->randomElement(['bike', 'scooter', 'car']),
            'vehicle_number' => strtoupper(fake()->bothify('??-##-??-####')),
            'license_number' => strtoupper(fake()->bothify('DL-##########')),
            'aadhar_number' => fake()->numerify('############'),
            'bank_name' => fake()->randomElement(['SBI', 'HDFC', 'ICICI']),
            'bank_account_number' => fake()->numerify('##############'),
            'bank_ifsc' => strtoupper(fake()->bothify('????0######')),
            'upi_id' => fake()->userName() . '@upi',
            'is_available' => true,
            'current_latitude' => fake()->latitude(25.5, 26.5),
            'current_longitude' => fake()->longitude(84.5, 86.0),
            'total_earnings' => 0,
            'pending_payout' => 0,
            'total_deliveries' => 0,
            'avg_rating' => 0,
            'status' => 'approved',
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'approved_at' => null,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn () => ['is_available' => false]);
    }
}
