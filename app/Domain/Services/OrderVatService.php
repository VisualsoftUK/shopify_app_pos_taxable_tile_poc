<?php

namespace App\Domain\Services;

use App\Domain\Concerns\ReadsGraphQlResponses;
use App\Domain\DataObjects\OrderVatBreakdownData;
use App\Domain\Enums\OrderType;
use App\Domain\Exceptions\ShopifyUserErrorsException;
use Carbon\CarbonImmutable;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\Shopify\Services\ShopifyGraphQlService;

class OrderVatService
{
    use ReadsGraphQlResponses;

    protected const string ORDER_QUERY = <<<'GRAPHQL'
        query OrderVat($id: ID!, $productNamespace: String!, $behaviourKey: String!) {
            order(id: $id) {
                id
                name
                currencyCode
                customAttributes { key value }
                lineItems(first: 100) {
                    nodes {
                        id
                        sku
                        title
                        variantTitle
                        currentQuantity
                        originalUnitPriceSet { shopMoney { amount } }
                        discountedUnitPriceAfterAllDiscountsSet { shopMoney { amount } }
                        variant { id }
                        product {
                            id
                            title
                            vatBehaviour: metafield(namespace: $productNamespace, key: $behaviourKey) { value }
                        }
                        lineItemGroup { id title }
                        customAttributes { key value }
                    }
                }
            }
        }
        GRAPHQL;

    protected const string SAVE_METAFIELDS_MUTATION = <<<'GRAPHQL'
        mutation SaveOrderVat($metafields: [MetafieldsSetInput!]!) {
            metafieldsSet(metafields: $metafields) {
                metafields { id }
                userErrors { field message code }
            }
        }
        GRAPHQL;

    protected const string UNCLASSIFIED_ORDERS_QUERY = <<<'GRAPHQL'
        query UnclassifiedOrders($query: String!, $after: String) {
            orders(first: 100, after: $after, query: $query, sortKey: CREATED_AT) {
                pageInfo { hasNextPage endCursor }
                nodes { legacyResourceId }
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

    /**
     * Fetch the order fields needed to calculate its eat-in VAT breakdown.
     *
     * @return array<string, mixed>|null
     */
    public function fetchOrder(string $orderGid): ?array
    {
        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::ORDER_QUERY)
            ->withParams([
                'id' => $orderGid,
                'productNamespace' => config('eat-in.product_metafields.namespace'),
                'behaviourKey' => config('eat-in.product_metafields.behaviour_key'),
            ])
            ->get();

        return $this->responseValue($response, 'body.data.order');
    }

    /**
     * Persist the breakdown as app-owned order metafields.
     *
     * @throws ShopifyUserErrorsException
     */
    public function saveBreakdown(OrderVatBreakdownData $breakdown): void
    {
        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::SAVE_METAFIELDS_MUTATION)
            ->withParams(['metafields' => $breakdown->toMetafieldInputs()])
            ->get();

        $this->assertNoUserErrors($response, 'metafieldsSet');
    }

    /**
     * Numeric IDs of orders created since the given date that have not been
     * tagged as eat-in or takeaway.
     *
     * @return array<int, int>
     */
    public function findUnclassifiedOrderIds(CarbonImmutable $since, int $maxPages = 20): array
    {
        $query = sprintf(
            "created_at:>=%s tag_not:'%s' tag_not:'%s'",
            $since->toDateString(),
            OrderType::EatIn->label(),
            OrderType::Takeaway->label(),
        );

        $orderIds = [];
        $cursor = null;
        $page = 0;

        do {
            $response = ShopifyGraphQlService::for($this->shop)
                ->withQuery(self::UNCLASSIFIED_ORDERS_QUERY)
                ->withParams(['query' => $query, 'after' => $cursor])
                ->get();

            foreach ($this->responseValue($response, 'body.data.orders.nodes', []) as $node) {
                $orderIds[] = (int) $node['legacyResourceId'];
            }

            $cursor = $this->responseValue($response, 'body.data.orders.pageInfo.endCursor');
            $hasNextPage = $this->responseValue($response, 'body.data.orders.pageInfo.hasNextPage') === true;
            $page++;
        } while ($hasNextPage === true && $page < $maxPages);

        return $orderIds;
    }
}
