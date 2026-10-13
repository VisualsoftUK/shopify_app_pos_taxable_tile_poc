<?php

namespace App\Jobs;

use Visualsoft\Shopify\Jobs\ShopifyWebhooks\BaseShopifyJob;

/**
 * refunds/create webhook: recalculate the order's eat-in totals net of the
 * refund. The order type tag is left unchanged.
 */
class RefundsCreateJob extends BaseShopifyJob
{
    public function handle(): void
    {
        ProcessOrderVatJob::dispatch($this->domain, (int) $this->data->order_id, false);
    }
}
