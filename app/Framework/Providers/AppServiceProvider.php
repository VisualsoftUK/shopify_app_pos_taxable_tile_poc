<?php

namespace App\Framework\Providers;

use App\Domain\Models\User;
use App\Framework\Support\DisabledPartnerApiClient;
use Illuminate\Support\Facades\Auth;
use Osiset\ShopifyApp\Contracts\ShopModel;
use Visualsoft\LaravelSecurity\Framework\Providers\AppServiceProvider as BaseAppServiceProvider;
use Visualsoft\ShopifyAppPricing\Client\PartnerApiClient;

class AppServiceProvider extends BaseAppServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(ShopModel::class, function ($app) {
            return Auth::user() ?? $app->make(User::class);
        });

        if (config('services.shopify_partner_api.enabled') !== true) {
            $this->app->singleton(PartnerApiClient::class, DisabledPartnerApiClient::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Merge the usage metrics config with the custom config
        // This is to allow us to add custom usage metrics to the app
        config([
            'vs-mantle.usage_metrics' => array_merge(
                config('vs-mantle.usage_metrics', []),
                config('vs-mantle-usage-metrics', [])
            ),
        ]);
    }
}
