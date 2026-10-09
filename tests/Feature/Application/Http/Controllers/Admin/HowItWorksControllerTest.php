<?php

use Inertia\Testing\AssertableInertia;

test('index page can be rendered', function () {
    $response = $this
        ->asShop()
        ->getJson(route('how-it-works'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('HowItWorks')
        );
});

test('unauthenticated users cannot access index page', function () {
    $response = $this->getJson(route('how-it-works'));

    $response->assertForbidden();
});
