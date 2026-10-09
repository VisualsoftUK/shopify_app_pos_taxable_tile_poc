<?php

use Inertia\Testing\AssertableInertia;
use Visualsoft\Shopify\Support\ThemeAppExtension;

test('index page can be rendered', function () {
    $response = $this
        ->asShop()
        ->getJson(route('settings'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Settings')
            ->has('installUrl', ThemeAppExtension::installUrl())
        );
});

test('unauthenticated users cannot access index page', function () {
    $response = $this->getJson(route('settings'));

    $response->assertForbidden();
});
