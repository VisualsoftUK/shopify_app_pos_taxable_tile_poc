<?php

namespace App\Application\Http\Controllers\Admin;

use App\Application\Http\Controllers\Controller;
use Visualsoft\Shopify\Support\ThemeAppExtension;

class InstallUrlController extends Controller
{
    /**
     * Get the install URL for the app
     */
    public function __invoke()
    {
        $installUrl = ThemeAppExtension::installUrl();

        if ($installUrl) {
            return response()->json(
                [
                    'success' => true,
                    'installUrl' => $installUrl,
                ]
            );
        }

        return response()->json(
            [
                'success' => false,
                'error' => 'Install URL not available',
            ],
            400
        );
    }
}
