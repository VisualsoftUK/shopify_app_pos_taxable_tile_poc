<?php

namespace App\Domain\Support;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

class MoneyAmount
{
    /**
     * Parse a decimal amount (as returned by Shopify, e.g. "3.95") into minor units for the currency.
     */
    public static function toMinorUnits(string|int|float|null $amount, string $currencyCode): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $parser = new DecimalMoneyParser(new ISOCurrencies);

        return (int) $parser->parse((string) $amount, new Currency($currencyCode))->getAmount();
    }

    /**
     * Format minor units as a plain decimal string (e.g. 138 GBP => "1.38").
     */
    public static function toDecimal(int $amountMinor, string $currencyCode): string
    {
        $formatter = new DecimalMoneyFormatter(new ISOCurrencies);

        return $formatter->format(new Money($amountMinor, new Currency($currencyCode)));
    }
}
