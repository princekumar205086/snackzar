<?php

use App\Modules\Shared\Traits\ApiResponse;

test('api response trait returns success response', function () {
    $controller = new class {
        use ApiResponse;

        public function testSuccess()
        {
            return $this->success(['key' => 'value'], 'Test success');
        }

        public function testError()
        {
            return $this->error('Test error', 422);
        }

        public function testCreated()
        {
            return $this->created(['id' => 1], 'Item created');
        }
    };

    $successResponse = $controller->testSuccess();
    expect($successResponse->getStatusCode())->toBe(200);
    $successData = $successResponse->getData(true);
    expect($successData['success'])->toBeTrue();
    expect($successData['message'])->toBe('Test success');
    expect($successData['data'])->toBe(['key' => 'value']);

    $errorResponse = $controller->testError();
    expect($errorResponse->getStatusCode())->toBe(422);
    $errorData = $errorResponse->getData(true);
    expect($errorData['success'])->toBeFalse();

    $createdResponse = $controller->testCreated();
    expect($createdResponse->getStatusCode())->toBe(201);
});
