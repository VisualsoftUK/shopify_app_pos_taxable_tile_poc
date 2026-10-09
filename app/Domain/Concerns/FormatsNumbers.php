<?php

namespace App\Domain\Concerns;

use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;
use NumberFormatter;

trait FormatsNumbers
{
    /**
     * Format a revenue amount to a currency string. This will use the correct subunit for the currency.
     */
    protected function formatRevenue(int $revenue, string $currencyCode, string $locale = 'en_GB'): string
    {
        $money = new Money($revenue, new Currency($currencyCode));
        $currencies = new ISOCurrencies;

        $numberFormatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
    }

    /**
     * Format a number to a string with commas.
     */
    protected function formatNumber(int $number): string
    {
        return number_format($number, 0, '.', ',');
    }

    /**
     * Convert a revenue amount to an integer. This will use the correct subunit for the currency.
     */
    protected function convertRevenueToInteger(float $revenue, string $currencyCode): int
    {
        $currencies = new ISOCurrencies;
        $currency = new Currency($currencyCode);

        $parser = new DecimalMoneyParser($currencies);

        return $parser->parse($revenue, $currency)->getAmount();
    }

    /**
     * Convert a revenue amount to a float. This will use the correct subunit for the currency.
     */
    protected function convertRevenueToFloat(int $revenue, string $currencyCode): float
    {
        $money = new Money($revenue, new Currency($currencyCode));
        $currencies = new ISOCurrencies;

        $formatter = new DecimalMoneyFormatter($currencies);

        return $formatter->format($money);
    }
}
