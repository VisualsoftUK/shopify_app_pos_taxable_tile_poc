<?php

namespace App\Application\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Visualsoft\Mantle\Concerns\BuildsMantlePlanData;
use Visualsoft\ShopifyAppPricing\Contracts\HasAppPricingState;
use Visualsoft\ShopifyAppPricing\Support\SharedAppPricingData;

class HandleInertiaRequests extends Middleware
{
    use BuildsMantlePlanData;

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'admin.app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appHandle' => config('shopify-ext.name'),
            'mantlePlan' => fn () => $this->mantlePlan($request),
            'appPricing' => fn (): array => $this->sharedAppPricing($request),
        ];
    }

    /**
     * Hosted plan URLs and entitlements for the Plans nav hop.
     *
     * @return array<string, mixed>
     */
    protected function sharedAppPricing(Request $request): array
    {
        return once(fn (): array => SharedAppPricingData::forRequest($request));
    }

    /**
     * Build the Mantle plan data (plan info + feature entitlements) shared with the frontend.
     *
     * App Pricing entitlements are returned when a plan is assigned so existing Mantle
     * checks keep working. Otherwise the local Mantle plan is used.
     *
     * @return array{mantle_plan: array<string, mixed>, features?: array<int, array<string, mixed>>}
     */
    protected function mantlePlan(Request $request): array
    {
        $shop = Auth::user();

        if ($shop instanceof HasAppPricingState) {
            $shop->loadMissing(['appPricingState.plan']);

            if ($shop->appPricingState?->plan !== null) {
                return $this->sharedAppPricing($request)['entitlements'];
            }
        }

        if ($shop?->mantle?->plan === null) {
            return [
                'mantle_plan' => [
                    'is_active' => false,
                ],
            ];
        }

        return $this->buildMantlePlanData($shop, $shop->mantle);
    }
}
