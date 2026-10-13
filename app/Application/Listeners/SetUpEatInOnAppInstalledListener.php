<?php

namespace App\Application\Listeners;

use App\Domain\Models\User;
use App\Jobs\SetUpEatInJob;
use Illuminate\Support\Facades\Log;
use Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent;

/**
 * Provision the eat-in setup (metafield definitions, charge product and Cart
 * Transform) as soon as a shop installs the app.
 */
class SetUpEatInOnAppInstalledListener
{
    public function handle(AppInstalledEvent $event): void
    {
        $shop = User::query()->find($event->shopId->toNative());

        if ($shop === null) {
            Log::warning('[SetUpEatInOnAppInstalled] Shop not found', ['shop_id' => $event->shopId->toNative()]);

            return;
        }

        SetUpEatInJob::dispatch($shop);
    }
}
