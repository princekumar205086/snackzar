<?php

use App\Models\Address;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryProfile;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();

    $this->deliveryUser = User::factory()->create();
    $this->deliveryUser->assignRole('delivery_partner');
    $this->deliveryProfile = DeliveryProfile::factory()->create(['user_id' => $this->deliveryUser->id]);
    $this->token = $this->deliveryUser->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];

    $this->customer = User::factory()->create();
    $this->customer->assignRole('user');
    $this->address = Address::factory()->default()->create(['user_id' => $this->customer->id]);
});

// -- Profile Tests --

test('delivery partner can view profile', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/delivery/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.vehicle_type', $this->deliveryProfile->vehicle_type);
});

test('delivery partner can create profile', function () {
    $newUser = User::factory()->create();
    $newUser->assignRole('delivery_partner');
    $token = $newUser->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('/api/v1/delivery/profile', [
            'vehicle_type' => 'bike',
            'vehicle_number' => 'BR-01-AB-1234',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('delivery_profiles', [
        'user_id' => $newUser->id,
        'vehicle_type' => 'bike',
        'status' => 'pending',
    ]);
});

test('delivery partner can update profile', function () {
    $response = $this->withHeaders($this->headers)
        ->putJson('/api/v1/delivery/profile', [
            'vehicle_type' => 'scooter',
        ]);

    $response->assertStatus(200);
    expect($this->deliveryProfile->fresh()->vehicle_type)->toBe('scooter');
});

test('delivery partner can toggle availability', function () {
    $was = $this->deliveryProfile->is_available;

    $response = $this->withHeaders($this->headers)
        ->patchJson('/api/v1/delivery/availability');

    $response->assertStatus(200);
    expect($this->deliveryProfile->fresh()->is_available)->toBe(!$was);
});

test('delivery partner can update location', function () {
    $response = $this->withHeaders($this->headers)
        ->patchJson('/api/v1/delivery/location', [
            'latitude' => 25.6120,
            'longitude' => 85.1580,
        ]);

    $response->assertStatus(200);
    $profile = $this->deliveryProfile->fresh();
    expect((float) $profile->current_latitude)->toBe(25.612);
});

test('delivery partner can view dashboard', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/delivery/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['profile', 'total_earnings', 'total_deliveries', 'is_available']]);
});

// -- Assignment Tests --

test('delivery partner can list assignments', function () {
    $order = Order::factory()->create([
        'user_id' => $this->customer->id,
        'address_id' => $this->address->id,
    ]);
    DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $this->deliveryProfile->id,
    ]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/delivery/assignments');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('delivery partner can view assignment detail', function () {
    $order = Order::factory()->create([
        'user_id' => $this->customer->id,
        'address_id' => $this->address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $this->deliveryProfile->id,
    ]);

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/delivery/assignments/{$assignment->id}");

    $response->assertStatus(200);
});

test('delivery partner can accept assignment', function () {
    $order = Order::factory()->create([
        'user_id' => $this->customer->id,
        'address_id' => $this->address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $this->deliveryProfile->id,
        'status' => 'assigned',
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/delivery/assignments/{$assignment->id}/accept");

    $response->assertStatus(200);
    expect($assignment->fresh()->status)->toBe('accepted');
    expect($assignment->fresh()->accepted_at)->not->toBeNull();
});

test('delivery partner can pick up order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->customer->id,
        'address_id' => $this->address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $this->deliveryProfile->id,
        'status' => 'accepted',
        'accepted_at' => now(),
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/delivery/assignments/{$assignment->id}/pickup");

    $response->assertStatus(200);
    expect($assignment->fresh()->status)->toBe('picked_up');
    expect($order->fresh()->status)->toBe('out_for_delivery');
});

test('delivery partner can mark order as delivered', function () {
    $order = Order::factory()->create([
        'user_id' => $this->customer->id,
        'address_id' => $this->address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $this->deliveryProfile->id,
        'status' => 'picked_up',
        'earning' => 50,
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/delivery/assignments/{$assignment->id}/deliver");

    $response->assertStatus(200);
    expect($assignment->fresh()->status)->toBe('delivered');
    expect($order->fresh()->status)->toBe('delivered');
    expect((float) $this->deliveryProfile->fresh()->total_earnings)->toBe(50.0);
    expect($this->deliveryProfile->fresh()->total_deliveries)->toBe(1);
});

test('cannot accept already accepted assignment', function () {
    $order = Order::factory()->create([
        'user_id' => $this->customer->id,
        'address_id' => $this->address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $this->deliveryProfile->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/delivery/assignments/{$assignment->id}/accept");

    $response->assertStatus(422);
});

// -- Access Control --

test('non-delivery user cannot access delivery routes', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('/api/v1/delivery/dashboard');

    $response->assertStatus(403);
});
