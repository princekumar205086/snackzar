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

test('user can view profile', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $this->user->id)
        ->assertJsonPath('data.email', $this->user->email);
});

test('user can update profile', function () {
    $response = $this->withHeaders($this->headers)
        ->putJson('/api/v1/user/profile', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Updated Name',
    ]);
});

test('user can change password', function () {
    $response = $this->withHeaders($this->headers)
        ->putJson('/api/v1/user/profile/password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertStatus(200);
});

test('user cannot change password with wrong current password', function () {
    $response = $this->withHeaders($this->headers)
        ->putJson('/api/v1/user/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

    $response->assertStatus(422);
});

test('user can delete account', function () {
    $response = $this->withHeaders($this->headers)
        ->deleteJson('/api/v1/user/profile');

    $response->assertStatus(200);
    $this->assertSoftDeleted('users', ['id' => $this->user->id]);
});

test('unauthenticated user cannot access profile', function () {
    $response = $this->getJson('/api/v1/user/profile');

    $response->assertStatus(401);
});
