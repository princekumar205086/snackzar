<?php

use App\Models\User;
use App\Services\Sms\InfobipSmsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

test('user can register via API', function () {
    $response = $this->postJson('/api/v1/user/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['user', 'token'],
        ]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('user cannot register with existing email', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/v1/user/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('user can login via API', function () {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $user->assignRole('user');

    $response = $this->postJson('/api/v1/user/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => ['user', 'token'],
        ]);
});

test('user cannot login with wrong credentials', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/v1/user/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

test('banned user cannot login', function () {
    $user = User::factory()->banned()->create(['email' => 'banned@example.com']);

    $response = $this->postJson('/api/v1/user/auth/login', [
        'email' => 'banned@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson('/api/v1/user/auth/logout');

    $response->assertStatus(200);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->getJson('/api/v1/user/auth/user');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id);
});

test('unauthenticated user cannot access protected routes', function () {
    $response = $this->getJson('/api/v1/user/auth/user');

    $response->assertStatus(401);
});

test('user can request OTP', function () {
    $this->mock(InfobipSmsService::class, function ($mock) {
        $mock->shouldReceive('sendOtp')->once()->andReturnNull();
    });

    $response = $this->postJson('/api/v1/user/auth/otp/send', [
        'phone' => '9876543210',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('otps', [
        'identifier' => '9876543210',
        'type' => 'phone',
    ]);
});

test('user can request forgot password', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->postJson('/api/v1/user/auth/forgot-password', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(200);
});

test('registration assigns user role', function () {
    $response = $this->postJson('/api/v1/user/auth/register', [
        'name' => 'Test User',
        'email' => 'roletest@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'roletest@example.com')->first();
    expect($user->hasRole('user'))->toBeTrue();
});

test('admin web login redirects to admin dashboard', function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    $admin = User::factory()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
    ]);
    $admin->assignRole('admin');

    $response = $this->post('/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin/dashboard');
    $this->assertAuthenticatedAs($admin);
});

test('customer dashboard redirects admin back to admin panel', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertRedirect('/admin/dashboard');
});

test('homepage redirects admin to admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/');

    $response->assertRedirect('/admin/dashboard');
});

test('admin cannot access customer cart api', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $token = $admin->createToken('admin-test')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ])->getJson('/api/v1/user/cart');

    $response->assertStatus(403)
        ->assertJsonPath('message', 'Admin accounts cannot use customer APIs.');
});
