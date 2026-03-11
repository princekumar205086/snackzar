<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Modules\Shared\Services\ShiprocketService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

test('shiprocket service can create order', function () {
    Http::fake([
        '*/auth/login' => Http::response(['token' => 'fake-token'], 200),
        '*/orders/create/adhoc' => Http::response([
            'order_id' => 12345,
            'shipment_id' => 67890,
            'status' => 'NEW',
        ], 200),
    ]);

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
    ]);

    $service = new ShiprocketService();
    $result = $service->createOrder($order);

    expect($result['order_id'])->toBe(12345);
    expect((int) $order->fresh()->shiprocket_order_id)->toBe(12345);
});

test('shiprocket service can track order', function () {
    Http::fake([
        '*/auth/login' => Http::response(['token' => 'fake-token'], 200),
        '*/courier/track/shipment/*' => Http::response([
            'tracking_data' => ['shipment_status' => 'In Transit'],
        ], 200),
    ]);

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'shiprocket_shipment_id' => '67890',
    ]);

    $service = new ShiprocketService();
    $result = $service->trackOrder($order);

    expect($result)->toHaveKey('tracking_data');
});

test('shiprocket returns message when no shipment exists', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'shiprocket_shipment_id' => null,
    ]);

    $service = new ShiprocketService();
    $result = $service->trackOrder($order);

    expect($result['status'])->toBe('No shipment created yet.');
});

test('shiprocket can cancel order', function () {
    Http::fake([
        '*/auth/login' => Http::response(['token' => 'fake-token'], 200),
        '*/orders/cancel' => Http::response(['status' => 200], 200),
    ]);

    $user = User::factory()->create();
    $address = Address::factory()->create(['user_id' => $user->id]);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'address_id' => $address->id,
        'shiprocket_order_id' => 12345,
    ]);

    $service = new ShiprocketService();
    $result = $service->cancelOrder($order);

    expect($result['status'])->toBe(200);
});

test('shiprocket can check serviceability', function () {
    Http::fake([
        '*/auth/login' => Http::response(['token' => 'fake-token'], 200),
        '*/courier/serviceability*' => Http::response([
            'data' => ['available_courier_companies' => [['name' => 'Delhivery']]],
        ], 200),
    ]);

    $service = new ShiprocketService();
    $result = $service->checkServiceability('800001', '110001');

    expect($result)->toHaveKey('data');
});
