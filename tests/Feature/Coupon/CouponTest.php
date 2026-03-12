<?php

use App\Models\Coupon;
use App\Models\User;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Category;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeCoupon(array $attrs = []): Coupon
{
    return Coupon::create(array_merge([
        'code'             => 'TEST' . random_int(1000, 9999),
        'scope'            => 'public',
        'type'             => 'percent',
        'value'            => 10,
        'min_order_amount' => 0,
        'max_uses'         => 0,
        'max_uses_per_user'=> 1,
        'used_count'       => 0,
        'is_active'        => true,
    ], $attrs));
}

function makeUser(): User
{
    $user = User::factory()->create();
    $user->assignRole('user');
    return $user;
}

function makeAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    return $admin;
}

// ── Setup ─────────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

// ═══════════════════════════════════════════════════════════════════════════════
// COUPON MODEL UNIT TESTS
// ═══════════════════════════════════════════════════════════════════════════════

describe('Coupon model', function () {

    test('public coupon is valid for any user', function () {
        $coupon = makeCoupon(['scope' => 'public', 'min_order_amount' => 0]);
        $user   = makeUser();
        expect($coupon->isValidForUser($user, 200))->toBeTrue();
    });

    test('inactive coupon is invalid', function () {
        $coupon = makeCoupon(['is_active' => false]);
        $user   = makeUser();
        expect($coupon->isValidForUser($user))->toBeFalse();
    });

    test('expired coupon is invalid', function () {
        $coupon = makeCoupon(['expires_at' => now()->subDay()]);
        $user   = makeUser();
        expect($coupon->isValidForUser($user))->toBeFalse();
    });

    test('coupon with max uses reached is invalid', function () {
        $coupon = makeCoupon(['max_uses' => 5, 'used_count' => 5]);
        $user   = makeUser();
        expect($coupon->isValidForUser($user))->toBeFalse();
    });

    test('coupon requires minimum order amount', function () {
        $coupon = makeCoupon(['min_order_amount' => 500]);
        $user   = makeUser();
        expect($coupon->isValidForUser($user, 499))->toBeFalse();
        expect($coupon->isValidForUser($user, 500))->toBeTrue();
    });

    test('percent discount is calculated correctly', function () {
        $coupon = makeCoupon(['type' => 'percent', 'value' => 20, 'max_discount' => null]);
        expect($coupon->calculateDiscount(1000))->toBe(200.00);
    });

    test('percent discount respects max_discount cap', function () {
        $coupon = makeCoupon(['type' => 'percent', 'value' => 20, 'max_discount' => 100]);
        expect($coupon->calculateDiscount(1000))->toBe(100.00);
    });

    test('flat discount is calculated correctly', function () {
        $coupon = makeCoupon(['type' => 'flat', 'value' => 50]);
        expect($coupon->calculateDiscount(500))->toBe(50.00);
    });

    test('flat discount cannot exceed order amount', function () {
        $coupon = makeCoupon(['type' => 'flat', 'value' => 500]);
        expect($coupon->calculateDiscount(200))->toBe(200.00);
    });

    test('individual coupon is only valid for assigned user', function () {
        $owner = makeUser();
        $other = makeUser();
        $coupon = makeCoupon(['scope' => 'individual', 'user_id' => $owner->id]);

        expect($coupon->isValidForUser($owner))->toBeTrue();
        expect($coupon->isValidForUser($other))->toBeFalse();
    });

    test('bulk coupon is valid only for assigned users', function () {
        $user1  = makeUser();
        $user2  = makeUser();
        $coupon = makeCoupon(['scope' => 'bulk']);
        $coupon->assignedUsers()->attach($user1->id, ['is_active' => true, 'used_count' => 0]);

        expect($coupon->isValidForUser($user1))->toBeTrue();
        expect($coupon->isValidForUser($user2))->toBeFalse();
    });

    test('revoked bulk coupon user cannot use it', function () {
        $user   = makeUser();
        $coupon = makeCoupon(['scope' => 'bulk']);
        $coupon->assignedUsers()->attach($user->id, ['is_active' => false, 'used_count' => 0]);

        expect($coupon->isValidForUser($user))->toBeFalse();
    });

    test('per user limit is enforced for bulk coupon', function () {
        $user   = makeUser();
        $coupon = makeCoupon(['scope' => 'bulk', 'max_uses_per_user' => 1]);
        $coupon->assignedUsers()->attach($user->id, ['is_active' => true, 'used_count' => 1]);

        expect($coupon->isValidForUser($user))->toBeFalse();
    });

    test('generate code returns unique code with prefix', function () {
        $code = Coupon::generateCode('TEST', 6);
        expect($code)->toStartWith('TEST');
        expect(strlen($code))->toBe(10);
    });

    test('active scope filters correctly', function () {
        makeCoupon(['is_active' => true]);
        makeCoupon(['is_active' => false]);
        expect(Coupon::active()->count())->toBe(1);
    });

    test('forUser scope returns public and assigned coupons', function () {
        $user   = makeUser();
        $public = makeCoupon(['scope' => 'public']);
        $mine   = makeCoupon(['scope' => 'individual', 'user_id' => $user->id]);
        $other  = makeCoupon(['scope' => 'individual', 'user_id' => makeUser()->id]);

        $results = Coupon::forUser($user->id)->get();
        expect($results->pluck('id'))->toContain($public->id)
            ->toContain($mine->id)
            ->not->toContain($other->id);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// USER COUPON API
// ═══════════════════════════════════════════════════════════════════════════════

describe('User coupon API', function () {

    beforeEach(function () {
        $this->user    = makeUser();
        $this->token   = $this->user->createToken('test')->plainTextToken;
        $this->headers = ['Authorization' => "Bearer {$this->token}"];
    });

    test('user can validate a public coupon', function () {
        $coupon = makeCoupon(['code' => 'SAVE10', 'scope' => 'public', 'type' => 'percent', 'value' => 10]);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'SAVE10',
            'order_amount' => 500,
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('data.code', 'SAVE10');
        expect((float) $res->json('data.discount'))->toBe(50.0);
    });

    test('user gets error for invalid coupon code', function () {
        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'FAKECODE',
            'order_amount' => 100,
        ]);

        $res->assertStatus(422);
    });

    test('user gets error for expired coupon', function () {
        makeCoupon(['code' => 'EXPIRED', 'expires_at' => now()->subDay()]);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'EXPIRED',
            'order_amount' => 100,
        ]);

        $res->assertStatus(422);
    });

    test('user gets error when order amount is below minimum', function () {
        makeCoupon(['code' => 'BIGORDER', 'min_order_amount' => 999]);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'BIGORDER',
            'order_amount' => 100,
        ]);

        $res->assertStatus(422);
    });

    test('user cannot use individual coupon assigned to another user', function () {
        $other  = makeUser();
        $coupon = makeCoupon(['code' => 'NOTMINE', 'scope' => 'individual', 'user_id' => $other->id]);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'NOTMINE',
            'order_amount' => 200,
        ]);

        $res->assertStatus(422);
    });

    test('user can use individual coupon assigned to them', function () {
        $coupon = makeCoupon(['code' => 'MYCODE', 'scope' => 'individual', 'user_id' => $this->user->id]);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'MYCODE',
            'order_amount' => 200,
        ]);

        $res->assertStatus(200)->assertJsonPath('data.code', 'MYCODE');
    });

    test('user can list their available coupons', function () {
        makeCoupon(['scope' => 'public']);
        $mine = makeCoupon(['scope' => 'individual', 'user_id' => $this->user->id]);
        $other = makeCoupon(['scope' => 'individual', 'user_id' => makeUser()->id]);

        $res = $this->withHeaders($this->headers)->getJson('/api/v1/user/coupon/my-coupons');

        $res->assertStatus(200);
        $codes = collect($res->json('data'))->pluck('id');
        expect($codes)->toContain($mine->id)->not->toContain($other->id);
    });

    test('unauthenticated user cannot validate coupon', function () {
        makeCoupon(['code' => 'PUBLIC1']);

        $res = $this->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'PUBLIC1',
            'order_amount' => 100,
        ]);

        $res->assertStatus(401);
    });

    test('flat coupon discount is returned correctly', function () {
        makeCoupon(['code' => 'FLAT50', 'type' => 'flat', 'value' => 50]);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/coupon/validate', [
            'code'         => 'FLAT50',
            'order_amount' => 300,
        ]);

        $res->assertStatus(200);
        expect((float) $res->json('data.discount'))->toBe(50.0);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// ADMIN COUPON API
// ═══════════════════════════════════════════════════════════════════════════════

describe('Admin coupon management', function () {

    beforeEach(function () {
        $this->admin   = makeAdmin();
        $this->token   = $this->admin->createToken('test')->plainTextToken;
        $this->headers = ['Authorization' => "Bearer {$this->token}"];
    });

    test('admin can list all coupons', function () {
        makeCoupon(); makeCoupon();

        $res = $this->withHeaders($this->headers)->getJson('/api/v1/admin/coupons');

        $res->assertStatus(200);
        expect(count($res->json('data.data')))->toBeGreaterThanOrEqual(2);
    });

    test('admin can filter coupons by scope', function () {
        makeCoupon(['scope' => 'public']);
        makeCoupon(['scope' => 'enterprise']);

        $res = $this->withHeaders($this->headers)->getJson('/api/v1/admin/coupons?scope=enterprise');

        $res->assertStatus(200);
        $scopes = collect($res->json('data.data'))->pluck('scope')->unique()->values()->toArray();
        expect($scopes)->toBe(['enterprise']);
    });

    test('admin can create public coupon', function () {
        $res = $this->withHeaders($this->headers)->postJson('/api/v1/admin/coupons', [
            'scope'            => 'public',
            'type'             => 'percent',
            'value'            => 15,
            'min_order_amount' => 200,
            'max_uses'         => 100,
            'max_uses_per_user'=> 1,
            'description'      => 'Save 15% on orders above ₹200',
            'is_active'        => true,
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('data.scope', 'public')
            ->assertJsonPath('data.type', 'percent');

        expect(Coupon::where('scope', 'public')->exists())->toBeTrue();
    });

    test('admin can create coupon with explicit code', function () {
        $res = $this->withHeaders($this->headers)->postJson('/api/v1/admin/coupons', [
            'code'  => 'TESTADMIN99',
            'scope' => 'public',
            'type'  => 'flat',
            'value' => 99,
        ]);

        $res->assertStatus(201)->assertJsonPath('data.code', 'TESTADMIN99');
    });

    test('admin cannot create coupon with duplicate code', function () {
        makeCoupon(['code' => 'DUPE001']);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/admin/coupons', [
            'code'  => 'DUPE001',
            'scope' => 'public',
            'type'  => 'flat',
            'value' => 10,
        ]);

        $res->assertStatus(422);
    });

    test('admin can update coupon', function () {
        $coupon = makeCoupon();

        $res = $this->withHeaders($this->headers)->putJson("/api/v1/admin/coupons/{$coupon->id}", [
            'description' => 'Updated description',
            'value'       => 25,
        ]);

        $res->assertStatus(200)->assertJsonPath('data.value', '25.00');
    });

    test('admin can delete coupon', function () {
        $coupon = makeCoupon();

        $this->withHeaders($this->headers)->deleteJson("/api/v1/admin/coupons/{$coupon->id}")
            ->assertStatus(200);

        expect(Coupon::find($coupon->id))->toBeNull();
    });

    test('admin can toggle coupon active status', function () {
        $coupon = makeCoupon(['is_active' => true]);

        $res = $this->withHeaders($this->headers)->patchJson("/api/v1/admin/coupons/{$coupon->id}/toggle");

        $res->assertStatus(200);
        expect($coupon->fresh()->is_active)->toBeFalse();
    });

    test('admin can view coupon stats', function () {
        makeCoupon(['is_active' => true]);
        makeCoupon(['is_active' => false]);

        $res = $this->withHeaders($this->headers)->getJson('/api/v1/admin/coupons/stats');

        $res->assertStatus(200)
            ->assertJsonStructure(['data' => ['total', 'active', 'expired', 'total_redemptions', 'top_used']]);
    });

    test('admin can assign coupon to a single user', function () {
        $coupon = makeCoupon(['scope' => 'individual']);
        $user   = makeUser();

        $res = $this->withHeaders($this->headers)->postJson("/api/v1/admin/coupons/{$coupon->id}/assign", [
            'user_id' => $user->id,
        ]);

        $res->assertStatus(200);
        expect($coupon->assignedUsers()->where('user_id', $user->id)->exists())->toBeTrue();
    });

    test('admin can revoke coupon from user', function () {
        $coupon = makeCoupon(['scope' => 'bulk']);
        $user   = makeUser();
        $coupon->assignedUsers()->attach($user->id, ['is_active' => true, 'used_count' => 0]);

        $res = $this->withHeaders($this->headers)->postJson("/api/v1/admin/coupons/{$coupon->id}/revoke", [
            'user_id' => $user->id,
        ]);

        $res->assertStatus(200);
        $pivot = $coupon->assignedUsers()->where('user_id', $user->id)->first()?->pivot;
        expect((bool) $pivot?->is_active)->toBeFalse();
    });

    test('admin can bulk assign coupon to multiple users', function () {
        $coupon = makeCoupon(['scope' => 'bulk']);
        $users  = User::factory()->count(3)->create();
        foreach ($users as $u) $u->assignRole('user');

        $res = $this->withHeaders($this->headers)->postJson("/api/v1/admin/coupons/{$coupon->id}/bulk-assign", [
            'user_ids' => $users->pluck('id')->toArray(),
        ]);

        $res->assertStatus(200)->assertJsonPath('data.assigned_count', 3);
        expect($coupon->assignedUsers()->count())->toBe(3);
    });

    test('admin can bulk generate coupons', function () {
        $res = $this->withHeaders($this->headers)->postJson('/api/v1/admin/coupons/bulk-generate', [
            'count'            => 5,
            'scope'            => 'bulk',
            'type'             => 'percent',
            'value'            => 10,
            'max_uses'         => 1,
            'max_uses_per_user'=> 1,
            'prefix'           => 'TEST',
        ]);

        $res->assertStatus(201)->assertJsonPath('data.count', 5);
        expect($res->json('data.codes'))->toHaveCount(5);
    });

    test('bulk generate creates unique codes', function () {
        $this->withHeaders($this->headers)->postJson('/api/v1/admin/coupons/bulk-generate', [
            'count' => 10, 'scope' => 'public', 'type' => 'flat', 'value' => 20,
        ]);

        $codes = Coupon::where('type', 'flat')->where('value', 20)->pluck('code');
        expect($codes->unique()->count())->toBe($codes->count());
    });

    test('admin can view assigned users for a coupon', function () {
        $coupon = makeCoupon(['scope' => 'bulk']);
        $user   = makeUser();
        $coupon->assignedUsers()->attach($user->id, ['is_active' => true, 'used_count' => 0]);

        $res = $this->withHeaders($this->headers)->getJson("/api/v1/admin/coupons/{$coupon->id}/users");

        $res->assertStatus(200);
        $ids = collect($res->json('data.data'))->pluck('id');
        expect($ids)->toContain($user->id);
    });

    test('non-admin cannot access coupon management', function () {
        $user    = makeUser();
        $headers = ['Authorization' => 'Bearer ' . $user->createToken('t')->plainTextToken];

        $this->withHeaders($headers)->getJson('/api/v1/admin/coupons')
            ->assertStatus(403);
    });

    test('admin can bulk assign by filter no-orders', function () {
        $coupon   = makeCoupon(['scope' => 'enterprise']);
        $noOrder  = makeUser();
        // user with no orders - just makeUser is fine

        $res = $this->withHeaders($this->headers)->postJson("/api/v1/admin/coupons/{$coupon->id}/bulk-filter", [
            'filter' => 'no_orders',
        ]);

        $res->assertStatus(200);
        expect($res->json('data.assigned_count'))->toBeGreaterThanOrEqual(1);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// COUPON APPLIED ON ORDER
// ═══════════════════════════════════════════════════════════════════════════════

describe('Coupon applied on order', function () {

    beforeEach(function () {
        $this->seed(RolePermissionSeeder::class);
        $this->user    = makeUser();
        $this->token   = $this->user->createToken('test')->plainTextToken;
        $this->headers = ['Authorization' => "Bearer {$this->token}"];

        // Address
        $this->address = Address::create([
            'user_id'       => $this->user->id,
            'name'          => 'Test User',
            'phone'         => '9876543210',
            'address_line_1'=> '123 Test St',
            'city'          => 'Patna',
            'state'         => 'Bihar',
            'pincode'       => '800001',
            'is_default'    => true,
        ]);

        // Product + category + cart
        $category = Category::factory()->create();
        $product  = Product::factory()->create([
            'category_id' => $category->id,
            'price'       => 200,
            'stock'       => 50,
            'is_active'   => true,
        ]);

        $cart = Cart::create(['user_id' => $this->user->id]);
        CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => $product->id,
            'quantity'   => 2,
            'unit_price' => 200,
        ]);
    });

    test('order discount is 0 when no coupon is given', function () {
        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/orders', [
            'address_id'     => $this->address->id,
            'payment_method' => 'cod',
        ]);

        $res->assertStatus(201);
        expect((float) $res->json('data.discount'))->toBe(0.0);
    });

    test('valid coupon reduces order total', function () {
        $coupon = makeCoupon(['code' => 'FLAT50', 'type' => 'flat', 'value' => 50, 'scope' => 'public']);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/orders', [
            'address_id'     => $this->address->id,
            'payment_method' => 'cod',
            'coupon_code'    => 'FLAT50',
        ]);

        $res->assertStatus(201);
        expect((float) $res->json('data.discount'))->toBe(50.0);
        expect($res->json('data.coupon_code'))->toBe('FLAT50');
    });

    test('coupon used_count increments after order', function () {
        $coupon = makeCoupon(['code' => 'USECOUNT', 'type' => 'flat', 'value' => 10, 'scope' => 'public']);
        expect($coupon->used_count)->toBe(0);

        $this->withHeaders($this->headers)->postJson('/api/v1/user/orders', [
            'address_id'     => $this->address->id,
            'payment_method' => 'cod',
            'coupon_code'    => 'USECOUNT',
        ])->assertStatus(201);

        expect($coupon->fresh()->used_count)->toBe(1);
    });

    test('invalid coupon code on order returns validation error', function () {
        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/orders', [
            'address_id'     => $this->address->id,
            'payment_method' => 'cod',
            'coupon_code'    => 'BADCOUPON999',
        ]);

        $res->assertStatus(422);
    });

    test('order total never goes below zero with coupon', function () {
        // Cart total is 400 (2 × ₹200), apply ₹999 flat discount
        $coupon = makeCoupon(['code' => 'HUGEDISCOUNT', 'type' => 'flat', 'value' => 999, 'scope' => 'public']);

        $res = $this->withHeaders($this->headers)->postJson('/api/v1/user/orders', [
            'address_id'     => $this->address->id,
            'payment_method' => 'cod',
            'coupon_code'    => 'HUGEDISCOUNT',
        ]);

        $res->assertStatus(201);
        expect((float) $res->json('data.total'))->toBeGreaterThanOrEqual(0.0);
    });
});
