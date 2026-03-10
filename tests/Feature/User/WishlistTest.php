<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
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

test('user can view wishlist', function () {
    Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/wishlist');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
});

test('user can add product to wishlist', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/wishlist', [
            'product_id' => $this->product->id,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.added', true);

    $this->assertDatabaseHas('wishlists', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);
});

test('toggling wishlist removes if already added', function () {
    Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/wishlist', [
            'product_id' => $this->product->id,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.added', false);

    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);
});

test('user can remove from wishlist', function () {
    Wishlist::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/user/wishlist/{$this->product->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('wishlists', [
        'user_id' => $this->user->id,
        'product_id' => $this->product->id,
    ]);
});
