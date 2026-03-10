<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 50, 2000);

        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['250g Pack', '500g Pack', '1kg Pack', 'Small', 'Medium', 'Large']),
            'sku' => strtoupper(fake()->unique()->bothify('SNK-V-####-??')),
            'price' => $price,
            'compare_price' => null,
            'stock' => fake()->numberBetween(0, 200),
            'weight' => fake()->randomFloat(2, 100, 1000),
            'attributes' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
