<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
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
    $this->seller->assignRole('seller');
    $this->category = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $this->category->id,
        'seller_id' => $this->seller->id,
        'price' => 250.00,
        'stock' => 50,
    ]);
});

test('user can view empty cart', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/cart');

    $response->assertStatus(200)
        ->assertJsonPath('data.total', 0);
});

test('user can add item to cart', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.total_items', 2);
});

test('adding same product increases quantity', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

    $cart = Cart::where('user_id', $this->user->id)->first();
    expect($cart->items->count())->toBe(1);
    expect($cart->items->first()->quantity)->toBe(5);
});

test('cannot add more than available stock', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

    $response->assertStatus(422);
});

test('cannot add out of stock product', function () {
    $outOfStock = Product::factory()->outOfStock()->create([
        'category_id' => $this->category->id,
        'seller_id' => $this->seller->id,
    ]);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $outOfStock->id,
            'quantity' => 1,
        ]);

    $response->assertStatus(422);
});

test('user can update cart item quantity', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $cartItem = CartItem::first();

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/user/cart/{$cartItem->id}", [
            'quantity' => 5,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.total_items', 5);
});

test('setting quantity to 0 removes item', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $cartItem = CartItem::first();

    $this->withHeaders($this->headers)
        ->putJson("/api/v1/user/cart/{$cartItem->id}", [
            'quantity' => 0,
        ]);

    expect(CartItem::count())->toBe(0);
});

test('user can remove item from cart', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $cartItem = CartItem::first();

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/user/cart/{$cartItem->id}");

    $response->assertStatus(200);
    expect(CartItem::count())->toBe(0);
});

test('user can clear cart', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $response = $this->withHeaders($this->headers)
        ->deleteJson('/api/v1/user/cart');

    $response->assertStatus(200);
    expect(CartItem::count())->toBe(0);
});

test('cart total is calculated correctly', function () {
    $product2 = Product::factory()->create([
        'category_id' => $this->category->id,
        'seller_id' => $this->seller->id,
        'price' => 100.00,
        'stock' => 50,
    ]);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/cart', [
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/cart');

    $response->assertStatus(200);
    // 2 * 250 + 3 * 100 = 800
    expect((float) $response->json('data.total'))->toBe(800.0);
});
