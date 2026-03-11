<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Modules\Shared\Services\CategoryService;
use App\Modules\Shared\Services\ProductService;
use App\Modules\Shared\Services\SitemapService;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Cache::flush();
    Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'seller', 'guard_name' => 'web']);
});

// --- Category Caching ---

test('category service caches active categories', function () {
    Category::factory()->create(['is_active' => true, 'parent_id' => null]);

    $service = app(CategoryService::class);

    $result1 = $service->listActive();
    expect(Cache::has('categories:active'))->toBeTrue();

    $result2 = $service->listActive();
    expect($result2->count())->toBe($result1->count());
});

test('category service caches slug lookup', function () {
    $category = Category::factory()->create(['is_active' => true, 'slug' => 'test-cat']);

    $service = app(CategoryService::class);
    $service->findBySlug('test-cat');

    expect(Cache::has('categories:slug:test-cat'))->toBeTrue();
});

test('category cache is cleared on store', function () {
    Cache::put('categories:active', 'cached-value', 3600);

    $service = app(CategoryService::class);
    $service->store([
        'name' => 'New Category',
        'slug' => 'new-category',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    expect(Cache::has('categories:active'))->toBeFalse();
});

test('category cache is cleared on update', function () {
    $category = Category::factory()->create(['is_active' => true]);
    Cache::put('categories:active', 'cached-value', 3600);

    $service = app(CategoryService::class);
    $service->update($category, ['name' => 'Updated']);

    expect(Cache::has('categories:active'))->toBeFalse();
});

test('category cache is cleared on delete', function () {
    $category = Category::factory()->create(['is_active' => true]);
    Cache::put('categories:active', 'cached-value', 3600);

    $service = app(CategoryService::class);
    $service->delete($category);

    expect(Cache::has('categories:active'))->toBeFalse();
});

// --- Product Caching ---

test('product featured method caches results', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
        'is_active' => true,
        'is_featured' => true,
        'stock' => 10,
    ]);

    $service = app(ProductService::class);
    $service->featured(8);

    expect(Cache::has('products:featured:8'))->toBeTrue();
});

test('product cache is cleared via static method', function () {
    Cache::put('products:featured:8', 'cached', 600);
    Cache::put('homepage:featured', 'cached', 600);
    Cache::put('homepage:stats', 'cached', 600);

    ProductService::clearCache();

    expect(Cache::has('products:featured:8'))->toBeFalse();
    expect(Cache::has('homepage:featured'))->toBeFalse();
    expect(Cache::has('homepage:stats'))->toBeFalse();
});

// --- Observer Cache Busting ---

test('product observer clears cache on save', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $category = Category::factory()->create(['is_active' => true]);

    Cache::put('products:featured:8', 'old', 600);
    Cache::put('sitemap:urls', 'old', 3600);

    Product::factory()->create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
    ]);

    expect(Cache::has('products:featured:8'))->toBeFalse();
    expect(Cache::has('sitemap:urls'))->toBeFalse();
});

test('category observer clears cache on save', function () {
    Cache::put('categories:active', 'old', 3600);
    Cache::put('homepage:categories', 'old', 3600);

    Category::factory()->create(['is_active' => true]);

    expect(Cache::has('categories:active'))->toBeFalse();
    expect(Cache::has('homepage:categories'))->toBeFalse();
});

test('review observer clears cache on save', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
    ]);

    Cache::put('homepage:reviews', 'old', 600);
    Cache::put('homepage:stats', 'old', 600);

    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Great product',
        'is_approved' => true,
    ]);

    expect(Cache::has('homepage:reviews'))->toBeFalse();
    expect(Cache::has('homepage:stats'))->toBeFalse();
});

// --- Sitemap Caching ---

test('sitemap service caches results', function () {
    $service = app(SitemapService::class);
    $result = $service->generate();

    expect(Cache::has('sitemap:urls'))->toBeTrue();
    expect($result)->toBeArray();
    expect(count($result))->toBeGreaterThanOrEqual(5); // at least static pages
});

// --- Homepage Caching ---

test('homepage caches data correctly', function () {
    $response = $this->get('/');

    // After visiting homepage, caches should be populated
    expect(Cache::has('homepage:featured'))->toBeTrue();
    expect(Cache::has('homepage:new_arrivals'))->toBeTrue();
    expect(Cache::has('homepage:categories'))->toBeTrue();
    expect(Cache::has('homepage:top_rated'))->toBeTrue();
    expect(Cache::has('homepage:reviews'))->toBeTrue();
    expect(Cache::has('homepage:stats'))->toBeTrue();
});

// --- Eager Loading Verification ---

test('product list uses eager loading', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $category = Category::factory()->create(['is_active' => true]);

    Product::factory()->count(3)->create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
        'is_active' => true,
        'stock' => 10,
    ]);

    $service = app(ProductService::class);
    $products = $service->list();

    // Verify relations are loaded (eager loaded)
    foreach ($products as $product) {
        expect($product->relationLoaded('category'))->toBeTrue();
        expect($product->relationLoaded('primaryImage'))->toBeTrue();
        expect($product->relationLoaded('seller'))->toBeTrue();
    }
});

test('product findBySlug uses eager loading', function () {
    $seller = User::factory()->create();
    $seller->assignRole('seller');
    $category = Category::factory()->create(['is_active' => true]);

    $product = Product::factory()->create([
        'seller_id' => $seller->id,
        'category_id' => $category->id,
        'is_active' => true,
        'slug' => 'test-product',
    ]);

    $service = app(ProductService::class);
    $found = $service->findBySlug('test-product');

    expect($found)->not->toBeNull();
    expect($found->relationLoaded('category'))->toBeTrue();
    expect($found->relationLoaded('images'))->toBeTrue();
    expect($found->relationLoaded('variants'))->toBeTrue();
    expect($found->relationLoaded('seller'))->toBeTrue();
});

// --- Clear App Cache Command ---

test('artisan clear app cache command works', function () {
    Cache::put('products:featured:8', 'cached', 600);
    Cache::put('categories:active', 'cached', 3600);
    Cache::put('homepage:reviews', 'cached', 300);
    Cache::put('sitemap:urls', 'cached', 3600);

    $this->artisan('app:clear-cache')
        ->expectsOutput('Application cache cleared successfully.')
        ->assertExitCode(0);

    expect(Cache::has('products:featured:8'))->toBeFalse();
    expect(Cache::has('categories:active'))->toBeFalse();
    expect(Cache::has('homepage:reviews'))->toBeFalse();
    expect(Cache::has('sitemap:urls'))->toBeFalse();
});
