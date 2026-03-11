<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'author_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'featured_image' => null,
            'category' => fake()->randomElement(['Makhana', 'Recipes', 'Health', 'Bihar Culture']),
            'tags' => [fake()->word(), fake()->word()],
            'status' => 'published',
            'meta_title' => $title,
            'meta_description' => fake()->sentence(15),
            'published_at' => now(),
            'views_count' => fake()->numberBetween(0, 500),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
