<?php

namespace App\Domain\Actions\Reports;

use App\Domain\Concerns\FormatsNumbers;
use App\Domain\DataObjects\ReportFilterData;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\Shopify\Objects\ShopLocale;

/**
 * Dashboard StatsBar cards for eat-in / takeaway activity in a timeframe.
 */
class GetOrderTypeStatsAction
{
    use FormatsNumbers;

    public function __construct(
        protected VsShopModel $shop,
        protected string $timeframe,
    ) {}

    public static function for(VsShopModel $shop, string $timeframe): static
    {
        return new static(shop: $shop, timeframe: $timeframe);
    }

    /**
     * @return array<int, array{title: string, value: string, tooltip: string}>
     */
    public function get(): array
    {
        $report = BuildOrderTypeReportAction::for($this->shop, ReportFilterData::fromTimeframe($this->timeframe))->get();
        $locale = (new ShopLocale)->defaultLocale();
        $currencyCode = $report->currencyCode;
        $eatIn = $report->summary['eatIn'];
        $takeaway = $report->summary['takeaway'];

        return [
            $this->stat('eat_in_orders', $this->formatNumber($eatIn['orderCount'])),
            $this->stat('eat_in_charges', $this->formatRevenue($eatIn['eatInChargeMinor'], $currencyCode, $locale)),
            $this->stat('takeaway_orders', $this->formatNumber($takeaway['orderCount'])),
            $this->stat('zero_rated_value', $this->formatRevenue($takeaway['zeroRatedMinor'], $currencyCode, $locale)),
            $this->stat('eat_in_share', number_format($report->summary['eatInSharePercent'], 1) . '%'),
            $this->stat('average_eat_in_charge', $this->formatRevenue($eatIn['averageChargeMinor'], $currencyCode, $locale)),
        ];
    }

    /**
     * @return array{title: string, value: string, tooltip: string}
     */
    protected function stat(string $key, string $value): array
    {
        return [
            'title' => "dashboard.stats.{$key}",
            'value' => $value,
            'tooltip' => "dashboard.stats.{$key}_tooltip",
        ];
    }
}
