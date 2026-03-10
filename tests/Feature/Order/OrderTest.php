<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
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
    $this->address = Address::factory()->default()->create(['user_id' => $this->user->id]);
});

function addProductToCart($user, $seller, $category, $price = 250, $stock = 50): Product
{
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'seller_id' => $seller->id,
        'price' => $price,
        'stock' => $stock,
    ]);

    $cart = Cart::firstOrCreate(['user_id' => $user->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => $price,
    ]);

    return $product;
}

test('user can place order from cart', function () {
    $product = addProductToCart($this->user, $this->seller, $this->category, 250, 50);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
            'payment_method' => 'cod',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('orders', ['user_id' => $this->user->id]);
    $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
    $this->assertDatabaseHas('payments', ['method' => 'cod']);

    // Cart should be cleared
    expect(CartItem::count())->toBe(0);
});

test('stock is deducted after order', function () {
    $product = addProductToCart($this->user, $this->seller, $this->category, 250, 50);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    expect($product->fresh()->stock)->toBe(48);
});

test('cannot place order with empty cart', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $response->assertStatus(422);
});

test('free shipping above 500', function () {
    addProductToCart($this->user, $this->seller, $this->category, 300, 50);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $order = Order::first();
    // subtotal = 300 * 2 = 600 >= 500, so shipping = 0
    expect((float) $order->shipping_charge)->toBe(0.0);
});

test('shipping charge below 500', function () {
    addProductToCart($this->user, $this->seller, $this->category, 100, 50);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $order = Order::first();
    // subtotal = 100 * 2 = 200 < 500, so shipping = 50
    expect((float) $order->shipping_charge)->toBe(50.0);
});

test('user can view orders list', function () {
    addProductToCart($this->user, $this->seller, $this->category);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/orders');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('user can view order detail', function () {
    addProductToCart($this->user, $this->seller, $this->category);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $order = Order::first();

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/user/orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.order_number', $order->order_number);
});

test('user can cancel pending order', function () {
    addProductToCart($this->user, $this->seller, $this->category, 250, 50);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $order = Order::first();

    $response = $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/orders/{$order->id}/cancel", [
            'reason' => 'Changed my mind',
        ]);

    $response->assertStatus(200);
    expect($order->fresh()->status)->toBe('cancelled');
});

test('stock is restored after cancellation', function () {
    $product = addProductToCart($this->user, $this->seller, $this->category, 250, 50);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    expect($product->fresh()->stock)->toBe(48);

    $order = Order::first();
    $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/orders/{$order->id}/cancel", [
            'reason' => 'Test',
        ]);

    expect($product->fresh()->stock)->toBe(50);
});

test('cannot cancel shipped order', function () {
    addProductToCart($this->user, $this->seller, $this->category);

    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/orders', [
            'address_id' => $this->address->id,
        ]);

    $order = Order::first();
    $order->update(['status' => 'shipped']);

    $response = $this->withHeaders($this->headers)
        ->postJson("/api/v1/user/orders/{$order->id}/cancel", [
            'reason' => 'Too late',
        ]);

    $response->assertStatus(422);
});

test('order number is generated correctly', function () {
    $orderNumber = Order::generateOrderNumber();

    expect($orderNumber)->toStartWith('SNK-');
    expect(strlen($orderNumber))->toBeGreaterThan(10);
});
