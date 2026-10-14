<?php

namespace App\Domain\Actions\Orders;

use App\Domain\Actions\Settings\GetDefaultOrderTypeAction;
use App\Domain\DataObjects\OrderVatBreakdownData;
use App\Domain\Services\OrderVatService;
use Illuminate\Support\Facades\Log;
use Visualsoft\Shopify\Actions\Orders\AddTagsToOrder;
use Visualsoft\Shopify\Concerns\PrependsShopifyGids;
use Visualsoft\Shopify\Contracts\VsShopModel;

/**
 * Fetch an order, calculate its eat-in VAT breakdown, write it to the order's
 * metafields and (optionally) tag the order as Eat In or Takeaway.
 */
class ProcessOrderVatAction
{
    use PrependsShopifyGids;

    public function __construct(
        protected VsShopModel $shop
    ) {}

    public static function for(VsShopModel $shop): static
    {
        return new static(shop: $shop);
    }

    public function process(int $orderId, bool $shouldTag = true): ?OrderVatBreakdownData
    {
        $orderGid = $this->prependOrderGid((string) $orderId);
        $service = OrderVatService::for($this->shop);

        $order = $service->fetchOrder($orderGid);

        if ($order === null) {
            Log::warning('[ProcessOrderVat] Order not found', ['shop' => $this->shop->name, 'order' => $orderGid]);

            return null;
        }

        $breakdown = CalculateOrderVatBreakdownAction::for(
            $order,
            GetDefaultOrderTypeAction::for($this->shop)->get()
        )->calculate();

        $service->saveBreakdown($breakdown);

        if ($shouldTag === true) {
            AddTagsToOrder::for($this->shop)->execute($orderId, [$breakdown->orderType->label()]);
        }

        Log::info('[ProcessOrderVat] Order processed', [
            'shop' => $this->shop->name,
            'order' => $orderGid,
            'order_type' => $breakdown->orderType->value,
            'eat_in_total_minor' => $breakdown->eatInTotalMinor,
        ]);

        return $breakdown;
    }
}
