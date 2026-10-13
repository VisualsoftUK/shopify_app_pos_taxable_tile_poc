<?php

use App\Domain\Actions\Reports\BuildOrderTypeReportAction;
use App\Domain\Actions\Reports\GetReportCsvRowsAction;
use App\Domain\DataObjects\ReportFilterData;
use App\Domain\Enums\ReportExportType;
use App\Domain\Models\User;
use Carbon\CarbonImmutable;
use Tests\Fixtures\EatInFixtures;

beforeEach(function () {
    $this->shop = User::factory()->withMantlePlan()->create();
    $this->filter = new ReportFilterData(from: CarbonImmutable::parse('2026-10-01'), to: CarbonImmutable::parse('2026-10-09'));
});

test('it summarises eat-in and takeaway orders', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $report = BuildOrderTypeReportAction::for($this->shop, $this->filter)->get();

    expect($report->orderCount)->toBe(3)
        ->and($report->isTruncated)->toBeFalse()
        ->and($report->summary)->toBe([
            'eatIn' => ['orderCount' => 2, 'totalMinor' => 1422, 'eatInChargeMinor' => 237, 'averageChargeMinor' => 118],
            'takeaway' => ['orderCount' => 1, 'totalMinor' => 690, 'zeroRatedMinor' => 690],
            'eatInSharePercent' => 66.7,
        ]);
});

test('daily totals are grouped by the shop timezone date', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $daily = collect(BuildOrderTypeReportAction::for($this->shop, $this->filter)->get()->daily)->keyBy('date');

    // 23:30 UTC on 2 October is 00:30 BST on 3 October.
    expect($daily->keys()->all())->toBe(['2026-10-01', '2026-10-03'])
        ->and($daily['2026-10-01']['eatInCount'])->toBe(1)
        ->and($daily['2026-10-01']['takeawayCount'])->toBe(1)
        ->and($daily['2026-10-03']['eatInChargeMinor'])->toBe(158);
});

test('variants are split by the order type they were sold under', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $variants = collect(BuildOrderTypeReportAction::for($this->shop, $this->filter)->get()->variants)
        ->keyBy(fn (array $variant): string => "{$variant['productTitle']}|{$variant['orderType']}");

    expect($variants)->toHaveCount(3)
        ->and($variants['Cheese Sandwich|eat_in'])->toMatchArray(['orderCount' => 2, 'quantity' => 3, 'lineTotalMinor' => 1185, 'eatInChargeMinor' => 237])
        ->and($variants['Cheese Sandwich|takeaway'])->toMatchArray(['orderCount' => 1, 'quantity' => 1, 'eatInChargeMinor' => 0])
        ->and($variants['Berry Smoothie|takeaway'])->toMatchArray(['quantity' => 1, 'lineTotalMinor' => 295]);
});

test('customers are split by order type with guests grouped together', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $customers = collect(BuildOrderTypeReportAction::for($this->shop, $this->filter)->get()->customers)
        ->keyBy(fn (array $customer): string => ($customer['customerName'] ?? 'Guest') . "|{$customer['orderType']}");

    expect($customers->keys()->sort()->values()->all())->toBe(['Guest|eat_in', 'Jo Bloggs|eat_in', 'Jo Bloggs|takeaway'])
        ->and($customers['Jo Bloggs|takeaway'])->toMatchArray(['orderCount' => 1, 'zeroRatedMinor' => 690]);
});

test('reports are flagged as truncated when the page limit is reached', function () {
    config(['eat-in.reports.max_pages' => 1]);
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::reportData([], hasNextPage: true))->once();

    expect(BuildOrderTypeReportAction::for($this->shop, $this->filter)->get()->isTruncated)->toBeTrue();
});

test('reports are cached per shop and filter', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData())->once();

    BuildOrderTypeReportAction::for($this->shop, $this->filter)->get();
    BuildOrderTypeReportAction::for($this->shop, $this->filter)->get();
});

test('csv rows include a header and decimal money values', function (ReportExportType $type, array $header, int $rowCount) {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $rows = GetReportCsvRowsAction::for(BuildOrderTypeReportAction::for($this->shop, $this->filter)->get(), $type)->get();

    expect($rows->first())->toBe($header)
        ->and($rows)->toHaveCount($rowCount + 1);
})->with([
    'orders' => [ReportExportType::Orders, ['Order', 'Created at', 'Channel', 'Order type', 'Customer', 'Order total', 'Eat-in charge', 'Zero-rated value'], 3],
    'daily' => [ReportExportType::Daily, ['Date', 'Eat-in orders', 'Eat-in value', 'Eat-in charge', 'Takeaway orders', 'Takeaway value', 'Zero-rated value'], 2],
    'variants' => [ReportExportType::Variants, ['Product', 'Variant', 'SKU', 'Variant ID', 'Order type', 'Orders', 'Quantity', 'Line value', 'Eat-in charge'], 3],
    'customers' => [ReportExportType::Customers, ['Customer', 'Customer ID', 'Order type', 'Orders', 'Order value', 'Eat-in charge', 'Zero-rated value'], 3],
]);

test('csv money values are written as decimals', function () {
    $this->mockGraphQlOperation('OrderTypeReport', EatInFixtures::standardReportData());

    $rows = GetReportCsvRowsAction::for(BuildOrderTypeReportAction::for($this->shop, $this->filter)->get(), ReportExportType::Orders)->get();

    expect($rows[1])->toBe(['#1', '2026-10-01T10:00:00+01:00', 'pos', 'Eat In', 'Jo Bloggs', '4.74', '0.79', '0.00']);
});
