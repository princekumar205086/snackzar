<?php

use App\Models\SeoCity;
use App\Models\SeoKeyword;
use App\Models\SeoCityKeyword;
use App\Modules\Shared\Services\SeoGeneratorService;
use App\Modules\Shared\Services\SeoPageRendererService;
use App\Modules\Shared\Services\RobotsTxtService;
use App\Modules\Shared\Services\FaviconService;
use App\Modules\Shared\Services\MultiCurrencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

describe('Programmatic SEO Routes', function () {
    it('renders district landing page', function () {
        $response = $this->get('/makhana-in-purnia');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Seo/Landing')
                ->where('mode', 'location')
                ->has('location')
                ->has('seo')
        );
    });

    it('renders city landing page', function () {
        $response = $this->get('/buy-makhana-online-delhi');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Seo/Landing')
                ->where('mode', 'location')
                ->has('location')
                ->has('seo')
        );
    });

    it('renders keyword landing page for valid id', function () {
        $response = $this->get('/seo/k/1-buy-makhana-online');

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) =>
            $page->component('Seo/Landing')
                ->where('mode', 'keyword')
                ->has('keyword')
                ->has('seo')
        );
    });

    it('returns 404 for out-of-range keyword id', function () {
        $response = $this->get('/seo/k/999999-buy-makhana-online');

        $response->assertNotFound();
    });
});

describe('SEO Generator Service', function () {
    it('can generate seed keywords', function () {
        $service = new SeoGeneratorService();
        $service->generateSeedKeywords();

        expect(SeoKeyword::count())->toBeGreaterThan(0);
    });

    it('can seed locations', function () {
        $service = new SeoGeneratorService();
        $service->seedLocations();

        expect(SeoCity::count())->toBeGreaterThan(38); // At least Bihar districts
    });

    it('can generate city-keyword combinations', function () {
        $service = new SeoGeneratorService();
        $service->generateSeedKeywords();
        $service->seedLocations();
        $count = $service->generateCityKeywordCombinations(10);

        expect($count)->toBe(10);
        expect(SeoCityKeyword::count())->toBe(10);
    });

    it('returns correct statistics', function () {
        $service = new SeoGeneratorService();
        $service->generateSeedKeywords();
        $service->seedLocations();
        $stats = $service->getStatistics();

        expect($stats)->toHaveKey('total_cities');
        expect($stats)->toHaveKey('active_cities');
        expect($stats)->toHaveKey('total_keywords');
        expect($stats)->toHaveKey('active_keywords');
        expect($stats)->toHaveKey('city_keyword_combinations');
    });
});

describe('SEO Models', function () {
    it('can create seo city', function () {
        $city = SeoCity::create([
            'name' => 'Test City',
            'slug' => 'test-city',
            'type' => 'city',
            'country' => 'IN',
            'is_active' => true,
        ]);

        expect($city)->toBeInstanceOf(SeoCity::class);
        expect($city->name)->toBe('Test City');
    });

    it('can create seo keyword', function () {
        $keyword = SeoKeyword::create([
            'keyword' => 'test keyword',
            'slug' => 'test-keyword',
            'is_active' => true,
        ]);

        expect($keyword)->toBeInstanceOf(SeoKeyword::class);
        expect($keyword->keyword)->toBe('test keyword');
    });

    it('can create city-keyword combination', function () {
        $city = SeoCity::create([
            'name' => 'Test City',
            'slug' => 'test-city',
            'type' => 'city',
            'country' => 'IN',
        ]);

        $keyword = SeoKeyword::create([
            'keyword' => 'test keyword',
            'slug' => 'test-keyword',
        ]);

        $combination = SeoCityKeyword::create([
            'seo_city_id' => $city->id,
            'seo_keyword_id' => $keyword->id,
            'url_slug' => 'test-keyword-in-test-city',
            'page_title' => 'Test Keyword in Test City',
            'meta_description' => 'Find test keyword in test city',
        ]);

        expect($combination)->toBeInstanceOf(SeoCityKeyword::class);
        expect($combination->getCanonicalUrl())->toContain('test-keyword-in-test-city');
    });
});

describe('SEO Page Renderer', function () {
    it('can generate landing page data', function () {
        $city = SeoCity::create([
            'name' => 'Test City',
            'slug' => 'test-city',
            'type' => 'city',
            'country' => 'IN',
        ]);

        $keyword = SeoKeyword::create([
            'keyword' => 'test keyword',
            'slug' => 'test-keyword',
        ]);

        $cityKeyword = SeoCityKeyword::create([
            'seo_city_id' => $city->id,
            'seo_keyword_id' => $keyword->id,
            'url_slug' => 'test-keyword-in-test-city',
            'page_title' => 'Test Keyword in Test City',
            'meta_description' => 'Find test keyword in test city',
        ]);

        $renderer = new SeoPageRendererService();
        $pageData = $renderer->generateLandingPage($cityKeyword);

        expect($pageData)->toHaveKey('seo');
        expect($pageData)->toHaveKey('location');
        expect($pageData)->toHaveKey('keyword');
        expect($pageData)->toHaveKey('content');
        expect($pageData)->toHaveKey('schema');
        expect($pageData['content'])->toHaveKey('h1');
        expect($pageData['content'])->toHaveKey('faq');
        expect($pageData['schema'])->toHaveKey('breadcrumb');
    });
});

describe('Robots.txt Service', function () {
    it('can generate robots.txt content', function () {
        $service = new RobotsTxtService();
        $content = $service->generate();

        expect($content)->toContain('User-agent: *');
        expect($content)->toContain('Disallow: /admin/');
        expect($content)->toContain('Sitemap:');
    });

    it('robots.txt route returns correct content type', function () {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/plain');
    });
});

describe('Multi-Currency Service', function () {
    it('supports all currencies', function () {
        $service = new MultiCurrencyService('IN');
        $currencies = $service->getSupportedCurrencies();

        expect($currencies)->toHaveKey('INR');
        expect($currencies)->toHaveKey('USD');
        expect($currencies)->toHaveKey('EUR');
        expect($currencies)->toHaveKey('GBP');
        expect($currencies)->toHaveKey('AED');
        expect($currencies)->toHaveKey('SGD');
    });

    it('can convert prices', function () {
        $service = new MultiCurrencyService('IN');
        $inrPrice = 1000;
        
        $usdPrice = $service->convertPrice($inrPrice, 'USD');
        $eurPrice = $service->convertPrice($inrPrice, 'EUR');

        expect($usdPrice)->toBeLessThan($inrPrice);
        expect($eurPrice)->toBeLessThan($inrPrice);
    });

    it('can format prices', function () {
        $service = new MultiCurrencyService('IN');
        $service->setCurrency('INR');
        $formatted = $service->formatPrice(1000, 'INR');

        expect($formatted)->toContain('₹');
        expect($formatted)->toContain('1000');
    });
});

describe('Favicon Service', function () {
    it('can generate manifest', function () {
        $service = new FaviconService();
        $manifest = $service->generateWebManifest();

        expect($manifest)->toBeJson();
        
        $decoded = json_decode($manifest, true);
        expect($decoded['name'])->toBe('Snackzar - Premium Healthy Snacks');
        expect($decoded['icons'])->not->toBeEmpty();
    });

    it('can generate browser config', function () {
        $service = new FaviconService();
        $config = $service->generateBrowserConfig();

        expect($config)->toContain('browserconfig');
        expect($config)->toContain('mstile');
    });

    it('can generate head tags', function () {
        $service = new FaviconService();
        $tags = $service->generateHeadTags();

        expect($tags)->toContain('favicon');
        expect($tags)->toContain('apple-touch-icon');
        expect($tags)->toContain('manifest');
    });
});

