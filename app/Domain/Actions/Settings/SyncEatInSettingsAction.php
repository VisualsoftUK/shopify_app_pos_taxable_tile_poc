<?php

namespace App\Domain\Actions\Settings;

use App\Domain\DataObjects\CustomSettingsData;
use App\Domain\Enums\OrderType;
use App\Domain\Services\EatInSetupService;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\Shopify\Enum\UserSettingsType;
use Visualsoft\Shopify\Models\UserSetting;

/**
 * After settings are saved to app-installation metafields, refresh the local
 * settings cache (UserSettingService does not invalidate it) and push the
 * default order type into the Cart Transform function configuration.
 */
class SyncEatInSettingsAction
{
    public function __construct(
        protected VsShopModel $shop
    ) {}

    public static function for(VsShopModel $shop): static
    {
        return new static(shop: $shop);
    }

    public function sync(CustomSettingsData $settings): void
    {
        UserSetting::saveForUser($this->shop->id, get_object_vars($settings), UserSettingsType::METAFIELDS);

        EatInSetupService::for($this->shop)->syncCartTransformConfig(
            OrderType::tryFrom($settings->defaultOrderType) ?? OrderType::default()
        );
    }
}
