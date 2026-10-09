<?php

use Inertia\Testing\AssertableInertia;
use Visualsoft\Shopify\Support\ThemeAppExtension;

beforeEach(function () {
    // Mock Shop::getDefaultCurrencyCode() GraphQL query
    $this->mockCurrencyCode();

    // Mock ShopLocale::defaultLocale() GraphQL query
    $this->mockLocale();
});

test('index page can be rendered', function () {
    $response = $this
        ->asShop()
        ->getJson(route('home'));

    $response->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('installUrl', ThemeAppExtension::installUrl())
            ->missing('stats')
        );
});

test('unauthenticated users cannot access index page', function () {
    $response = $this->getJson(route('home'));

    $response->assertForbidden();
});

test('stats returns correct structure and values', function (string $timeframe) {
    $response = $this
        ->asShop()
        ->get($this->routeWithShopifyToken('home', ['timeframe' => $timeframe]));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->missing('stats')
            ->reloadOnly('stats', fn (AssertableInertia $reload) => $reload
                ->has('stats', 4)
                ->has('stats.0', fn (AssertableInertia $stat) => $stat
                    ->has('title')
                    ->has('value')
                    ->where('title', 'dashboard.stats.example_stat_1')
                    ->etc()
                )
                ->has('stats.1', fn (AssertableInertia $stat) => $stat
                    ->has('title')
                    ->has('value')
                    ->where('title', 'dashboard.stats.example_stat_2')
                    ->etc()
                )
                ->has('stats.2', fn (AssertableInertia $stat) => $stat
                    ->has('title')
                    ->has('value')
                    ->where('title', 'dashboard.stats.example_stat_3')
                    ->etc()
                )
                ->has('stats.3', fn (AssertableInertia $stat) => $stat
                    ->has('title')
                    ->has('value')
                    ->where('title', 'dashboard.stats.example_stat_4')
                    ->etc()
                )
            )
        );
})->with(['daily_7', 'daily_30', 'monthly_6', 'all']);
