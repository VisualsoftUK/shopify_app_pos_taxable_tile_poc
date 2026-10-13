<?php

namespace Tests\Fixtures;

class EatInFixtures
{
    /**
     * An order line item as returned by OrderVatService::ORDER_QUERY.
     *
     * @return array<string, mixed>
     */
    public static function lineItem(
        string $sku,
        string $unitPrice,
        int $quantity = 1,
        ?string $behaviour = 'dynamic',
        ?string $groupId = null,
        string $title = 'Cheese Sandwich',
        ?string $variantId = null,
        array $customAttributes = [],
    ): array {
        return [
            'id' => 'gid://shopify/LineItem/' . crc32($sku . $title . $groupId),
            'sku' => $sku,
            'title' => $title,
            'variantTitle' => null,
            'currentQuantity' => $quantity,
            'originalUnitPriceSet' => ['shopMoney' => ['amount' => $unitPrice]],
            'discountedUnitPriceAfterAllDiscountsSet' => ['shopMoney' => ['amount' => $unitPrice]],
            'variant' => ['id' => $variantId ?? 'gid://shopify/ProductVariant/' . crc32($sku)],
            'product' => [
                'id' => 'gid://shopify/Product/' . crc32($title),
                'title' => $title,
                'vatBehaviour' => $behaviour === null ? null : ['value' => $behaviour],
            ],
            'lineItemGroup' => $groupId === null ? null : ['id' => $groupId, 'title' => "Eat In: {$title}"],
            'customAttributes' => $customAttributes,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $lineItems
     * @return array<string, mixed>
     */
    public static function order(array $lineItems, ?string $orderType = null, int $id = 1001): array
    {
        return [
            'id' => "gid://shopify/Order/{$id}",
            'name' => "#{$id}",
            'currencyCode' => 'GBP',
            'customAttributes' => $orderType === null ? [] : [['key' => '_order_type', 'value' => $orderType]],
            'lineItems' => ['nodes' => $lineItems],
        ];
    }

    /**
     * An eat-in order: sandwich (dynamic, bundled with a charge), latte (standard) and a coffee bag (zero).
     *
     * @return array<string, mixed>
     */
    public static function eatInOrder(int $id = 1001): array
    {
        return self::order([
            self::lineItem('SAND-1', '3.95', groupId: 'group-1', variantId: 'gid://shopify/ProductVariant/11'),
            self::lineItem('EAT-IN-CHARGE', '0.79', behaviour: null, groupId: 'group-1', title: 'Eat-in charge'),
            self::lineItem('LATTE', '2.95', behaviour: 'standard', title: 'Latte'),
            self::lineItem('BAG-1KG', '18.00', behaviour: 'zero', title: 'Coffee Beans 1kg'),
        ], 'eat_in', $id);
    }

    /**
     * A report order node as returned by OrderReportService::ORDERS_QUERY.
     *
     * @param  array<int, array<string, mixed>>  $breakdown
     * @return array<string, mixed>
     */
    public static function reportNode(
        int $id,
        string $orderType,
        string $total,
        string $eatInTotal,
        string $zeroRatedTotal,
        array $breakdown,
        ?array $customer = null,
        string $createdAt = '2026-10-01T09:00:00Z',
        string $sourceName = 'pos',
    ): array {
        $money = fn (string $amount): array => ['value' => json_encode(['amount' => $amount, 'currency_code' => 'GBP'])];

        return [
            'id' => "gid://shopify/Order/{$id}",
            'name' => "#{$id}",
            'createdAt' => $createdAt,
            'sourceName' => $sourceName,
            'currentTotalPriceSet' => ['shopMoney' => ['amount' => $total, 'currencyCode' => 'GBP']],
            'customer' => $customer,
            'orderType' => ['value' => $orderType],
            'eatInTotal' => $money($eatInTotal),
            'zeroRatedTotal' => $money($zeroRatedTotal),
            'vatBreakdown' => ['value' => json_encode($breakdown)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breakdownLine(string $variantId, string $title, int $quantity, string $lineTotal, string $eatInCharge): array
    {
        return [
            'variantId' => $variantId,
            'productTitle' => $title,
            'variantTitle' => 'Default Title',
            'sku' => null,
            'quantity' => $quantity,
            'lineTotal' => $lineTotal,
            'eatInCharge' => $eatInCharge,
            'upliftPercent' => '20.00',
        ];
    }

    /**
     * A full orders-report GraphQL `data` payload (single page).
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<string, mixed>
     */
    public static function reportData(array $nodes, bool $hasNextPage = false): array
    {
        return [
            'shop' => ['currencyCode' => 'GBP', 'ianaTimezone' => 'Europe/London'],
            'orders' => [
                'pageInfo' => ['hasNextPage' => $hasNextPage, 'endCursor' => $hasNextPage ? 'cursor-1' : null],
                'nodes' => $nodes,
            ],
        ];
    }

    /**
     * Three orders: two eat-in (one guest), one takeaway.
     *
     * @return array<string, mixed>
     */
    public static function standardReportData(): array
    {
        $sandwich = 'gid://shopify/ProductVariant/11';
        $smoothie = 'gid://shopify/ProductVariant/12';
        $customer = ['id' => 'gid://shopify/Customer/7', 'displayName' => 'Jo Bloggs'];

        return self::reportData([
            self::reportNode(1, 'eat_in', '4.74', '0.79', '0.00', [
                self::breakdownLine($sandwich, 'Cheese Sandwich', 1, '3.95', '0.79'),
            ], $customer),
            self::reportNode(2, 'eat_in', '9.48', '1.58', '0.00', [
                self::breakdownLine($sandwich, 'Cheese Sandwich', 2, '7.90', '1.58'),
            ], createdAt: '2026-10-02T23:30:00Z'),
            self::reportNode(3, 'takeaway', '6.90', '0.00', '6.90', [
                self::breakdownLine($sandwich, 'Cheese Sandwich', 1, '3.95', '0.00'),
                self::breakdownLine($smoothie, 'Berry Smoothie', 1, '2.95', '0.00'),
            ], $customer, sourceName: 'web'),
        ]);
    }
}
