<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();

    $this->seller = User::factory()->create();
    $this->seller->assignRole('seller');
    $this->sellerProfile = SellerProfile::factory()->create(['user_id' => $this->seller->id]);
    $this->token = $this->seller->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];

    $this->category = Category::factory()->create();
});

// -- Profile Tests --

test('seller can view profile', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/seller/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.business_name', $this->sellerProfile->business_name);
});

test('seller can update profile', function () {
    $response = $this->withHeaders($this->headers)
        ->putJson('/api/v1/seller/profile', [
            'business_name' => 'Updated Business',
            'upi_id' => 'seller@upi',
        ]);

    $response->assertStatus(200);
    expect($this->sellerProfile->fresh()->business_name)->toBe('Updated Business');
});

test('seller can create profile', function () {
    $newSeller = User::factory()->create();
    $newSeller->assignRole('seller');
    $token = $newSeller->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/v1/seller/profile', [
            'business_name' => 'New Business',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('seller_profiles', [
        'user_id' => $newSeller->id,
        'business_name' => 'New Business',
        'status' => 'pending',
    ]);
});

test('seller cannot create duplicate profile', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/seller/profile', [
            'business_name' => 'Duplicate',
        ]);

    $response->assertStatus(422);
});

// -- Dashboard Tests --

test('seller can view dashboard', function () {
    Product::factory()->count(3)->create([
        'seller_id' => $this->seller->id,
        'category_id' => $this->category->id,
    ]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/seller/dashboard');

    $response->assertStatus(200)
        ->assertJsonPath('data.total_products', 3);
});

// -- Product Tests --

test('seller can list their products', function () {
    Product::factory()->count(3)->create([
        'seller_id' => $this->seller->id,
        'category_id' => $this->category->id,
    ]);

    // Another seller's product
    Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/seller/products');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(3);
});

test('seller can create product', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/seller/products', [
            'category_id' => $this->category->id,
            'name' => 'Makhana Premium',
            'sku' => 'MKH-001',
            'price' => 299,
            'stock' => 100,
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('products', [
        'seller_id' => $this->seller->id,
        'name' => 'Makhana Premium',
        'sku' => 'MKH-001',
    ]);
});

test('seller can update their product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'category_id' => $this->category->id,
    ]);

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/seller/products/{$product->id}", [
            'price' => 399,
            'stock' => 200,
        ]);

    $response->assertStatus(200);
    expect((float) $product->fresh()->price)->toBe(399.0);
});

test('seller can delete their product', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'category_id' => $this->category->id,
    ]);

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/seller/products/{$product->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('seller cannot access other sellers product', function () {
    $otherProduct = Product::factory()->create(['category_id' => $this->category->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/seller/products/{$otherProduct->id}");

    $response->assertStatus(404);
});

test('seller can toggle product active status', function () {
    $product = Product::factory()->create([
        'seller_id' => $this->seller->id,
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/seller/products/{$product->id}/toggle-active");

    $response->assertStatus(200);
    expect($product->fresh()->is_active)->toBeFalse();
});

// -- Payout Tests --

test('seller can view payouts', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/seller/payouts');

    $response->assertStatus(200);
});

test('seller can request payout', function () {
    $this->sellerProfile->update(['pending_payout' => 5000]);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/seller/payouts', [
            'amount' => 1000,
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('seller_payouts', [
        'seller_profile_id' => $this->sellerProfile->id,
        'amount' => 1000,
        'status' => 'pending',
    ]);
    expect((float) $this->sellerProfile->fresh()->pending_payout)->toBe(4000.0);
});

test('seller cannot request payout exceeding balance', function () {
    $this->sellerProfile->update(['pending_payout' => 500]);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/seller/payouts', [
            'amount' => 1000,
        ]);

    $response->assertStatus(422);
});

test('seller cannot request payout below minimum', function () {
    $this->sellerProfile->update(['pending_payout' => 5000]);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/seller/payouts', [
            'amount' => 50,
        ]);

    $response->assertStatus(422);
});

// -- Access Control --

test('non-seller cannot access seller routes', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/v1/seller/dashboard');

    $response->assertStatus(403);
});
