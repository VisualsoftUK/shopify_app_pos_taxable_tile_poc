<?php

namespace App\Jobs;

use App\Domain\Actions\Orders\ProcessOrderVatAction;
use Visualsoft\Shopify\Jobs\BaseJob;

/**
 * Calculate, store and (optionally) tag an order's eat-in VAT breakdown.
 */
class ProcessOrderVatJob extends BaseJob
{
    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60];

    public function __construct(
        string $domain,
        public readonly int $orderId,
        public readonly bool $shouldTag = true,
    ) {
        parent::__construct($domain);
    }

    public function childHandle(): bool
    {
        $shop = $this->getShopForDomain();

        if ($this->shouldSkipShop($shop) === true) {
            return true;
        }

        ProcessOrderVatAction::for($shop)->process($this->orderId, $this->shouldTag);

        return true;
    }
}
