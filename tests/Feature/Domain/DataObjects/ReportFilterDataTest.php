<?php

use App\Domain\DataObjects\ReportFilterData;
use App\Domain\Enums\OrderType;
use App\Domain\Enums\ReportChannel;
use Carbon\CarbonImmutable;

test('the search query covers the inclusive date range and both order types by default', function () {
    $filter = new ReportFilterData(
        from: CarbonImmutable::parse('2026-10-01'),
        to: CarbonImmutable::parse('2026-10-09'),
    );

    expect($filter->toSearchQuery())
        ->toBe("created_at:>=2026-10-01 created_at:<2026-10-10 (tag:'Eat In' OR tag:'Takeaway')");
});

test('the search query narrows by order type and channel', function () {
    $filter = new ReportFilterData(
        from: null,
        to: CarbonImmutable::parse('2026-10-09'),
        orderType: OrderType::EatIn,
        channel: ReportChannel::Pos,
    );

    expect($filter->toSearchQuery())->toBe("created_at:<2026-10-10 tag:'Eat In' source_name:pos");
});

test('dashboard timeframes map to date ranges ending today', function (string $timeframe, ?string $expectedFrom) {
    $filter = ReportFilterData::fromTimeframe($timeframe, CarbonImmutable::parse('2026-10-09'));

    expect($filter->from?->toDateString())->toBe($expectedFrom)
        ->and($filter->to->toDateString())->toBe('2026-10-09');
})->with([
    ['daily_7', '2026-10-03'],
    ['daily_30', '2026-09-10'],
    ['monthly_6', '2026-04-10'],
    ['all', null],
]);
