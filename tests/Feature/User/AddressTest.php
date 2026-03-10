<?php

use App\Models\Address;
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
});

test('user can list addresses', function () {
    Address::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/addresses');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('user can create address', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/addresses', [
            'name' => 'John Doe',
            'phone' => '9876543210',
            'address_line_1' => '123 Main St',
            'city' => 'Patna',
            'state' => 'Bihar',
            'pincode' => '800001',
            'type' => 'home',
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('addresses', [
        'user_id' => $this->user->id,
        'city' => 'Patna',
    ]);
});

test('first address is automatically set as default', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/addresses', [
            'name' => 'John Doe',
            'phone' => '9876543210',
            'address_line_1' => '123 Main St',
            'city' => 'Patna',
            'state' => 'Bihar',
            'pincode' => '800001',
        ]);

    $address = Address::where('user_id', $this->user->id)->first();
    expect($address->is_default)->toBeTrue();
});

test('user can view single address', function () {
    $address = Address::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/user/addresses/{$address->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $address->id);
});

test('user cannot view another users address', function () {
    $otherUser = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/user/addresses/{$address->id}");

    $response->assertStatus(404);
});

test('user can update address', function () {
    $address = Address::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/user/addresses/{$address->id}", [
            'name' => 'Updated Name',
            'phone' => '9876543210',
            'address_line_1' => 'Updated Street',
            'city' => 'Muzaffarpur',
            'state' => 'Bihar',
            'pincode' => '842001',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.city', 'Muzaffarpur');
});

test('user can delete address', function () {
    $address = Address::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/user/addresses/{$address->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
});

test('user can set default address', function () {
    $address1 = Address::factory()->default()->create(['user_id' => $this->user->id]);
    $address2 = Address::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/v1/user/addresses/{$address2->id}/default");

    $response->assertStatus(200);
    expect($address2->fresh()->is_default)->toBeTrue();
    expect($address1->fresh()->is_default)->toBeFalse();
});

test('address validation works', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/user/addresses', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'phone', 'address_line_1', 'city', 'state', 'pincode']);
});

test('deleting default address promotes next address', function () {
    $address1 = Address::factory()->default()->create(['user_id' => $this->user->id]);
    $address2 = Address::factory()->create(['user_id' => $this->user->id]);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/user/addresses/{$address1->id}");

    expect($address2->fresh()->is_default)->toBeTrue();
});
