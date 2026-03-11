<?php

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Models\SeoMeta;
use App\Models\User;
use App\Modules\Shared\Services\BlogService;
use App\Modules\Shared\Services\SitemapService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Notification::fake();

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->token = $this->admin->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];
});

// -- Blog Model & Factory --

test('blog post factory creates valid post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

    expect($post)->toBeInstanceOf(BlogPost::class)
        ->and($post->title)->toBeString()
        ->and($post->slug)->toBeString()
        ->and($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull();
});

test('blog post factory draft state', function () {
    $post = BlogPost::factory()->draft()->create(['author_id' => $this->admin->id]);

    expect($post->status)->toBe('draft')
        ->and($post->published_at)->toBeNull();
});

test('blog post belongs to author', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

    expect($post->author)->toBeInstanceOf(User::class)
        ->and($post->author->id)->toBe($this->admin->id);
});

test('blog post published scope filters correctly', function () {
    BlogPost::factory()->count(3)->create(['author_id' => $this->admin->id, 'status' => 'published']);
    BlogPost::factory()->draft()->count(2)->create(['author_id' => $this->admin->id]);

    $published = BlogPost::published()->count();
    $drafts = BlogPost::draft()->count();

    expect($published)->toBe(3)
        ->and($drafts)->toBe(2);
});

// -- SEO Meta (Polymorphic) --

test('seo meta can be attached to blog post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);
    $seo = SeoMeta::create([
        'seoable_type' => BlogPost::class,
        'seoable_id' => $post->id,
        'meta_title' => 'SEO Title',
        'meta_description' => 'SEO Description',
    ]);

    expect($post->seoMeta)->toBeInstanceOf(SeoMeta::class)
        ->and($post->seoMeta->meta_title)->toBe('SEO Title');
});

test('seo meta can be attached to product', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'seller_id' => $this->admin->id]);
    $seo = SeoMeta::create([
        'seoable_type' => Product::class,
        'seoable_id' => $product->id,
        'meta_title' => 'Product SEO',
        'meta_description' => 'Product Description',
    ]);

    expect($product->seoMeta)->toBeInstanceOf(SeoMeta::class)
        ->and($product->seoMeta->meta_title)->toBe('Product SEO');
});

test('seo meta can be attached to category', function () {
    $category = Category::factory()->create();
    $seo = SeoMeta::create([
        'seoable_type' => Category::class,
        'seoable_id' => $category->id,
        'meta_title' => 'Category SEO',
    ]);

    expect($category->seoMeta)->toBeInstanceOf(SeoMeta::class)
        ->and($category->seoMeta->meta_title)->toBe('Category SEO');
});

// -- Blog Service --

test('blog service lists published posts', function () {
    BlogPost::factory()->count(3)->create(['author_id' => $this->admin->id, 'status' => 'published']);
    BlogPost::factory()->draft()->count(2)->create(['author_id' => $this->admin->id]);

    $service = app(BlogService::class);
    $result = $service->listPublished();

    expect($result->total())->toBe(3);
});

test('blog service filters published by category', function () {
    BlogPost::factory()->count(2)->create(['author_id' => $this->admin->id, 'category' => 'Recipes']);
    BlogPost::factory()->create(['author_id' => $this->admin->id, 'category' => 'Health']);

    $service = app(BlogService::class);
    $result = $service->listPublished(['category' => 'Recipes']);

    expect($result->total())->toBe(2);
});

test('blog service find by slug increments views', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id, 'views_count' => 5]);

    $service = app(BlogService::class);
    $found = $service->findBySlug($post->slug);

    expect($found->id)->toBe($post->id)
        ->and($found->fresh()->views_count)->toBe(6);
});

test('blog service stores new post with slug', function () {
    $service = app(BlogService::class);
    $post = $service->store([
        'title' => 'Test Blog Post Title',
        'content' => 'Some content here.',
        'status' => 'published',
        'author_id' => $this->admin->id,
    ]);

    expect($post->slug)->toContain('test-blog-post-title')
        ->and($post->published_at)->not->toBeNull();
});

test('blog service updates post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);
    $service = app(BlogService::class);

    $updated = $service->update($post, ['title' => 'Updated Title']);

    expect($updated->title)->toBe('Updated Title');
});

test('blog service deletes post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);
    $service = app(BlogService::class);

    $service->delete($post);

    expect(BlogPost::find($post->id))->toBeNull();
});

// -- Admin Blog API CRUD --

test('admin can list blog posts', function () {
    BlogPost::factory()->count(3)->create(['author_id' => $this->admin->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/v1/admin/blog');

    $response->assertStatus(200)
        ->assertJsonPath('data.total', 3);
});

test('admin can create blog post', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/admin/blog', [
            'title' => 'New Blog Post',
            'content' => 'This is the content of the blog post.',
            'category' => 'Makhana',
            'status' => 'published',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'New Blog Post');

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'New Blog Post',
        'author_id' => $this->admin->id,
    ]);
});

test('admin can view single blog post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/v1/admin/blog/{$post->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $post->id);
});

test('admin can update blog post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/v1/admin/blog/{$post->id}", [
            'title' => 'Updated Blog Title',
        ]);

    $response->assertStatus(200);
    expect($post->fresh()->title)->toBe('Updated Blog Title');
});

test('admin can delete blog post', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/v1/admin/blog/{$post->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
});

test('admin blog store validation rejects empty title', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/v1/admin/blog', [
            'content' => 'Content without title',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('title');
});

// -- Public Blog Routes (Inertia) --

test('public blog index page renders', function () {
    BlogPost::factory()->count(3)->create(['author_id' => $this->admin->id]);

    $response = $this->get('/blog');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Blog/Index')
            ->has('posts.data', 3)
            ->has('categories')
        );
});

test('public blog show page renders', function () {
    $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

    $response = $this->get("/blog/{$post->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Blog/Show')
            ->where('post.id', $post->id)
            ->has('relatedPosts')
        );
});

test('public blog show returns 404 for invalid slug', function () {
    $response = $this->get('/blog/non-existent-slug');

    $response->assertStatus(404);
});

test('public blog index filters by category', function () {
    BlogPost::factory()->count(2)->create(['author_id' => $this->admin->id, 'category' => 'Recipes']);
    BlogPost::factory()->create(['author_id' => $this->admin->id, 'category' => 'Health']);

    $response = $this->get('/blog?category=Recipes');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Blog/Index')
            ->has('posts.data', 2)
        );
});

// -- Sitemap --

test('sitemap xml renders with correct content type', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/xml');

    $content = $response->getContent();
    expect($content)->toContain('<urlset')
        ->and($content)->toContain('<loc>');
});

test('sitemap includes static pages', function () {
    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->toContain('/products')
        ->and($content)->toContain('/about')
        ->and($content)->toContain('/blog');
});

test('sitemap includes products and blog posts', function () {
    $category = Category::factory()->create();
    Product::factory()->create([
        'category_id' => $category->id,
        'seller_id' => $this->admin->id,
        'slug' => 'test-makhana-product',
        'is_active' => true,
        'stock' => 10,
    ]);
    BlogPost::factory()->create([
        'author_id' => $this->admin->id,
        'slug' => 'test-blog-article',
    ]);

    $response = $this->get('/sitemap.xml');

    $content = $response->getContent();
    expect($content)->toContain('test-makhana-product')
        ->and($content)->toContain('test-blog-article');
});

test('sitemap service generates urls array', function () {
    $category = Category::factory()->create();
    Product::factory()->create([
        'category_id' => $category->id,
        'seller_id' => $this->admin->id,
        'is_active' => true,
        'stock' => 10,
    ]);

    $service = app(SitemapService::class);
    $urls = $service->generate();

    expect($urls)->toBeArray()
        ->and(count($urls))->toBeGreaterThan(5);

    $staticUrls = collect($urls)->pluck('url')->toArray();
    expect($staticUrls)->toContain('/')
        ->and($staticUrls)->toContain('/products')
        ->and($staticUrls)->toContain('/blog');
});
