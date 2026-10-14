<?php

namespace App\Domain\DataObjects;

use Spatie\LaravelData\Data;

class OrderReportResultData extends Data
{
    /**
     * @param  array<int, OrderReportRowData>  $rows
     */
    public function __construct(
        public array $rows,
        public bool $isTruncated,
        public string $currencyCode,
        public string $timezone,
    ) {}
}
