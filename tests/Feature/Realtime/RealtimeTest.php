<?php

use App\Events\NewDeliveryAssignment;
use App\Events\NewSellerOrder;
use App\Events\OrderStatusUpdated;
use App\Models\Address;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryProfile;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

test('order status updated event has correct payload', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'status' => 'confirmed',
    ]);

    $event = new OrderStatusUpdated($order, 'pending');

    expect($event->broadcastWith())->toBe([
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'old_status' => 'pending',
        'new_status' => 'confirmed',
    ]);
    expect($event->broadcastAs())->toBe('order.status.updated');
});

test('order status updated event broadcasts on correct channel', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);

    $event = new OrderStatusUpdated($order, 'pending');
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-orders.{$user->id}");
});

test('order status updated event can be dispatched', function () {
    Event::fake([OrderStatusUpdated::class]);

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'status' => 'confirmed',
    ]);

    OrderStatusUpdated::dispatch($order, 'pending');

    Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
        return $event->order->id === $order->id && $event->oldStatus === 'pending';
    });
});

test('new seller order event has correct payload', function () {
    $event = new NewSellerOrder(
        sellerId: 5,
        orderNumber: 'ORD-12345',
        orderItemId: 10
    );

    expect($event->broadcastWith())->toBe([
        'seller_id' => 5,
        'order_number' => 'ORD-12345',
        'order_item_id' => 10,
    ]);
    expect($event->broadcastAs())->toBe('seller.new.order');
});

test('new seller order event broadcasts on correct channel', function () {
    $event = new NewSellerOrder(
        sellerId: 5,
        orderNumber: 'ORD-12345',
        orderItemId: 10
    );

    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe('private-seller.5');
});

test('new seller order event can be dispatched', function () {
    Event::fake([NewSellerOrder::class]);

    NewSellerOrder::dispatch(5, 'ORD-12345', 10);

    Event::assertDispatched(NewSellerOrder::class, function ($event) {
        return $event->sellerId === 5 && $event->orderNumber === 'ORD-12345';
    });
});

test('new delivery assignment event has correct payload', function () {
    $user = User::factory()->create();
    $user->assignRole('delivery_partner');
    $address = Address::factory()->create(['user_id' => $user->id]);
    $profile = DeliveryProfile::factory()->create(['user_id' => $user->id, 'status' => 'approved']);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $profile->id,
        'earning' => 50.00,
    ]);

    $event = new NewDeliveryAssignment($assignment);

    $payload = $event->broadcastWith();
    expect($payload['assignment_id'])->toBe($assignment->id);
    expect($payload['order_id'])->toBe($order->id);
    expect($payload['earning'])->toBe(50.0);
    expect($event->broadcastAs())->toBe('delivery.new.assignment');
});

test('new delivery assignment event broadcasts on correct channel', function () {
    $user = User::factory()->create();
    $user->assignRole('delivery_partner');
    $address = Address::factory()->create(['user_id' => $user->id]);
    $profile = DeliveryProfile::factory()->create(['user_id' => $user->id, 'status' => 'approved']);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);
    $assignment = DeliveryAssignment::factory()->create([
        'order_id' => $order->id,
        'delivery_profile_id' => $profile->id,
    ]);

    $event = new NewDeliveryAssignment($assignment);
    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1);
    expect($channels[0]->name)->toBe("private-delivery.{$user->id}");
});

test('channel authorization works for order owner', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-orders.{$user->id}",
        ])
        ->assertOk();
});

test('channel authorization rejects non-owner', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $other = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-orders.{$other->id}",
        ])
        ->assertForbidden();
});
