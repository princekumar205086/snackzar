<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\DeliveryProfile;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->token = $this->admin->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];
});

// -- Dashboard --

test('admin can view dashboard stats', function () {
    User::factory()->count(3)->create();
    Category::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['total_users', 'total_orders', 'total_revenue']]);
});

// -- User Management --

test('admin can list users', function () {
    User::factory()->count(5)->create();

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/users');

    $response->assertStatus(200);
    // 5 users + admin = 6 total
    expect($response->json('data.total'))->toBe(6);
});

test('admin can filter users by role', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/users?role=seller');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('admin can view user detail', function () {
    $user = User::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/admin/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id);
});

test('admin can ban user', function () {
    $user = User::factory()->create(['status' => 'active']);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/admin/users/{$user->id}/status", [
            'status' => 'banned',
        ]);

    $response->assertStatus(200);
    expect($user->fresh()->status)->toBe('banned');
});

// -- Order Management --

test('admin can list orders', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    Order::factory()->count(3)->create(['user_id' => $user->id, 'address_id' => $address->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/orders');

    $response->assertStatus(200);
    expect($response->json('data.total'))->toBe(3);
});

test('admin can view order detail', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create(['user_id' => $user->id, 'address_id' => $address->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/admin/orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.order_number', $order->order_number);
});

test('admin can update order status', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'status' => 'pending',
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => 'confirmed',
        ]);

    $response->assertStatus(200);
    expect($order->fresh()->status)->toBe('confirmed');
});

test('admin cannot make invalid order transition', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'status' => 'pending',
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/admin/orders/{$order->id}/status", [
            'status' => 'delivered',
        ]);

    $response->assertStatus(422);
});

// -- Seller Management --

test('admin can list sellers', function () {
    $seller = User::factory()->create();
    SellerProfile::factory()->create(['user_id' => $seller->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/sellers');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('admin can approve seller', function () {
    $seller = User::factory()->create();
    $profile = SellerProfile::factory()->pending()->create(['user_id' => $seller->id]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/admin/sellers/{$profile->id}/approve");

    $response->assertStatus(200);
    expect($profile->fresh()->status)->toBe('approved');
    expect($profile->fresh()->approved_at)->not->toBeNull();
});

test('admin can suspend seller', function () {
    $seller = User::factory()->create();
    $profile = SellerProfile::factory()->create(['user_id' => $seller->id]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/admin/sellers/{$profile->id}/suspend");

    $response->assertStatus(200);
    expect($profile->fresh()->status)->toBe('suspended');
});

// -- Delivery Partner Management --

test('admin can list delivery partners', function () {
    $dpUser = User::factory()->create();
    DeliveryProfile::factory()->create(['user_id' => $dpUser->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/delivery-partners');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('admin can approve delivery partner', function () {
    $dpUser = User::factory()->create();
    $profile = DeliveryProfile::factory()->pending()->create(['user_id' => $dpUser->id]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/admin/delivery-partners/{$profile->id}/approve");

    $response->assertStatus(200);
    expect($profile->fresh()->status)->toBe('approved');
});

test('admin can assign delivery to order', function () {
    $dpUser = User::factory()->create();
    $dpProfile = DeliveryProfile::factory()->create(['user_id' => $dpUser->id]);

    $customer = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $customer->id]);
    $order = Order::factory()->create(['user_id' => $customer->id, 'address_id' => $address->id]);

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/admin/delivery/assign', [
            'order_id' => $order->id,
            'delivery_profile_id' => $dpProfile->id,
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('delivery_assignments', [
        'order_id' => $order->id,
        'delivery_profile_id' => $dpProfile->id,
    ]);
});

// -- Category Management --

test('admin can create category', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/admin/categories', [
            'name' => 'Makhana',
            'slug' => 'makhana',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('categories', ['name' => 'Makhana', 'slug' => 'makhana']);
});

test('admin can update category', function () {
    $category = Category::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200);
    expect($category->fresh()->name)->toBe('Updated Name');
});

test('admin can delete category', function () {
    $category = Category::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/admin/categories/{$category->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

// -- Access Control --

test('non-admin cannot access admin routes', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/v1/admin/dashboard');

    $response->assertStatus(403);
});
