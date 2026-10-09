<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use Visualsoft\Shopify\Facades\CurrentAppInstallation;

class ThemeController extends Controller
{
    /**
     * Check if app is active in theme
     */
    public function __invoke(): array
    {
        return CurrentAppInstallation::checkAppStatusInTheme();
    }
}
