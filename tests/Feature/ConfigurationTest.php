<?php

test('application uses correct name', function () {
    expect(config('app.name'))->toBe('Snackzar');
});

test('snackzar config is loaded', function () {
    expect(config('snackzar'))->toBeArray();
    expect(config('snackzar.otp.length'))->toBe(6);
    expect(config('snackzar.otp.expiry_minutes'))->toBe(10);
    expect(config('snackzar.pagination.per_page'))->toBe(15);
});

test('sanctum config exists', function () {
    expect(config('sanctum'))->toBeArray();
});

test('permission config exists', function () {
    expect(config('permission'))->toBeArray();
});
