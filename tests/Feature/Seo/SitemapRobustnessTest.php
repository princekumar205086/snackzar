<?php

use App\Modules\Shared\Services\CityLandingPageService;

it('serves sitemap index with keyword shards', function () {
    $response = $this->get('/sitemap-index.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('sitemapindex', false);
    $response->assertSee('sitemap-keywords-1.xml', false);
});

it('serves first keyword sitemap shard', function () {
    $response = $this->get('/sitemap-keywords-1.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('urlset', false);
    $response->assertSee('/seo/k/1-', false);
});

it('returns 404 for invalid keyword shard', function () {
    $response = $this->get('/sitemap-keywords-999.xml');

    $response->assertNotFound();
});

it('has full bihar district coverage and large keyword universe', function () {
    $service = app(CityLandingPageService::class);

    expect($service->getBiharDistricts())->toHaveCount(38);
    expect($service->getKeywordUniverseSize())->toBeGreaterThanOrEqual(250000);
});
