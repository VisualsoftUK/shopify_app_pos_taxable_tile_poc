<?php

namespace App\Framework\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register broadcast auth routes
        // Use 'admin' middleware group if available, otherwise fall back to 'verify.shopify'
        $middleware = app('router')->hasMiddlewareGroup('admin') === true ? 'admin' : 'verify.shopify';
        Broadcast::routes(['middleware' => [$middleware]]);

        require base_path('routes/channels.php');
    }
}
