<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 200, 2000);
        $shipping = $subtotal >= 500 ? 0 : 50;
        $tax = round($subtotal * 0.05, 2);

        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'address_id' => Address::factory(),
            'status' => 'confirmed',
            'subtotal' => $subtotal,
            'shipping_charge' => $shipping,
            'tax' => $tax,
            'discount' => 0,
            'total' => $subtotal + $shipping + $tax,
            'shipping_address' => [
                'name' => fake()->name(),
                'phone' => fake()->phoneNumber(),
                'address_line_1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => 'Bihar',
                'pincode' => fake()->numerify('######'),
            ],
        ];
    }

    public function shipped(): static
    {
        return $this->state(fn () => [
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);
    }
}
