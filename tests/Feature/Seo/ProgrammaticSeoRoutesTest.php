<?php

use Inertia\Testing\AssertableInertia;

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
