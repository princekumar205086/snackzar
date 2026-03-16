<?php

namespace App\Modules\Shared\Services;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public function __construct(
        private readonly CityLandingPageService $cityLandingService
    ) {}

    public function generate(): array
    {
        return Cache::remember('sitemap:urls', 3600, function () {
            return $this->buildUrls();
        });
    }

    public function generateSitemapIndex(): string
    {
        $baseUrl = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        $coreSitemaps = [
            '/sitemap-main.xml',
            '/sitemap-products.xml',
            '/sitemap-cities.xml',
            '/sitemap-blog.xml',
        ];

        foreach ($coreSitemaps as $path) {
            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . $path, ENT_XML1) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $keywordParts = $this->getKeywordSitemapPartCount();
        for ($part = 1; $part <= $keywordParts; $part++) {
            $xml .= '  <sitemap>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . "/sitemap-keywords-{$part}.xml", ENT_XML1) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . now()->toAtomString() . '</lastmod>' . PHP_EOL;
            $xml .= '  </sitemap>' . PHP_EOL;
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    public function getKeywordSitemapPartCount(): int
    {
        $total = (int) config('snackzar.seo.programmatic.target_indexable_pages', 150000);
        $chunkSize = (int) config('snackzar.seo.sitemap_chunk_size', 45000);

        return max(1, (int) ceil($total / max(1, $chunkSize)));
    }

    public function getKeywordLandingUrlsForPart(int $part): array
    {
        return Cache::remember("sitemap:keywords:part:{$part}", 21600, function () use ($part) {
            $targetIndexablePages = (int) config('snackzar.seo.programmatic.target_indexable_pages', 150000);
            $keywordUniverseSize = (int) config('snackzar.seo.programmatic.keyword_universe_size', 250000);
            $chunkSize = (int) config('snackzar.seo.sitemap_chunk_size', 45000);

            $maxPages = min($targetIndexablePages, $keywordUniverseSize);
            $totalParts = max(1, (int) ceil($maxPages / max(1, $chunkSize)));

            if ($part < 1 || $part > $totalParts) {
                return [];
            }

            $start = ($part - 1) * $chunkSize + 1;
            $end = min($part * $chunkSize, $maxPages);
            $lastmod = now()->toAtomString();

            $urls = [];
            for ($id = $start; $id <= $end; $id++) {
                $keywordSlug = $this->cityLandingService->getKeywordByIndex($id - 1);
                $urls[] = [
                    'url' => "/seo/k/{$id}-{$keywordSlug}",
                    'priority' => '0.5',
                    'changefreq' => 'weekly',
                    'lastmod' => $lastmod,
                ];
            }

            return $urls;
        });
    }

    protected function buildUrls(): array
    {
        $urls = [];

        $urls[] = ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'];
        $urls[] = ['url' => '/products', 'priority' => '0.9', 'changefreq' => 'daily'];
        $urls[] = ['url' => '/about', 'priority' => '0.7', 'changefreq' => 'monthly'];
        $urls[] = ['url' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'];
        $urls[] = ['url' => '/blog', 'priority' => '0.8', 'changefreq' => 'weekly'];

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

        Category::active()->select('slug', 'updated_at')->each(function ($category) use (&$urls) {
            $urls[] = [
                'url' => "/category/{$category->slug}",
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at->toAtomString(),
            ];
        });

        BlogPost::published()->select('slug', 'updated_at')->each(function ($post) use (&$urls) {
            $urls[] = [
                'url' => "/blog/{$post->slug}",
                'priority' => '0.6',
                'changefreq' => 'weekly',
                'lastmod' => $post->updated_at->toAtomString(),
            ];
        });

        foreach ($this->cityLandingService->getBiharDistricts() as $slug => $name) {
            $urls[] = [
                'url' => "/makhana-in-{$slug}",
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => now()->toAtomString(),
            ];
        }

        foreach (array_keys($this->cityLandingService->getMajorCities()) as $city) {
            $urls[] = [
                'url' => "/buy-makhana-online-{$city}",
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => now()->toAtomString(),
            ];
        }

        foreach (array_keys($this->cityLandingService->getGlobalCities()) as $city) {
            $urls[] = [
                'url' => "/buy-makhana-online-{$city}",
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => now()->toAtomString(),
            ];
        }

        return $urls;
    }

    public function getBiharDistricts(): array
    {
        return $this->cityLandingService->getBiharDistricts();
    }

    public function getMajorCities(): array
    {
        return array_keys($this->cityLandingService->getMajorCities());
    }

    public function getGlobalCities(): array
    {
        return array_keys($this->cityLandingService->getGlobalCities());
    }
}
