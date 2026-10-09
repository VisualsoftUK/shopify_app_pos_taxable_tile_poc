<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use App\Domain\Actions\GetExampleStats;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Visualsoft\Shopify\Support\ThemeAppExtension;
use Visualsoft\ShopifyAdmin\Actions\Common\GetSetUpSteps;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $timeframe = $request->input('timeframe', 'daily_30');

        return Inertia::render('Dashboard', [
            'installUrl' => ThemeAppExtension::installUrl(),
            'stats' => Inertia::optional(
                fn () => GetExampleStats::for($timeframe)->get(),
                'stats'
            ),
            'setupStepsStatus' => GetSetUpSteps::for($request->user())->get(),
        ]);
    }
}
