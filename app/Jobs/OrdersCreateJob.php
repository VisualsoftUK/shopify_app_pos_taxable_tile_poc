<?php

namespace App\Jobs;

use Visualsoft\Shopify\Jobs\ShopifyWebhooks\BaseShopifyJob;

/**
 * orders/create webhook: classify the order as eat-in or takeaway.
 */
class OrdersCreateJob extends BaseShopifyJob
{
    public function handle(): void
    {
        ProcessOrderVatJob::dispatch($this->domain, (int) $this->data->id, true);
    }
}
