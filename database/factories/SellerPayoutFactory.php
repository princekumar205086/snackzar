<?php

namespace Database\Factories;

use App\Models\SellerPayout;
use App\Models\SellerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerPayoutFactory extends Factory
{
    protected $model = SellerPayout::class;

    public function definition(): array
    {
        return [
            'seller_profile_id' => SellerProfile::factory(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'status' => 'pending',
            'transaction_id' => null,
            'notes' => null,
            'processed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'transaction_id' => 'TXN' . fake()->numerify('##########'),
            'processed_at' => now(),
        ]);
    }
}
