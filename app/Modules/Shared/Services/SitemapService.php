<?php

namespace App\Modules\Shared\Services;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public function generate(): array
    {
        return Cache::remember('sitemap:urls', 3600, function () {
            return $this->buildUrls();
        });
    }

    protected function buildUrls(): array
    {
        $urls = [];

        // Static pages
        $urls[] = ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['url' => '/products', 'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['url' => '/about', 'priority' => '0.5', 'changefreq' => 'monthly'];
        $urls[] = ['url' => '/contact', 'priority' => '0.5', 'changefreq' => 'monthly'];
        $urls[] = ['url' => '/blog', 'priority' => '0.8', 'changefreq' => 'daily'];

        // Products
        Product::active()->inStock()->select('slug', 'updated_at')->chunk(100, function ($products) use (&$urls) {
            foreach ($products as $product) {
                $urls[] = [
                    'url' => "/products/{$product->slug}",
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at->toAtomString(),
                ];
            }
        });

        // Categories
        Category::active()->select('slug', 'updated_at')->each(function ($category) use (&$urls) {
            $urls[] = [
                'url' => "/products?category={$category->slug}",
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at->toAtomString(),
            ];
        });

        // Blog posts
        BlogPost::published()->select('slug', 'updated_at')->each(function ($post) use (&$urls) {
            $urls[] = [
                'url' => "/blog/{$post->slug}",
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'lastmod' => $post->updated_at->toAtomString(),
            ];
        });

        return $urls;
    }
}
