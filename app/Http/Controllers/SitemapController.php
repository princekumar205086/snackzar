<?php

namespace App\Http\Controllers;

use App\Modules\Shared\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapService $sitemapService
    ) {}

    public function index(): Response
    {
        return $this->generateSiteMap($this->sitemapService->generate());
    }

    public function indexXml(): Response
    {
        $xml = $this->sitemapService->generateSitemapIndex();
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function mainXml(): Response
    {
        $baseUrl = config('app.url');
        
        $urls = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/products', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => '/blog', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ];

        return $this->generateSiteMap($urls);
    }

    public function productsXml(): Response
    {
        $baseUrl = config('app.url');
        $urls = [];

        // Products
        \App\Models\Product::active()
            ->inStock()
            ->select('slug', 'updated_at')
            ->chunk(100, function ($products) use (&$urls) {
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
        \App\Models\Category::active()
            ->select('slug', 'updated_at')
            ->each(function ($category) use (&$urls) {
                $urls[] = [
                    'url' => "/category/{$category->slug}",
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $category->updated_at->toAtomString(),
                ];
            });

        return $this->generateSiteMap($urls);
    }

    public function citiesXml(): Response
    {
        $urls = [];

        // Bihar districts
        foreach ($this->sitemapService->getBiharDistricts() as $slug => $name) {
            $urls[] = [
                'url' => "/makhana-in-{$slug}",
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        }

        // Major Indian cities
        foreach ($this->sitemapService->getMajorCities() as $city) {
            $urls[] = [
                'url' => "/buy-makhana-online-{$city}",
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        // Global cities
        foreach ($this->sitemapService->getGlobalCities() as $city) {
            $urls[] = [
                'url' => "/buy-makhana-online-{$city}",
                'priority' => '0.6',
                'changefreq' => 'monthly',
            ];
        }

        return $this->generateSiteMap($urls);
    }

    public function keywordsXml(int $part): Response
    {
        $urls = $this->sitemapService->getKeywordLandingUrlsForPart($part);

        if ($urls === []) {
            abort(404);
        }

        return $this->generateSiteMap($urls);
    }

    public function blogXml(): Response
    {
        $urls = [];
        $baseUrl = config('app.url');

        // Blog posts
        \App\Models\BlogPost::published()
            ->select('slug', 'updated_at')
            ->each(function ($post) use (&$urls) {
                $urls[] = [
                    'url' => "/blog/{$post->slug}",
                    'priority' => '0.6',
                    'changefreq' => 'weekly',
                    'lastmod' => $post->updated_at->toAtomString(),
                ];
            });

        return $this->generateSiteMap($urls);
    }

    protected function generateSiteMap(array $urls): Response
    {
        $baseUrl = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . PHP_EOL;
        $xml .= '  xmlns:xhtml="http://www.w3.org/1999/xhtml"' . PHP_EOL;
        $xml .= '  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . PHP_EOL;
        $xml .= '  xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"' . PHP_EOL;
        $xml .= '  xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"' . PHP_EOL;
        $xml .= '  xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($baseUrl . $url['url'], ENT_XML1) . '</loc>' . PHP_EOL;
            if (isset($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            }
            $xml .= '    <changefreq>' . ($url['changefreq'] ?? 'weekly') . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . ($url['priority'] ?? '0.5') . '</priority>' . PHP_EOL;
            
            // Add alternate language versions if available
            if (isset($url['alternates'])) {
                foreach ($url['alternates'] as $lang => $langUrl) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="' . htmlspecialchars($lang, ENT_QUOTES) . '" href="' . htmlspecialchars($langUrl, ENT_QUOTES) . '" />' . PHP_EOL;
                }
            }
            
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
