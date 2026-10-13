<?php

namespace App\Domain\DataObjects;

use Visualsoft\Shopify\DataObjects\Data;

class CustomSettingsData extends Data
{
    protected static string $metafieldsNamespace = 'settings';

    /**
     * @param  string  $defaultOrderType  One of the OrderType values; used when a cart or order has no order type set.
     */
    public function __construct(public string $defaultOrderType = 'takeaway')
    {
        //
    }
}
