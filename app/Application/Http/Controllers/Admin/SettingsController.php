<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Visualsoft\Shopify\Support\ThemeAppExtension;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings', [
            'installUrl' => ThemeAppExtension::installUrl(),
        ]);
    }
}
