<?php

namespace App\Domain\Services;

use App\Domain\Concerns\ReadsGraphQlResponses;
use App\Domain\DataObjects\OrderReportResultData;
use App\Domain\DataObjects\OrderReportRowData;
use App\Domain\DataObjects\OrderVatLineData;
use App\Domain\DataObjects\ReportFilterData;
use App\Domain\Enums\OrderType;
use App\Domain\Support\MoneyAmount;
use Carbon\CarbonImmutable;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\Shopify\Services\ShopifyGraphQlService;

/**
 * Reads classified orders (and the eat-in metafields written by
 * ProcessOrderVatAction) live from Shopify. Only order-level fields are
 * requested so each page stays well inside the GraphQL cost limit.
 */
class OrderReportService
{
    use ReadsGraphQlResponses;

    protected const string ORDERS_QUERY = <<<'GRAPHQL'
        query OrderTypeReport($query: String!, $after: String, $first: Int!, $namespace: String!, $orderTypeKey: String!, $eatInTotalKey: String!, $zeroRatedTotalKey: String!, $breakdownKey: String!) {
            shop { currencyCode ianaTimezone }
            orders(first: $first, after: $after, query: $query, sortKey: CREATED_AT) {
                pageInfo { hasNextPage endCursor }
                nodes {
                    id
                    name
                    createdAt
                    sourceName
                    currentTotalPriceSet { shopMoney { amount currencyCode } }
                    customer { id displayName }
                    orderType: metafield(namespace: $namespace, key: $orderTypeKey) { value }
                    eatInTotal: metafield(namespace: $namespace, key: $eatInTotalKey) { value }
                    zeroRatedTotal: metafield(namespace: $namespace, key: $zeroRatedTotalKey) { value }
                    vatBreakdown: metafield(namespace: $namespace, key: $breakdownKey) { value }
                }
            }
        }
        GRAPHQL;

    public function __construct(
        protected VsShopModel $shop
    ) {}

    public static function for(VsShopModel $shop): static
    {
        return new static(shop: $shop);
    }

    public function fetch(ReportFilterData $filter): OrderReportResultData
    {
        $maxPages = (int) config('eat-in.reports.max_pages');
        $rows = [];
        $cursor = null;
        $page = 0;
        $currencyCode = 'GBP';
        $timezone = (string) config('app.timezone');

        do {
            $response = ShopifyGraphQlService::for($this->shop)
                ->withQuery(self::ORDERS_QUERY)
                ->withParams([
                    'query' => $filter->toSearchQuery(),
                    'after' => $cursor,
                    'first' => (int) config('eat-in.reports.page_size'),
                    'namespace' => config('eat-in.order_metafields.namespace'),
                    'orderTypeKey' => config('eat-in.order_metafields.order_type_key'),
                    'eatInTotalKey' => config('eat-in.order_metafields.eat_in_total_key'),
                    'zeroRatedTotalKey' => config('eat-in.order_metafields.zero_rated_total_key'),
                    'breakdownKey' => config('eat-in.order_metafields.breakdown_key'),
                ])
                ->get();

            $currencyCode = $this->responseValue($response, 'body.data.shop.currencyCode', $currencyCode);
            $timezone = $this->responseValue($response, 'body.data.shop.ianaTimezone', $timezone);

            foreach ($this->responseValue($response, 'body.data.orders.nodes', []) as $node) {
                $row = $this->toRow($node, $currencyCode, $timezone);

                if ($row !== null) {
                    $rows[] = $row;
                }
            }

            $cursor = $this->responseValue($response, 'body.data.orders.pageInfo.endCursor');
            $hasNextPage = $this->responseValue($response, 'body.data.orders.pageInfo.hasNextPage') === true;
            $page++;
        } while ($hasNextPage === true && $page < $maxPages);

        return new OrderReportResultData(
            rows: $rows,
            isTruncated: $hasNextPage,
            currencyCode: $currencyCode,
            timezone: $timezone,
        );
    }

    /**
     * Map an order node to a report row. Orders tagged by the app but missing
     * the order type metafield (e.g. tagged manually) are skipped.
     *
     * @param  array<string, mixed>  $node
     */
    protected function toRow(array $node, string $shopCurrencyCode, string $timezone): ?OrderReportRowData
    {
        $orderType = OrderType::tryFrom((string) ($node['orderType']['value'] ?? ''));

        if ($orderType === null) {
            return null;
        }

        $currencyCode = $node['currentTotalPriceSet']['shopMoney']['currencyCode'] ?? $shopCurrencyCode;
        $breakdown = json_decode((string) ($node['vatBreakdown']['value'] ?? '[]'), true) ?? [];

        return new OrderReportRowData(
            orderId: (string) $node['id'],
            orderName: (string) $node['name'],
            createdAt: CarbonImmutable::parse($node['createdAt'])->setTimezone($timezone),
            channel: $node['sourceName'] ?? null,
            orderType: $orderType,
            currencyCode: $currencyCode,
            totalMinor: MoneyAmount::toMinorUnits($node['currentTotalPriceSet']['shopMoney']['amount'] ?? null, $currencyCode),
            eatInTotalMinor: $this->moneyMetafieldMinor($node['eatInTotal']['value'] ?? null, $currencyCode),
            zeroRatedTotalMinor: $this->moneyMetafieldMinor($node['zeroRatedTotal']['value'] ?? null, $currencyCode),
            customerId: $node['customer']['id'] ?? null,
            customerName: $node['customer']['displayName'] ?? null,
            lines: collect($breakdown)
                ->map(fn (array $line): OrderVatLineData => OrderVatLineData::fromMetafieldArray($line, $currencyCode))
                ->all(),
        );
    }

    protected function moneyMetafieldMinor(?string $value, string $currencyCode): int
    {
        if ($value === null) {
            return 0;
        }

        $money = json_decode($value, true);

        return MoneyAmount::toMinorUnits($money['amount'] ?? null, $currencyCode);
    }
}
