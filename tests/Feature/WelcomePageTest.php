<?php

use Inertia\Testing\AssertableInertia;

test('welcome page returns successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('welcome page renders inertia component', function () {
    $response = $this->get('/');

    $response->assertInertia(fn (AssertableInertia $page) =>
        $page->component('Welcome')
    );
});

test('health check endpoint works', function () {
    $response = $this->get('/up');

    $response->assertStatus(200);
});
