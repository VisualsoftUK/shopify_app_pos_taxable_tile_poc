<?php

use Inertia\Testing\AssertableInertia;
use Tests\Fixtures\EatInFixtures;

test('the reports page renders with default filters and defers the report', function () {
    $this->travelTo('2026-10-09 12:00:00');

    $this->asShop()
        ->getJson(route('reports'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Reports')
            ->where('filters', ['from' => '2026-09-10', 'to' => '2026-10-09', 'order_type' => 'all', 'channel' => 'all'])
            ->missing('report')
        );
});

test('the deferred report is loaded for the requested filters', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData(),
        fn (array $params): bool => $params['query'] === "created_at:>=2026-10-01 created_at:<2026-10-10 tag:'Eat In' source_name:pos"
    )->once();

    $this->asShop()
        ->get($this->routeWithShopifyToken('reports', [
            'from' => '2026-10-01',
            'to' => '2026-10-09',
            'order_type' => 'eat_in',
            'channel' => 'pos',
        ]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Reports')
            ->where('filters.order_type', 'eat_in')
            ->loadDeferredProps(fn (AssertableInertia $reload) => $reload
                ->where('report.orderCount', 3)
                ->where('report.currencyCode', 'GBP')
                ->where('report.summary.eatIn.eatInChargeMinor', 237)
                ->has('report.variants', 3)
                ->has('report.customers', 3)
                ->etc()
            )
        );
});

test('invalid filters are rejected', function (array $params) {
    $this->asShop()
        ->getJson(route('reports', $params))
        ->assertUnprocessable();
})->with([
    'bad date' => [['from' => '01/10/2026']],
    'to before from' => [['from' => '2026-10-09', 'to' => '2026-10-01']],
    'unknown order type' => [['order_type' => 'drive_thru']],
    'unknown channel' => [['channel' => 'phone']],
]);

test('a report section can be exported as csv', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $response = $this->asShop()
        ->get($this->routeWithShopifyToken('reports.export', [
            'from' => '2026-10-01',
            'to' => '2026-10-09',
            'type' => 'variants',
        ]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertDownload('eat-in-variants-2026-10-01-to-2026-10-09.csv');

    $lines = array_filter(explode("\n", $response->streamedContent()));

    expect($lines)->toHaveCount(4)
        ->and($lines[0])->toBe('Product,Variant,SKU,"Variant ID","Order type",Orders,Quantity,"Line value","Eat-in charge"');
});

test('unauthenticated users cannot access reports', function () {
    $this->getJson(route('reports'))->assertForbidden();
    $this->getJson(route('reports.export'))->assertForbidden();
});
