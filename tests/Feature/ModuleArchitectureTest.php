<?php

use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\Shared\DTOs\BaseDTO;
use App\Modules\Shared\Contracts\RepositoryInterface;
use App\Modules\Shared\Repositories\BaseRepository;

test('shared module classes exist', function () {
    expect(trait_exists(ApiResponse::class))->toBeTrue();
    expect(class_exists(BaseDTO::class))->toBeTrue();
    expect(interface_exists(RepositoryInterface::class))->toBeTrue();
    expect(class_exists(BaseRepository::class))->toBeTrue();
});

test('module service providers exist', function () {
    expect(class_exists(\App\Modules\Admin\AdminServiceProvider::class))->toBeTrue();
    expect(class_exists(\App\Modules\User\UserServiceProvider::class))->toBeTrue();
    expect(class_exists(\App\Modules\Seller\SellerServiceProvider::class))->toBeTrue();
    expect(class_exists(\App\Modules\Delivery\DeliveryServiceProvider::class))->toBeTrue();
});

test('module service provider is registered', function () {
    expect(class_exists(\App\Providers\ModuleServiceProvider::class))->toBeTrue();
});
