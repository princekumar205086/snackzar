<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 50, 2000);

        return [
            'category_id' => Category::factory(),
            'seller_id' => User::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SNK-####-??')),
            'price' => $price,
            'compare_price' => fake()->optional(0.3)->randomFloat(2, $price + 50, $price + 500),
            'cost_price' => round($price * 0.6, 2),
            'stock' => fake()->numberBetween(0, 500),
            'low_stock_threshold' => 5,
            'weight' => fake()->randomFloat(2, 50, 5000),
            'unit' => fake()->randomElement(['piece', 'kg', 'g', 'pack']),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'attributes' => null,
            'meta' => null,
            'avg_rating' => 0,
            'total_reviews' => 0,
            'total_sold' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
