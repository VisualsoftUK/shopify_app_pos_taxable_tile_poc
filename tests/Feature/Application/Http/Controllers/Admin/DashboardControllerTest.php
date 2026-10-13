<?php

use Inertia\Testing\AssertableInertia;
use Tests\Fixtures\EatInFixtures;
use Visualsoft\Shopify\Support\ThemeAppExtension;

beforeEach(function () {
    // Mock ShopLocale::defaultLocale() GraphQL query
    $this->mockLocale();

    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());
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

test('stats return eat-in and takeaway metrics', function (string $timeframe) {
    $response = $this
        ->asShop()
        ->get($this->routeWithShopifyToken('home', ['timeframe' => $timeframe]));

    $response
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->missing('stats')
            ->reloadOnly('stats', fn (AssertableInertia $reload) => $reload
                ->has('stats', 6)
                ->where('stats.0.title', 'dashboard.stats.eat_in_orders')
                ->where('stats.0.value', '2')
                ->where('stats.1.title', 'dashboard.stats.eat_in_charges')
                ->where('stats.1.value', '£2.37')
                ->where('stats.2.title', 'dashboard.stats.takeaway_orders')
                ->where('stats.2.value', '1')
                ->where('stats.3.title', 'dashboard.stats.zero_rated_value')
                ->where('stats.3.value', '£6.90')
                ->where('stats.4.title', 'dashboard.stats.eat_in_share')
                ->where('stats.4.value', '66.7%')
                ->where('stats.5.title', 'dashboard.stats.average_eat_in_charge')
                ->where('stats.5.value', '£1.18')
                ->etc()
            )
        );
})->with(['daily_7', 'daily_30', 'monthly_6', 'all']);
