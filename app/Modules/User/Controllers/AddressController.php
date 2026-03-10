<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\Requests\AddressRequest;
use App\Modules\User\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
