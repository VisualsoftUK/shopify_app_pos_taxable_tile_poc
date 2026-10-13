<?php

namespace App\Domain\DataObjects;

use App\Domain\Support\MoneyAmount;
use Spatie\LaravelData\Data;

class OrderVatLineData extends Data
{
    public function __construct(
        public ?string $variantId,
        public string $productTitle,
        public ?string $variantTitle,
        public ?string $sku,
        public int $quantity,
        public int $lineTotalMinor,
        public int $eatInChargeMinor,
    ) {}

    /**
     * The surcharge as a percentage of the line total, to two decimal places.
     */
    public function upliftPercent(): string
    {
        if ($this->lineTotalMinor === 0) {
            return '0.00';
        }

        return number_format(($this->eatInChargeMinor / $this->lineTotalMinor) * 100, 2, '.', '');
    }

    /**
     * Whether this dynamic line was sold for eating in.
     */
    public function hasEatInCharge(): bool
    {
        return $this->eatInChargeMinor > 0;
    }

    /**
     * Shape stored in the `$app.vat_breakdown` order metafield.
     *
     * @return array{variantId: string|null, productTitle: string, variantTitle: string|null, sku: string|null, quantity: int, lineTotal: string, eatInCharge: string, upliftPercent: string}
     */
    public function toMetafieldArray(string $currencyCode): array
    {
        return [
            'variantId' => $this->variantId,
            'productTitle' => $this->productTitle,
            'variantTitle' => $this->variantTitle,
            'sku' => $this->sku,
            'quantity' => $this->quantity,
            'lineTotal' => MoneyAmount::toDecimal($this->lineTotalMinor, $currencyCode),
            'eatInCharge' => MoneyAmount::toDecimal($this->eatInChargeMinor, $currencyCode),
            'upliftPercent' => $this->upliftPercent(),
        ];
    }

    /**
     * Rebuild a line from the `$app.vat_breakdown` order metafield.
     *
     * @param  array{variantId?: string|null, productTitle?: string, variantTitle?: string|null, sku?: string|null, quantity?: int, lineTotal?: string, eatInCharge?: string}  $line
     */
    public static function fromMetafieldArray(array $line, string $currencyCode): self
    {
        return new self(
            variantId: $line['variantId'] ?? null,
            productTitle: (string) ($line['productTitle'] ?? ''),
            variantTitle: $line['variantTitle'] ?? null,
            sku: $line['sku'] ?? null,
            quantity: (int) ($line['quantity'] ?? 0),
            lineTotalMinor: MoneyAmount::toMinorUnits($line['lineTotal'] ?? null, $currencyCode),
            eatInChargeMinor: MoneyAmount::toMinorUnits($line['eatInCharge'] ?? null, $currencyCode),
        );
    }
}
