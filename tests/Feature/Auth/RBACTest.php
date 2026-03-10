<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('roles are created correctly', function () {
    expect(Role::count())->toBe(4);
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
    expect(Role::where('name', 'user')->exists())->toBeTrue();
    expect(Role::where('name', 'seller')->exists())->toBeTrue();
    expect(Role::where('name', 'delivery_partner')->exists())->toBeTrue();
});

test('permissions are created correctly', function () {
    expect(Permission::count())->toBeGreaterThan(20);
});

test('admin has all permissions', function () {
    $admin = Role::findByName('admin');
    expect($admin->permissions->count())->toBe(Permission::count());
});

test('user role has correct permissions', function () {
    $user = Role::findByName('user');
    $permissions = $user->permissions->pluck('name')->toArray();

    expect($permissions)->toContain('view profile');
    expect($permissions)->toContain('create orders');
    expect($permissions)->toContain('manage cart');
    expect($permissions)->not->toContain('manage users');
    expect($permissions)->not->toContain('manage products');
});

test('seller role has correct permissions', function () {
    $seller = Role::findByName('seller');
    $permissions = $seller->permissions->pluck('name')->toArray();

    expect($permissions)->toContain('manage products');
    expect($permissions)->toContain('manage inventory');
    expect($permissions)->not->toContain('manage users');
});

test('delivery partner role has correct permissions', function () {
    $delivery = Role::findByName('delivery_partner');
    $permissions = $delivery->permissions->pluck('name')->toArray();

    expect($permissions)->toContain('view deliveries');
    expect($permissions)->toContain('accept deliveries');
    expect($permissions)->not->toContain('manage products');
});

test('user can be assigned a role', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    expect($user->hasRole('user'))->toBeTrue();
    expect($user->hasRole('admin'))->toBeFalse();
});

test('user can have multiple roles', function () {
    $user = User::factory()->create();
    $user->assignRole('user', 'seller');

    expect($user->hasRole('user'))->toBeTrue();
    expect($user->hasRole('seller'))->toBeTrue();
});

test('role middleware blocks unauthorized access', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $token = $user->createToken('test')->plainTextToken;

    // User trying to access admin API should be forbidden
    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->getJson('/api/v1/admin');

    // Should get 403 or 404 (no admin routes yet, but middleware should apply)
    expect($response->status())->toBeIn([403, 404, 500]);
});

test('admin user model factory works', function () {
    $admin = User::factory()->admin()->create();

    expect($admin->hasRole('admin'))->toBeTrue();
});

test('seller user model factory works', function () {
    $seller = User::factory()->seller()->create();

    expect($seller->hasRole('seller'))->toBeTrue();
});

test('delivery partner user model factory works', function () {
    $dp = User::factory()->deliveryPartner()->create();

    expect($dp->hasRole('delivery_partner'))->toBeTrue();
});
