<?php

namespace App\Domain\DataObjects;

use Spatie\LaravelData\Data;

class OrderTypeReportData extends Data
{
    /**
     * All money values are integer minor units in `$currencyCode`.
     *
     * @param  array{eatIn: array{orderCount: int, totalMinor: int, eatInChargeMinor: int, averageChargeMinor: int}, takeaway: array{orderCount: int, totalMinor: int, zeroRatedMinor: int}, eatInSharePercent: float}  $summary
     * @param  array<int, array{date: string, eatInCount: int, eatInTotalMinor: int, eatInChargeMinor: int, takeawayCount: int, takeawayTotalMinor: int, zeroRatedMinor: int}>  $daily
     * @param  array<int, array{variantId: string|null, productTitle: string, variantTitle: string|null, sku: string|null, orderType: string, orderCount: int, quantity: int, lineTotalMinor: int, eatInChargeMinor: int}>  $variants
     * @param  array<int, array{customerId: string|null, customerName: string|null, orderType: string, orderCount: int, totalMinor: int, eatInChargeMinor: int, zeroRatedMinor: int}>  $customers
     * @param  array<int, array{orderId: string, orderName: string, createdAt: string, channel: string|null, orderType: string, customerName: string|null, totalMinor: int, eatInTotalMinor: int, zeroRatedTotalMinor: int}>  $orders
     */
    public function __construct(
        public string $currencyCode,
        public string $timezone,
        public int $orderCount,
        public bool $isTruncated,
        public array $summary,
        public array $daily,
        public array $variants,
        public array $customers,
        public array $orders,
    ) {}
}
