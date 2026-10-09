<?php

namespace App\Framework\Providers;

use Illuminate\Support\Facades\Route;
use Visualsoft\LaravelSecurity\Framework\Providers\RouteServiceProvider as BaseRouteServiceProvider;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        parent::boot();

        $this->routes(function () {
            // api routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // web routes
            Route::middleware('web')->group(base_path('routes/web.php'));

            // shopify routes
            Route::middleware('admin')->prefix('/app')->group(base_path('routes/admin.php'));
        });
    }
}
