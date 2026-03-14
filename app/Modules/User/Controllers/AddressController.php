<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\Requests\AddressRequest;
use App\Modules\User\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * @group Addresses
 *
 * APIs for managing user delivery addresses (CRUD, set default).
 */
class AddressController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AddressService $addressService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->list($request->user());

        return $this->success($addresses);
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $address = $this->addressService->store(
            $request->user(),
            $request->validated()
        );

        return $this->created($address, 'Address added successfully.');
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Address not found.', 404);
        }

        return $this->success($address);
    }

    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Address not found.', 404);
        }

        $address = $this->addressService->update($address, $request->validated());

        return $this->success($address, 'Address updated successfully.');
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Address not found.', 404);
        }

        $this->addressService->delete($address);

        return $this->noContent('Address deleted successfully.');
    }

    public function setDefault(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->error('Address not found.', 404);
        }

        $address = $this->addressService->setDefault($address);

        return $this->success($address, 'Default address updated.');
    }

    public function lookupPincode(string $pincode): JsonResponse
    {
        if (!preg_match('/^\d{6}$/', $pincode)) {
            return $this->error('Invalid pincode format.', 422);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 250)
                ->get("https://api.postalpincode.in/pincode/{$pincode}");
        } catch (ConnectionException $e) {
            return $this->success([
                'pincode' => $pincode,
                'country' => 'India',
                'state' => null,
                'district' => null,
                'city' => null,
                'post_offices' => [],
                'lookup_fallback' => true,
            ], 'Pincode lookup service is temporarily unavailable. Please enter city, district, and state manually.');
        }

        if (! $response->ok()) {
            return $this->success([
                'pincode' => $pincode,
                'country' => 'India',
                'state' => null,
                'district' => null,
                'city' => null,
                'post_offices' => [],
                'lookup_fallback' => true,
            ], 'Pincode lookup service is temporarily unavailable. Please enter city, district, and state manually.');
        }

        $body = $response->json();
        $result = is_array($body) ? ($body[0] ?? null) : null;
        $postOffices = $result['PostOffice'] ?? [];

        if (! is_array($postOffices) || count($postOffices) === 0) {
            return $this->error('No address details found for this pincode.', 404);
        }

        $primary = $postOffices[0];

        return $this->success([
            'pincode' => $pincode,
            'country' => $primary['Country'] ?? 'India',
            'state' => $primary['State'] ?? null,
            'district' => $primary['District'] ?? null,
            'city' => $primary['District'] ?? null,
            'post_offices' => array_values(array_filter(array_map(
                fn ($office) => $office['Name'] ?? null,
                $postOffices
            ))),
        ]);
    }
}
