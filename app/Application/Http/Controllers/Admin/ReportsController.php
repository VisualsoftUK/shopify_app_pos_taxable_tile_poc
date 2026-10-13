<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use App\Application\Http\Requests\Admin\ReportFilterRequest;
use App\Domain\Actions\Reports\BuildOrderTypeReportAction;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function index(ReportFilterRequest $request): Response
    {
        $filter = $request->toFilterData();

        return Inertia::render('Reports', [
            'filters' => $filter->toQueryParams(),
            'report' => Inertia::defer(
                fn () => BuildOrderTypeReportAction::for($request->user(), $filter)->get()->toArray()
            ),
        ]);
    }
}
