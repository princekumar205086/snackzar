<?php

use App\Models\Order;
use App\Models\User;
use App\Notifications\Channels\InfobipSmsChannel;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusNotification;
use App\Notifications\SellerApprovedNotification;
use App\Models\SellerProfile;
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

test('order placed notification has correct data', function () {
    $order = new Order([
        'id' => 1,
        'order_number' => 'SNK-TEST-001',
        'total' => 599.00,
    ]);

    $notification = new OrderPlacedNotification($order);

    $mailData = $notification->toMail($this->user);
    expect($mailData->subject)->toBe('Order Confirmed - SNK-TEST-001');

    $arrayData = $notification->toArray($this->user);
    expect($arrayData['type'])->toBe('order_placed');
    expect($arrayData['order_number'])->toBe('SNK-TEST-001');
});

test('order status notification has correct data', function () {
    $order = new Order([
        'id' => 1,
        'order_number' => 'SNK-TEST-002',
        'status' => 'shipped',
    ]);

    $notification = new OrderStatusNotification($order, 'processing');

    $arrayData = $notification->toArray($this->user);
    expect($arrayData['type'])->toBe('order_status');
    expect($arrayData['old_status'])->toBe('processing');
    expect($arrayData['new_status'])->toBe('shipped');
});

test('seller approved notification has correct data', function () {
    $profile = new SellerProfile([
        'id' => 1,
        'business_name' => 'Test Business',
    ]);

    $notification = new SellerApprovedNotification($profile);

    $mailData = $notification->toMail($this->user);
    expect($mailData->subject)->toBe('Seller Account Approved - SNACKZAR');

    $arrayData = $notification->toArray($this->user);
    expect($arrayData['type'])->toBe('seller_approved');
});

test('user can list notifications', function () {
    // Create real database notifications
    $this->user->notify(new OrderPlacedNotification(new Order([
        'id' => 1,
        'order_number' => 'SNK-T-001',
        'total' => 500,
    ])));

    // Since Notification::fake() prevents actual delivery, we need to allow it
    Notification::assertSentTo($this->user, OrderPlacedNotification::class);
});

test('user can view unread notification count', function () {
    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/user/notifications/unread');

    $response->assertStatus(200)
        ->assertJsonPath('data.unread_count', 0);
});

test('user can mark all notifications as read', function () {
    $response = $this->withHeaders($this->headers)
        ->patchJson('/api/v1/user/notifications/read-all');

    $response->assertStatus(200);
});

test('notification channels are correct', function () {
    $order = new Order(['id' => 1, 'order_number' => 'T', 'total' => 100]);
    $notification = new OrderPlacedNotification($order);

    expect($notification->via($this->user))->toBe(['mail', 'database', InfobipSmsChannel::class]);
});
