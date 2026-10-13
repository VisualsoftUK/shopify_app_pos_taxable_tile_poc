<?php

namespace App\Application\Http\Requests\Admin;

use App\Domain\DataObjects\ReportFilterData;
use App\Domain\Enums\OrderType;
use App\Domain\Enums\ReportChannel;
use App\Domain\Enums\ReportExportType;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'order_type' => ['nullable', Rule::in(['all', ...array_column(OrderType::cases(), 'value')])],
            'channel' => ['nullable', Rule::enum(ReportChannel::class)],
            'type' => ['nullable', Rule::enum(ReportExportType::class)],
        ];
    }

    /**
     * The validated filter, defaulting to the last 30 days across all order types and channels.
     */
    public function toFilterData(): ReportFilterData
    {
        $today = CarbonImmutable::today();
        $from = $this->validated('from');
        $to = $this->validated('to');

        return new ReportFilterData(
            from: $from !== null ? CarbonImmutable::parse($from) : $today->subDays(29),
            to: $to !== null ? CarbonImmutable::parse($to) : $today,
            orderType: OrderType::tryFrom((string) $this->validated('order_type')),
            channel: ReportChannel::tryFrom((string) $this->validated('channel')) ?? ReportChannel::All,
        );
    }

    public function exportType(): ReportExportType
    {
        return ReportExportType::tryFrom((string) $this->validated('type')) ?? ReportExportType::Orders;
    }
}
