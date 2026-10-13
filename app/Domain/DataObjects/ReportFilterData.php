<?php

namespace App\Domain\DataObjects;

use App\Domain\Enums\OrderType;
use App\Domain\Enums\ReportChannel;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class ReportFilterData extends Data
{
    /**
     * @param  CarbonImmutable|null  $from  Inclusive start date (shop-local calendar date); null means no lower bound.
     * @param  CarbonImmutable  $to  Inclusive end date (shop-local calendar date).
     * @param  OrderType|null  $orderType  Null includes both eat-in and takeaway orders.
     */
    public function __construct(
        public ?CarbonImmutable $from,
        public CarbonImmutable $to,
        public ?OrderType $orderType = null,
        public ReportChannel $channel = ReportChannel::All,
    ) {}

    /**
     * Build a filter from a dashboard timeframe key (see the admin TimeFilters util).
     */
    public static function fromTimeframe(string $timeframe, ?CarbonImmutable $today = null): self
    {
        $today = ($today ?? CarbonImmutable::today())->startOfDay();

        $from = match ($timeframe) {
            'daily_7' => $today->subDays(6),
            'daily_60' => $today->subDays(59),
            'daily_90' => $today->subDays(89),
            'monthly_6' => $today->subMonthsNoOverflow(6)->addDay(),
            'monthly_12' => $today->subMonthsNoOverflow(12)->addDay(),
            'all' => null,
            default => $today->subDays(29),
        };

        return new self(from: $from, to: $today);
    }

    /**
     * The Shopify order search query for this filter. Only classified orders
     * (tagged by the app) are included.
     */
    public function toSearchQuery(): string
    {
        $terms = [];

        if ($this->from !== null) {
            $terms[] = "created_at:>={$this->from->toDateString()}";
        }

        $terms[] = "created_at:<{$this->to->addDay()->toDateString()}";

        $terms[] = $this->orderTypeTerm();

        if ($this->channel !== ReportChannel::All) {
            $terms[] = "source_name:{$this->channel->value}";
        }

        return implode(' ', $terms);
    }

    /**
     * A stable cache key segment for this filter.
     */
    public function cacheKey(): string
    {
        return md5($this->toSearchQuery());
    }

    /**
     * Query-string friendly representation used by the admin UI.
     *
     * @return array{from: string|null, to: string, order_type: string, channel: string}
     */
    public function toQueryParams(): array
    {
        return [
            'from' => $this->from?->toDateString(),
            'to' => $this->to->toDateString(),
            'order_type' => $this->orderType?->value ?? 'all',
            'channel' => $this->channel->value,
        ];
    }

    protected function orderTypeTerm(): string
    {
        if ($this->orderType !== null) {
            return "tag:'{$this->orderType->label()}'";
        }

        return sprintf("(tag:'%s' OR tag:'%s')", OrderType::EatIn->label(), OrderType::Takeaway->label());
    }
}
