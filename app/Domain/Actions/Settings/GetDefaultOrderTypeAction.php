<?php

namespace App\Domain\Actions\Settings;

use App\Domain\DataObjects\CustomSettingsData;
use App\Domain\Enums\OrderType;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\Shopify\Enum\UserSettingsType;
use Visualsoft\Shopify\Services\UserSettingService;

class GetDefaultOrderTypeAction
{
    public function __construct(
        protected VsShopModel $shop
    ) {}

    public static function for(VsShopModel $shop): static
    {
        return new static(shop: $shop);
    }

    /**
     * The shop's preferred order type when a cart/order does not specify one.
     */
    public function get(): OrderType
    {
        /** @var CustomSettingsData $settings */
        $settings = UserSettingService::for($this->shop)
            ->as(CustomSettingsData::class)
            ->type(UserSettingsType::METAFIELDS)
            ->get();

        return OrderType::tryFrom($settings->defaultOrderType) ?? OrderType::default();
    }
}
