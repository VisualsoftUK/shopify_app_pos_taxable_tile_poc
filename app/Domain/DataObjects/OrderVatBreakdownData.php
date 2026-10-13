<?php

namespace App\Domain\DataObjects;

use App\Domain\Enums\OrderType;
use App\Domain\Support\MoneyAmount;
use Spatie\LaravelData\Data;

class OrderVatBreakdownData extends Data
{
    /**
     * @param  array<int, OrderVatLineData>  $lines  Dynamic (eat-in/takeaway) product lines only.
     */
    public function __construct(
        public string $orderId,
        public string $orderName,
        public OrderType $orderType,
        public string $currencyCode,
        public int $eatInTotalMinor,
        public int $zeroRatedTotalMinor,
        public array $lines,
    ) {}

    /**
     * The `metafieldsSet` inputs that persist this breakdown against the order.
     *
     * @return array<int, array{ownerId: string, namespace: string, key: string, type: string, value: string}>
     */
    public function toMetafieldInputs(): array
    {
        $namespace = config('eat-in.order_metafields.namespace');

        return [
            [
                'ownerId' => $this->orderId,
                'namespace' => $namespace,
                'key' => config('eat-in.order_metafields.order_type_key'),
                'type' => 'single_line_text_field',
                'value' => $this->orderType->value,
            ],
            [
                'ownerId' => $this->orderId,
                'namespace' => $namespace,
                'key' => config('eat-in.order_metafields.eat_in_total_key'),
                'type' => 'money',
                'value' => $this->moneyValue($this->eatInTotalMinor),
            ],
            [
                'ownerId' => $this->orderId,
                'namespace' => $namespace,
                'key' => config('eat-in.order_metafields.zero_rated_total_key'),
                'type' => 'money',
                'value' => $this->moneyValue($this->zeroRatedTotalMinor),
            ],
            [
                'ownerId' => $this->orderId,
                'namespace' => $namespace,
                'key' => config('eat-in.order_metafields.breakdown_key'),
                'type' => 'json',
                'value' => json_encode(
                    collect($this->lines)
                        ->map(fn (OrderVatLineData $line): array => $line->toMetafieldArray($this->currencyCode))
                        ->values()
                        ->all(),
                    JSON_THROW_ON_ERROR
                ),
            ],
        ];
    }

    /**
     * Encode a money metafield value.
     */
    protected function moneyValue(int $amountMinor): string
    {
        return json_encode([
            'amount' => MoneyAmount::toDecimal($amountMinor, $this->currencyCode),
            'currency_code' => $this->currencyCode,
        ], JSON_THROW_ON_ERROR);
    }
}
