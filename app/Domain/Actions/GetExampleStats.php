<?php

namespace App\Domain\Actions;

use App\Domain\Concerns\FormatsNumbers;
use Visualsoft\Shopify\Objects\Shop;
use Visualsoft\Shopify\Objects\ShopLocale;

class GetExampleStats
{
    use FormatsNumbers;

    public function __construct(
        protected string $timeframe
    ) {}

    public static function for(string $timeframe): static
    {
        return new static(
            timeframe: $timeframe
        );
    }

    public function get(): array
    {
        // Parse timeframe to vary the example data
        $multiplier = $this->getMultiplierFromTimeframe($this->timeframe);

        // Get currency and locale from shop (same as GetOverallStats)
        $currencyCode = (new Shop)->getDefaultCurrencyCode();
        $locale = (new ShopLocale)->defaultLocale();

        return [
            [
                'title' => 'dashboard.stats.example_stat_1',
                'value' => $this->formatNumber(245 * $multiplier),
                'tooltip' => 'dashboard.stats.example_stat_1_tooltip',
            ],
            [
                'title' => 'dashboard.stats.example_stat_2',
                'value' => $this->formatRevenue(124700 * $multiplier, $currencyCode, $locale),
                'tooltip' => 'dashboard.stats.example_stat_2_tooltip',
            ],
            [
                'title' => 'dashboard.stats.example_stat_3',
                'value' => $this->formatNumber(185 * $multiplier),
                'tooltip' => 'dashboard.stats.example_stat_3_tooltip',
            ],
            [
                'title' => 'dashboard.stats.example_stat_4',
                'value' => $this->formatRevenue(54300 * $multiplier, $currencyCode, $locale),
                'tooltip' => 'dashboard.stats.example_stat_4_tooltip',
            ],
        ];
    }

    protected function getMultiplierFromTimeframe(string $timeframe): int
    {
        // Parse timeframe and return different multipliers
        // to show data changing based on filter
        return match ($timeframe) {
            'daily_7' => 1,
            'daily_30' => 4,
            'daily_60' => 8,
            'daily_90' => 12,
            'monthly_6' => 24,
            'monthly_12' => 48,
            'all' => 200,
            default => 4,
        };
    }
}
