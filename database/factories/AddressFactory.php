<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['home', 'work', 'other']),
            'name' => fake()->name(),
            'phone' => fake()->numerify('9#########'),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->sentence(3),
            'city' => fake()->city(),
            'state' => 'Bihar',
            'pincode' => fake()->numerify('8#####'),
            'landmark' => fake()->optional()->sentence(3),
            'is_default' => false,
            'latitude' => fake()->latitude(24, 27),
            'longitude' => fake()->longitude(83, 88),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
