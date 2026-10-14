<?php

namespace App\Jobs;

use App\Domain\Actions\Settings\GetDefaultOrderTypeAction;
use App\Domain\Services\EatInSetupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Visualsoft\Shopify\Contracts\VsShopModel;

/**
 * Provision metafield definitions, the eat-in charge product and the Cart
 * Transform. Dispatched when the app is installed; the set-up is idempotent,
 * so re-running it only fills in anything that is missing.
 */
class SetUpEatInJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public VsShopModel $shop,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->shop->id;
    }

    public function handle(): void
    {
        EatInSetupService::for($this->shop)->setUp(
            GetDefaultOrderTypeAction::for($this->shop)->get()
        );
    }
}
