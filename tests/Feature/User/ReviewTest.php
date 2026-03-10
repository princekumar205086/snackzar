<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();

    $this->user = User::factory()->create();
    $this->user->assignRole('user');
    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];

    $this->seller = User::factory()->create();
    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'seller_id' => $this->seller->id,
    ]);
});

test('can list approved reviews for a product', function () {
    Review::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'rating' => 5,
        'comment' => 'Great product!',
        'is_approved' => true,
    ]);

    Review::create([
        'user_id' => User::factory()->create()->id,
        'product_id' => $this->product->id,
        'rating' => 3,
        'comment' => 'Pending review',
        'is_approved' => false,
    ]);

    $response = $this->getJson("/api/v1/user/products/{$this->product->id}/reviews");

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('user can submit a review', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Amazing makhana!',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('reviews', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'rating' => 5,
        'is_approved' => false,
    ]);
});

test('user cannot submit duplicate review', function () {
    Review::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'rating' => 5,
        'comment' => 'First review',
    ]);

    $response = $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/products/{$this->product->id}/reviews", [
            'rating' => 4,
            'comment' => 'Second review',
        ]);

    $response->assertStatus(422);
});

test('user can update their review', function () {
    $review = Review::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'rating' => 3,
        'comment' => 'OK product',
    ]);

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/user/reviews/{$review->id}", [
            'rating' => 5,
            'comment' => 'Actually great!',
        ]);

    $response->assertStatus(200);
    expect($review->fresh()->rating)->toBe(5);
});

test('user cannot update another users review', function () {
    $otherUser = User::factory()->create();
    $review = Review::create([
        'user_id' => $otherUser->id,
        'product_id' => $this->product->id,
        'rating' => 3,
        'comment' => 'Meh',
    ]);

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/user/reviews/{$review->id}", [
            'rating' => 1,
        ]);

    $response->assertStatus(403);
});

test('user can delete their review', function () {
    $review = Review::create([
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
        'rating' => 3,
    ]);

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/user/reviews/{$review->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});

test('review validation requires rating', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/products/{$this->product->id}/reviews", [
            'comment' => 'No rating',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('rating');
});

test('rating must be between 1 and 5', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/products/{$this->product->id}/reviews", [
            'rating' => 6,
        ]);

    $response->assertStatus(422);
});
