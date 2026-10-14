<?php

namespace App\Domain\Actions\Orders;

use App\Domain\DataObjects\OrderVatBreakdownData;
use App\Domain\DataObjects\OrderVatLineData;
use App\Domain\Enums\OrderType;
use App\Domain\Enums\VatBehaviour;
use App\Domain\Support\MoneyAmount;
use Illuminate\Support\Collection;

/**
 * Works out the eat-in surcharge and zero-rated totals for an order fetched by
 * OrderVatService::fetchOrder(). Eat-in charge lines (identified by SKU) are
 * paired to their product line by bundle group, falling back to the
 * `_eat_in_for` attribute written by the Cart Transform function.
 */
class CalculateOrderVatBreakdownAction
{
    /**
     * Remaining (unassigned) eat-in charge per pairing key, in minor units.
     *
     * @var array<string, int>
     */
    protected array $unassignedCharges = [];

    /**
     * @param  array<string, mixed>  $order
     */
    public function __construct(
        protected array $order,
        protected OrderType $defaultOrderType,
    ) {}

    /**
     * @param  array<string, mixed>  $order
     */
    public static function for(array $order, OrderType $defaultOrderType): static
    {
        return new static(order: $order, defaultOrderType: $defaultOrderType);
    }

    public function calculate(): OrderVatBreakdownData
    {
        $currencyCode = (string) ($this->order['currencyCode'] ?? 'GBP');

        $lineItems = collect($this->order['lineItems']['nodes'] ?? [])
            ->filter(fn (array $lineItem): bool => (int) ($lineItem['currentQuantity'] ?? 0) > 0);

        list($chargeLines, $productLines) = $lineItems->partition(
            fn (array $lineItem): bool => $this->isChargeLine($lineItem)
        );

        $eatInTotalMinor = $this->collectCharges($chargeLines, $currencyCode);

        $dynamicLines = $productLines
            ->filter(fn (array $lineItem): bool => $this->isDynamic($lineItem))
            ->map(fn (array $lineItem): OrderVatLineData => $this->toLineData($lineItem, $currencyCode))
            ->values();

        $zeroRatedTotalMinor = $dynamicLines
            ->reject(fn (OrderVatLineData $line): bool => $line->hasEatInCharge())
            ->sum(fn (OrderVatLineData $line): int => $line->lineTotalMinor);

        return new OrderVatBreakdownData(
            orderId: (string) $this->order['id'],
            orderName: (string) ($this->order['name'] ?? ''),
            orderType: $this->resolveOrderType($eatInTotalMinor),
            currencyCode: $currencyCode,
            eatInTotalMinor: $eatInTotalMinor,
            zeroRatedTotalMinor: $zeroRatedTotalMinor,
            lines: $dynamicLines->all(),
        );
    }

    /**
     * Total all eat-in charge lines and index them by pairing key.
     *
     * @param  Collection<int, array<string, mixed>>  $chargeLines
     */
    protected function collectCharges(Collection $chargeLines, string $currencyCode): int
    {
        $this->unassignedCharges = [];

        foreach ($chargeLines as $chargeLine) {
            $pairingKey = $this->chargePairingKey($chargeLine);
            $amount = $this->lineTotalMinor($chargeLine, $currencyCode);

            $this->unassignedCharges[$pairingKey] = ($this->unassignedCharges[$pairingKey] ?? 0) + $amount;
        }

        return array_sum($this->unassignedCharges);
    }

    /**
     * @param  array<string, mixed>  $lineItem
     */
    protected function toLineData(array $lineItem, string $currencyCode): OrderVatLineData
    {
        return new OrderVatLineData(
            variantId: $lineItem['variant']['id'] ?? null,
            productTitle: (string) ($lineItem['product']['title'] ?? $lineItem['title'] ?? ''),
            variantTitle: $lineItem['variantTitle'] ?? null,
            sku: $lineItem['sku'] ?? null,
            quantity: (int) $lineItem['currentQuantity'],
            lineTotalMinor: $this->lineTotalMinor($lineItem, $currencyCode),
            eatInChargeMinor: $this->claimCharge($lineItem),
        );
    }

    /**
     * Take the eat-in charge paired with this product line. Each charge is
     * claimed once so duplicate variants without bundle groups are not double counted.
     *
     * @param  array<string, mixed>  $lineItem
     */
    protected function claimCharge(array $lineItem): int
    {
        $pairingKey = $this->productPairingKey($lineItem);
        $charge = $this->unassignedCharges[$pairingKey] ?? 0;

        $this->unassignedCharges[$pairingKey] = 0;

        return $charge;
    }

    protected function resolveOrderType(int $eatInTotalMinor): OrderType
    {
        $attribute = collect($this->order['customAttributes'] ?? [])
            ->firstWhere('key', config('eat-in.order_type_attribute'));

        $orderType = OrderType::tryFrom((string) ($attribute['value'] ?? ''));

        if ($orderType !== null) {
            return $orderType;
        }

        if ($eatInTotalMinor > 0) {
            return OrderType::EatIn;
        }

        return $this->defaultOrderType;
    }

    /**
     * @param  array<string, mixed>  $lineItem
     */
    protected function isChargeLine(array $lineItem): bool
    {
        return ($lineItem['sku'] ?? null) === config('eat-in.charge_product.sku');
    }

    /**
     * @param  array<string, mixed>  $lineItem
     */
    protected function isDynamic(array $lineItem): bool
    {
        return ($lineItem['product']['vatBehaviour']['value'] ?? null) === VatBehaviour::Dynamic->value;
    }

    /**
     * @param  array<string, mixed>  $lineItem
     */
    protected function lineTotalMinor(array $lineItem, string $currencyCode): int
    {
        $unitPrice = $lineItem['discountedUnitPriceAfterAllDiscountsSet']['shopMoney']['amount']
            ?? $lineItem['originalUnitPriceSet']['shopMoney']['amount']
            ?? null;

        return MoneyAmount::toMinorUnits($unitPrice, $currencyCode) * (int) $lineItem['currentQuantity'];
    }

    /**
     * @param  array<string, mixed>  $chargeLine
     */
    protected function chargePairingKey(array $chargeLine): string
    {
        $groupId = $chargeLine['lineItemGroup']['id'] ?? null;

        if ($groupId !== null) {
            return "group:{$groupId}";
        }

        $pairedVariantId = collect($chargeLine['customAttributes'] ?? [])->firstWhere('key', '_eat_in_for')['value'] ?? null;

        return "variant:{$pairedVariantId}";
    }

    /**
     * @param  array<string, mixed>  $lineItem
     */
    protected function productPairingKey(array $lineItem): string
    {
        $groupId = $lineItem['lineItemGroup']['id'] ?? null;

        if ($groupId !== null) {
            return "group:{$groupId}";
        }

        $variantId = $lineItem['variant']['id'] ?? null;

        return "variant:{$variantId}";
    }
}
