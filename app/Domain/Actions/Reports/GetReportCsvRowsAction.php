<?php

namespace App\Domain\Actions\Reports;

use App\Domain\DataObjects\OrderTypeReportData;
use App\Domain\Enums\OrderType;
use App\Domain\Enums\ReportExportType;
use App\Domain\Support\MoneyAmount;
use Illuminate\Support\Collection;

/**
 * Flatten a report section into CSV rows (header first). Money is written as
 * plain decimals in the shop currency so it can be summed in a spreadsheet.
 */
class GetReportCsvRowsAction
{
    public function __construct(
        protected OrderTypeReportData $report,
        protected ReportExportType $type,
    ) {}

    public static function for(OrderTypeReportData $report, ReportExportType $type): static
    {
        return new static(report: $report, type: $type);
    }

    /**
     * @return Collection<int, array<int, string|int|null>>
     */
    public function get(): Collection
    {
        return match ($this->type) {
            ReportExportType::Orders => $this->orders(),
            ReportExportType::Daily => $this->daily(),
            ReportExportType::Variants => $this->variants(),
            ReportExportType::Customers => $this->customers(),
        };
    }

    /**
     * @return Collection<int, array<int, string|int|null>>
     */
    protected function orders(): Collection
    {
        return collect($this->report->orders)
            ->map(fn (array $order): array => [
                $order['orderName'],
                $order['createdAt'],
                $order['channel'],
                $this->orderTypeLabel($order['orderType']),
                $order['customerName'],
                $this->money($order['totalMinor']),
                $this->money($order['eatInTotalMinor']),
                $this->money($order['zeroRatedTotalMinor']),
            ])
            ->prepend(['Order', 'Created at', 'Channel', 'Order type', 'Customer', 'Order total', 'Eat-in charge', 'Zero-rated value']);
    }

    /**
     * @return Collection<int, array<int, string|int|null>>
     */
    protected function daily(): Collection
    {
        return collect($this->report->daily)
            ->map(fn (array $day): array => [
                $day['date'],
                $day['eatInCount'],
                $this->money($day['eatInTotalMinor']),
                $this->money($day['eatInChargeMinor']),
                $day['takeawayCount'],
                $this->money($day['takeawayTotalMinor']),
                $this->money($day['zeroRatedMinor']),
            ])
            ->prepend(['Date', 'Eat-in orders', 'Eat-in value', 'Eat-in charge', 'Takeaway orders', 'Takeaway value', 'Zero-rated value']);
    }

    /**
     * @return Collection<int, array<int, string|int|null>>
     */
    protected function variants(): Collection
    {
        return collect($this->report->variants)
            ->map(fn (array $variant): array => [
                $variant['productTitle'],
                $variant['variantTitle'],
                $variant['sku'],
                $variant['variantId'],
                $this->orderTypeLabel($variant['orderType']),
                $variant['orderCount'],
                $variant['quantity'],
                $this->money($variant['lineTotalMinor']),
                $this->money($variant['eatInChargeMinor']),
            ])
            ->prepend(['Product', 'Variant', 'SKU', 'Variant ID', 'Order type', 'Orders', 'Quantity', 'Line value', 'Eat-in charge']);
    }

    /**
     * @return Collection<int, array<int, string|int|null>>
     */
    protected function customers(): Collection
    {
        return collect($this->report->customers)
            ->map(fn (array $customer): array => [
                $customer['customerName'] ?? 'Guest',
                $customer['customerId'],
                $this->orderTypeLabel($customer['orderType']),
                $customer['orderCount'],
                $this->money($customer['totalMinor']),
                $this->money($customer['eatInChargeMinor']),
                $this->money($customer['zeroRatedMinor']),
            ])
            ->prepend(['Customer', 'Customer ID', 'Order type', 'Orders', 'Order value', 'Eat-in charge', 'Zero-rated value']);
    }

    protected function money(int $amountMinor): string
    {
        return MoneyAmount::toDecimal($amountMinor, $this->report->currencyCode);
    }

    protected function orderTypeLabel(string $orderType): string
    {
        return OrderType::from($orderType)->label();
    }
}
