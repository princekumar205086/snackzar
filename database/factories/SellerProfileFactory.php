<?php

namespace Database\Factories;

use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerProfileFactory extends Factory
{
    protected $model = SellerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'gst_number' => strtoupper(fake()->bothify('##??????????#Z#')),
            'pan_number' => strtoupper(fake()->bothify('?????####?')),
            'business_address' => fake()->address(),
            'bank_name' => fake()->randomElement(['SBI', 'HDFC', 'ICICI', 'Axis', 'PNB']),
            'bank_account_number' => fake()->numerify('##############'),
            'bank_ifsc' => strtoupper(fake()->bothify('????0######')),
            'upi_id' => fake()->userName() . '@upi',
            'commission_rate' => 10.00,
            'total_earnings' => 0,
            'pending_payout' => 0,
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

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => 'suspended']);
    }
}
