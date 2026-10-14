<?php

namespace App\Domain\DataObjects;

use App\Domain\Enums\OrderType;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class OrderReportRowData extends Data
{
    /**
     * @param  array<int, OrderVatLineData>  $lines
     */
    public function __construct(
        public string $orderId,
        public string $orderName,
        public CarbonImmutable $createdAt,
        public ?string $channel,
        public OrderType $orderType,
        public string $currencyCode,
        public int $totalMinor,
        public int $eatInTotalMinor,
        public int $zeroRatedTotalMinor,
        public ?string $customerId,
        public ?string $customerName,
        public array $lines,
    ) {}
}
