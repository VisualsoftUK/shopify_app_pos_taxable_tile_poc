<?php

namespace App\Domain\Actions\Reports;

use App\Domain\DataObjects\OrderReportResultData;
use App\Domain\DataObjects\OrderReportRowData;
use App\Domain\DataObjects\OrderTypeReportData;
use App\Domain\DataObjects\OrderVatLineData;
use App\Domain\DataObjects\ReportFilterData;
use App\Domain\Enums\OrderType;
use App\Domain\Services\OrderReportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Visualsoft\Shopify\Contracts\VsShopModel;

/**
 * Aggregate eat-in / takeaway orders for a date range into summary, daily,
 * variant and customer breakdowns. Nothing is persisted; results are cached
 * briefly per shop and filter.
 */
class BuildOrderTypeReportAction
{
    public function __construct(
        protected VsShopModel $shop,
        protected ReportFilterData $filter,
    ) {}

    public static function for(VsShopModel $shop, ReportFilterData $filter): static
    {
        return new static(shop: $shop, filter: $filter);
    }

    public function get(): OrderTypeReportData
    {
        return Cache::remember(
            "order-type-report:{$this->shop->id}:{$this->filter->cacheKey()}",
            (int) config('eat-in.reports.cache_ttl_seconds'),
            fn (): OrderTypeReportData => $this->build(OrderReportService::for($this->shop)->fetch($this->filter))
        );
    }

    protected function build(OrderReportResultData $result): OrderTypeReportData
    {
        $rows = collect($result->rows);

        return new OrderTypeReportData(
            currencyCode: $result->currencyCode,
            timezone: $result->timezone,
            orderCount: $rows->count(),
            isTruncated: $result->isTruncated,
            summary: $this->summary($rows),
            daily: $this->daily($rows),
            variants: $this->variants($rows),
            customers: $this->customers($rows),
            orders: $this->orders($rows),
        );
    }

    /**
     * @param  Collection<int, OrderReportRowData>  $rows
     * @return array{eatIn: array{orderCount: int, totalMinor: int, eatInChargeMinor: int, averageChargeMinor: int}, takeaway: array{orderCount: int, totalMinor: int, zeroRatedMinor: int}, eatInSharePercent: float}
     */
    protected function summary(Collection $rows): array
    {
        $eatInRows = $rows->filter(fn (OrderReportRowData $row): bool => $row->orderType === OrderType::EatIn);
        $takeawayRows = $rows->filter(fn (OrderReportRowData $row): bool => $row->orderType === OrderType::Takeaway);

        $eatInChargeMinor = $eatInRows->sum(fn (OrderReportRowData $row): int => $row->eatInTotalMinor);
        $eatInCount = $eatInRows->count();

        return [
            'eatIn' => [
                'orderCount' => $eatInCount,
                'totalMinor' => $eatInRows->sum(fn (OrderReportRowData $row): int => $row->totalMinor),
                'eatInChargeMinor' => $eatInChargeMinor,
                'averageChargeMinor' => $eatInCount > 0 ? intdiv($eatInChargeMinor, $eatInCount) : 0,
            ],
            'takeaway' => [
                'orderCount' => $takeawayRows->count(),
                'totalMinor' => $takeawayRows->sum(fn (OrderReportRowData $row): int => $row->totalMinor),
                'zeroRatedMinor' => $takeawayRows->sum(fn (OrderReportRowData $row): int => $row->zeroRatedTotalMinor),
            ],
            'eatInSharePercent' => $rows->isEmpty() === true ? 0.0 : round(($eatInCount / $rows->count()) * 100, 1),
        ];
    }

    /**
     * @param  Collection<int, OrderReportRowData>  $rows
     * @return array<int, array{date: string, eatInCount: int, eatInTotalMinor: int, eatInChargeMinor: int, takeawayCount: int, takeawayTotalMinor: int, zeroRatedMinor: int}>
     */
    protected function daily(Collection $rows): array
    {
        return $rows
            ->groupBy(fn (OrderReportRowData $row): string => $row->createdAt->toDateString())
            ->map(function (Collection $dayRows, string $date): array {
                $eatIn = $dayRows->filter(fn (OrderReportRowData $row): bool => $row->orderType === OrderType::EatIn);
                $takeaway = $dayRows->filter(fn (OrderReportRowData $row): bool => $row->orderType === OrderType::Takeaway);

                return [
                    'date' => $date,
                    'eatInCount' => $eatIn->count(),
                    'eatInTotalMinor' => $eatIn->sum(fn (OrderReportRowData $row): int => $row->totalMinor),
                    'eatInChargeMinor' => $eatIn->sum(fn (OrderReportRowData $row): int => $row->eatInTotalMinor),
                    'takeawayCount' => $takeaway->count(),
                    'takeawayTotalMinor' => $takeaway->sum(fn (OrderReportRowData $row): int => $row->totalMinor),
                    'zeroRatedMinor' => $takeaway->sum(fn (OrderReportRowData $row): int => $row->zeroRatedTotalMinor),
                ];
            })
            ->sortKeys()
            ->values()
            ->all();
    }

    /**
     * Dynamic-product variants sold, split by the order type they were sold under.
     *
     * @param  Collection<int, OrderReportRowData>  $rows
     * @return array<int, array{variantId: string|null, productTitle: string, variantTitle: string|null, sku: string|null, orderType: string, orderCount: int, quantity: int, lineTotalMinor: int, eatInChargeMinor: int}>
     */
    protected function variants(Collection $rows): array
    {
        return $rows
            ->flatMap(fn (OrderReportRowData $row): array => collect($row->lines)
                ->map(fn (OrderVatLineData $line): array => ['row' => $row, 'line' => $line])
                ->all())
            ->groupBy(fn (array $item): string => "{$item['line']->variantId}|{$item['row']->orderType->value}")
            ->map(function (Collection $items): array {
                /** @var OrderVatLineData $first */
                $first = $items->first()['line'];

                return [
                    'variantId' => $first->variantId,
                    'productTitle' => $first->productTitle,
                    'variantTitle' => $first->variantTitle,
                    'sku' => $first->sku,
                    'orderType' => $items->first()['row']->orderType->value,
                    'orderCount' => $items->unique(fn (array $item): string => $item['row']->orderId)->count(),
                    'quantity' => $items->sum(fn (array $item): int => $item['line']->quantity),
                    'lineTotalMinor' => $items->sum(fn (array $item): int => $item['line']->lineTotalMinor),
                    'eatInChargeMinor' => $items->sum(fn (array $item): int => $item['line']->eatInChargeMinor),
                ];
            })
            ->sortByDesc('quantity')
            ->values()
            ->all();
    }

    /**
     * Customers (null = guest / walk-in) split by order type.
     *
     * @param  Collection<int, OrderReportRowData>  $rows
     * @return array<int, array{customerId: string|null, customerName: string|null, orderType: string, orderCount: int, totalMinor: int, eatInChargeMinor: int, zeroRatedMinor: int}>
     */
    protected function customers(Collection $rows): array
    {
        return $rows
            ->groupBy(fn (OrderReportRowData $row): string => ($row->customerId ?? 'guest') . "|{$row->orderType->value}")
            ->map(function (Collection $customerRows): array {
                /** @var OrderReportRowData $first */
                $first = $customerRows->first();

                return [
                    'customerId' => $first->customerId,
                    'customerName' => $first->customerName,
                    'orderType' => $first->orderType->value,
                    'orderCount' => $customerRows->count(),
                    'totalMinor' => $customerRows->sum(fn (OrderReportRowData $row): int => $row->totalMinor),
                    'eatInChargeMinor' => $customerRows->sum(fn (OrderReportRowData $row): int => $row->eatInTotalMinor),
                    'zeroRatedMinor' => $customerRows->sum(fn (OrderReportRowData $row): int => $row->zeroRatedTotalMinor),
                ];
            })
            ->sortByDesc('orderCount')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, OrderReportRowData>  $rows
     * @return array<int, array{orderId: string, orderName: string, createdAt: string, channel: string|null, orderType: string, customerName: string|null, totalMinor: int, eatInTotalMinor: int, zeroRatedTotalMinor: int}>
     */
    protected function orders(Collection $rows): array
    {
        return $rows
            ->map(fn (OrderReportRowData $row): array => [
                'orderId' => $row->orderId,
                'orderName' => $row->orderName,
                'createdAt' => $row->createdAt->toIso8601String(),
                'channel' => $row->channel,
                'orderType' => $row->orderType->value,
                'customerName' => $row->customerName,
                'totalMinor' => $row->totalMinor,
                'eatInTotalMinor' => $row->eatInTotalMinor,
                'zeroRatedTotalMinor' => $row->zeroRatedTotalMinor,
            ])
            ->values()
            ->all();
    }
}
