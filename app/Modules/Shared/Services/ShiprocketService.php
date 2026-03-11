<?php

namespace App\Modules\Shared\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ShiprocketService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.shiprocket.base_url', 'https://apiv2.shiprocket.in/v1/external');
    }

    /**
     * Get auth token (cached for 10 days).
     */
    public function getToken(): string
    {
        return Cache::remember('shiprocket_token', 864000, function () {
            $response = Http::post("{$this->baseUrl}/auth/login", [
                'email' => config('services.shiprocket.email'),
                'password' => config('services.shiprocket.password'),
            ]);

            if ($response->failed()) {
                throw new \RuntimeException('Shiprocket authentication failed.');
            }

            return $response->json('token');
        });
    }

    /**
     * Create a Shiprocket order from our order.
     */
    public function createOrder(Order $order): array
    {
        $order->loadMissing(['items.product', 'user']);

        $items = $order->items->map(fn ($item) => [
            'name' => $item->product_name,
            'sku' => $item->sku,
            'units' => $item->quantity,
            'selling_price' => (float) $item->unit_price,
        ])->toArray();

        $shippingAddress = $order->shipping_address;

        $response = $this->request('post', '/orders/create/adhoc', [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => 'Primary',
            'billing_customer_name' => $shippingAddress['name'] ?? $order->user->name,
            'billing_last_name' => '',
            'billing_address' => $shippingAddress['address_line_1'] ?? '',
            'billing_address_2' => $shippingAddress['address_line_2'] ?? '',
            'billing_city' => $shippingAddress['city'] ?? '',
            'billing_pincode' => $shippingAddress['pincode'] ?? '',
            'billing_state' => $shippingAddress['state'] ?? '',
            'billing_country' => 'India',
            'billing_email' => $order->user->email,
            'billing_phone' => $shippingAddress['phone'] ?? $order->user->phone,
            'shipping_is_billing' => true,
            'order_items' => $items,
            'payment_method' => $order->payment?->method === 'cod' ? 'COD' : 'Prepaid',
            'sub_total' => (float) $order->total,
            'length' => 20,
            'breadth' => 15,
            'height' => 10,
            'weight' => 0.5,
        ]);

        if (isset($response['order_id'])) {
            $order->update([
                'shiprocket_order_id' => $response['order_id'],
                'shiprocket_shipment_id' => $response['shipment_id'] ?? null,
            ]);
        }

        return $response;
    }

    /**
     * Get tracking data for an order.
     */
    public function trackOrder(Order $order): array
    {
        if (!$order->shiprocket_shipment_id) {
            return ['status' => 'No shipment created yet.'];
        }

        return $this->request('get', "/courier/track/shipment/{$order->shiprocket_shipment_id}");
    }

    /**
     * Cancel a Shiprocket order.
     */
    public function cancelOrder(Order $order): array
    {
        if (!$order->shiprocket_order_id) {
            return ['status' => 'No Shiprocket order to cancel.'];
        }

        return $this->request('post', '/orders/cancel', [
            'ids' => [$order->shiprocket_order_id],
        ]);
    }

    /**
     * Check serviceability for a pincode.
     */
    public function checkServiceability(string $pickupPincode, string $deliveryPincode, float $weight = 0.5): array
    {
        return $this->request('get', '/courier/serviceability/', [
            'pickup_postcode' => $pickupPincode,
            'delivery_postcode' => $deliveryPincode,
            'weight' => $weight,
            'cod' => 1,
        ]);
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->getToken();

        $request = Http::withToken($token);

        $response = match ($method) {
            'get' => $request->get("{$this->baseUrl}{$endpoint}", $data),
            'post' => $request->post("{$this->baseUrl}{$endpoint}", $data),
            default => throw new \InvalidArgumentException("Unsupported method: {$method}"),
        };

        return $response->json() ?? [];
    }
}
