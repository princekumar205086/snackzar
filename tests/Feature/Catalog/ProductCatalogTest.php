<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();
});

test('can list active categories', function () {
    Category::factory()->count(3)->create();
    Category::factory()->inactive()->create();

    $response = $this->getJson('/api/v1/user/categories');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('categories include children', function () {
    $parent = Category::factory()->create();
    Category::factory()->count(2)->create(['parent_id' => $parent->id]);

    $response = $this->getJson('/api/v1/user/categories');

    $response->assertStatus(200);
    $data = $response->json('data');
    // Only root categories returned
    expect($data)->toHaveCount(1);
    expect($data[0]['children'])->toHaveCount(2);
});

test('can view category by slug', function () {
    $category = Category::factory()->create(['slug' => 'makhana']);

    $response = $this->getJson('/api/v1/user/categories/makhana');

    $response->assertStatus(200)
        ->assertJsonPath('data.slug', 'makhana');
});

test('returns 404 for nonexistent category', function () {
    $response = $this->getJson('/api/v1/user/categories/nonexistent');

    $response->assertStatus(404);
});

test('can list products with pagination', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $category = Category::factory()->create();

    Product::factory()->count(5)->create([
        'category_id' => $category->id,
        'seller_id' => $seller->id,
        'stock' => 100,
    ]);

    $response = $this->getJson('/api/v1/user/products');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(5);
});

test('products can be filtered by category', function () {
    $seller = User::factory()->create();
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    Product::factory()->count(3)->create(['category_id' => $category1->id, 'seller_id' => $seller->id, 'stock' => 50]);
    Product::factory()->count(2)->create(['category_id' => $category2->id, 'seller_id' => $seller->id, 'stock' => 50]);

    $response = $this->getJson("/api/v1/user/products?category_id={$category1->id}");

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(3);
});

test('products can be searched', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();

    Product::factory()->create(['name' => 'Premium Makhana', 'category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);
    Product::factory()->create(['name' => 'Sattu Powder', 'category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);

    $response = $this->getJson('/api/v1/user/products?search=Makhana');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('products can be sorted by price', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();

    Product::factory()->create(['name' => 'Cheap', 'price' => 100, 'category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);
    Product::factory()->create(['name' => 'Expensive', 'price' => 500, 'category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);

    $response = $this->getJson('/api/v1/user/products?sort_by=price&sort_dir=asc');

    $response->assertStatus(200);
    $data = $response->json('data.data');
    expect((float) $data[0]['price'])->toBeLessThanOrEqual((float) $data[1]['price']);
});

test('inactive products are excluded from listing', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();

    Product::factory()->create(['category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);
    Product::factory()->inactive()->create(['category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);

    $response = $this->getJson('/api/v1/user/products');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('out of stock products are excluded from listing', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();

    Product::factory()->create(['category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);
    Product::factory()->outOfStock()->create(['category_id' => $category->id, 'seller_id' => $seller->id]);

    $response = $this->getJson('/api/v1/user/products');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(1);
});

test('can view product by slug', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'premium-makhana-500g',
        'category_id' => $category->id,
        'seller_id' => $seller->id,
    ]);

    ProductImage::factory()->primary()->create(['product_id' => $product->id]);
    ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    $response = $this->getJson('/api/v1/user/products/premium-makhana-500g');

    $response->assertStatus(200)
        ->assertJsonPath('data.slug', 'premium-makhana-500g');
    expect($response->json('data.variants'))->toHaveCount(2);
});

test('product detail includes images', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'test-product',
        'category_id' => $category->id,
        'seller_id' => $seller->id,
    ]);
    ProductImage::factory()->count(3)->create(['product_id' => $product->id]);

    $response = $this->getJson('/api/v1/user/products/test-product');

    $response->assertStatus(200);
    expect($response->json('data.images'))->toHaveCount(3);
});

test('can list featured products', function () {
    $seller = User::factory()->create();
    $category = Category::factory()->create();

    Product::factory()->featured()->count(3)->create(['category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50]);
    Product::factory()->count(2)->create(['category_id' => $category->id, 'seller_id' => $seller->id, 'stock' => 50, 'is_featured' => false]);

    $response = $this->getJson('/api/v1/user/products/featured');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('product model computes discount percentage', function () {
    $product = new Product(['price' => 200, 'compare_price' => 300]);

    expect($product->hasDiscount())->toBeTrue();
    expect($product->discountPercentage())->toBe(33.3);
});

test('product model detects in stock status', function () {
    $inStock = new Product(['stock' => 10]);
    $outOfStock = new Product(['stock' => 0]);

    expect($inStock->isInStock())->toBeTrue();
    expect($outOfStock->isInStock())->toBeFalse();
});

test('category model scopes work correctly', function () {
    Category::factory()->count(2)->create();
    Category::factory()->inactive()->create();
    $parent = Category::factory()->create();
    Category::factory()->create(['parent_id' => $parent->id]);

    expect(Category::active()->count())->toBe(4); // 2 + parent + child (all active)
    expect(Category::root()->count())->toBe(4); // 3 root + 1 inactive root... actually
});
