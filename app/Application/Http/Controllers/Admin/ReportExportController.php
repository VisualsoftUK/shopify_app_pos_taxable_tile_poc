<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use App\Application\Http\Requests\Admin\ReportFilterRequest;
use App\Domain\Actions\Reports\BuildOrderTypeReportAction;
use App\Domain\Actions\Reports\GetReportCsvRowsAction;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Stream a report section as CSV, built live from Shopify orders.
 */
class ReportExportController extends Controller
{
    public function __invoke(ReportFilterRequest $request): StreamedResponse
    {
        $filter = $request->toFilterData();
        $type = $request->exportType();
        $report = BuildOrderTypeReportAction::for($request->user(), $filter)->get();
        $rows = GetReportCsvRowsAction::for($report, $type)->get();

        $filename = sprintf(
            'eat-in-%s-%s-to-%s.csv',
            $type->value,
            $filter->from?->toDateString() ?? 'start',
            $filter->to->toDateString()
        );

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row, escape: '');
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
