<?php

namespace Tests\Feature\Seo;

use App\Models\BlogPost;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostMeta;
use App\Models\User;
use Database\Seeders\BlogSeoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogSeoContentSystemTest extends TestCase
{
    use RefreshDatabase;

    private function makeSeoPost(array $overrides = []): BlogPost
    {
        $author = User::factory()->create();

        return BlogPost::factory()->create(array_merge([
            'author_id' => $author->id,
            'title' => '10 Health Benefits of Makhana You Should Know',
            'slug' => '10-health-benefits-of-makhana-you-should-know',
            'excerpt' => 'An in-depth guide to makhana benefits and nutrition.',
            'content' => '<h1>Makhana Guide</h1><h2>Table of Contents</h2><p>Content body here.</p>',
            'featured_image' => 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg',
            'category' => 'Makhana Benefits',
            'cluster' => 'Makhana Benefits',
            'status' => 'published',
            'meta_title' => '10 Health Benefits of Makhana You Should Know | Snackzar',
            'meta_description' => 'Learn the top makhana benefits, nutrition facts, and best ways to include fox nuts in your diet.',
            'canonical_url' => 'https://snackzar.com/blog/10-health-benefits-of-makhana-you-should-know',
            'article_schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => '10 Health Benefits of Makhana You Should Know',
            ],
            'breadcrumb_schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
            ],
            'faq_schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
            ],
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    public function test_blog_pages_render_correctly(): void
    {
        $post = $this->makeSeoPost();

        $this->get('/blog')->assertOk();
        $this->get('/blog/' . $post->slug)->assertOk();
    }

    public function test_seo_metadata_exists_on_blog_posts(): void
    {
        $post = $this->makeSeoPost();

        $this->assertNotEmpty($post->meta_title);
        $this->assertNotEmpty($post->meta_description);
        $this->assertStringContainsString('/blog/' . $post->slug, (string) $post->canonical_url);
    }

    public function test_schema_markup_payloads_are_valid_and_typed(): void
    {
        $post = $this->makeSeoPost();

        $this->assertIsArray($post->article_schema);
        $this->assertSame('Article', $post->article_schema['@type']);

        $this->assertIsArray($post->breadcrumb_schema);
        $this->assertSame('BreadcrumbList', $post->breadcrumb_schema['@type']);

        $this->assertIsArray($post->faq_schema);
        $this->assertSame('FAQPage', $post->faq_schema['@type']);
    }

    public function test_image_records_are_valid_for_open_source_providers(): void
    {
        $author = User::factory()->create();

        $post = Post::create([
            'author_id' => $author->id,
            'title' => 'Healthy Snacks in Delhi: Complete Local Guide',
            'slug' => 'healthy-snacks-in-delhi-complete-local-guide',
            'category' => 'Healthy Snacks',
            'cluster' => 'Healthy Snacks',
            'content' => '<p>Healthy snack guide content.</p>',
            'featured_image' => 'https://source.unsplash.com/1600x900/?healthy-snacks,india',
            'image_source' => 'Unsplash',
            'image_alt' => 'Healthy Snacks in Delhi',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        PostImage::create([
            'post_id' => $post->id,
            'source' => 'Unsplash',
            'url' => 'https://source.unsplash.com/1600x900/?healthy-snacks,india',
            'alt_text' => 'Healthy snacks assortment',
            'is_featured' => true,
        ]);

        PostMeta::create([
            'post_id' => $post->id,
            'meta_title' => 'Healthy Snacks in Delhi | Snackzar',
            'meta_description' => 'Best healthy snacks in Delhi with practical buying tips.',
            'canonical_url' => 'https://snackzar.com/blog/healthy-snacks-in-delhi-complete-local-guide',
        ]);

        $image = PostImage::first();

        $this->assertNotNull($image);
        $this->assertStringStartsWith('https://', (string) $image->url);
        $this->assertContains($image->source, ['Unsplash', 'Pexels', 'Pixabay']);
    }

    public function test_blog_seo_seeder_creates_300_plus_posts_and_sitemap_is_updated(): void
    {
        User::factory()->create(['email' => 'admin@snackzar.com']);

        $this->seed(BlogSeoContentSeeder::class);

        $this->assertGreaterThanOrEqual(300, BlogPost::published()->count());
        $this->assertGreaterThanOrEqual(300, Post::published()->count());
        $this->assertGreaterThanOrEqual(300, PostMeta::count());
        $this->assertGreaterThanOrEqual(300, PostImage::count());
        $this->assertGreaterThan(0, BlogPost::where('is_featured', true)->count());

        $randomSlug = BlogPost::query()->value('slug');

        $this->get('/sitemap-blog.xml')
            ->assertOk()
            ->assertSee('/blog/' . $randomSlug, false);
    }
}
