<?php

use App\Domain\Models\User;
use Visualsoft\Shopify\Enum\UserSettingsType;
use Visualsoft\Shopify\Models\UserSetting;

test('the POS tile receives the shop default order type', function (string $defaultOrderType) {
    $shop = User::factory()->withMantlePlan()->create();
    UserSetting::saveForUser($shop->id, ['defaultOrderType' => $defaultOrderType], UserSettingsType::METAFIELDS);

    $this->asPos($shop)
        ->getJson(route('pos.config'))
        ->assertOk()
        ->assertExactJson(['defaultOrderType' => $defaultOrderType]);
})->with(['eat_in', 'takeaway']);

test('an invalid stored default falls back to the configured default', function () {
    $shop = User::factory()->withMantlePlan()->create();
    UserSetting::saveForUser($shop->id, ['defaultOrderType' => 'drive_thru'], UserSettingsType::METAFIELDS);

    $this->asPos($shop)
        ->getJson(route('pos.config'))
        ->assertOk()
        ->assertExactJson(['defaultOrderType' => 'takeaway']);
});

test('requests without a POS session token are rejected', function () {
    $this->getJson(route('pos.config'))->assertUnauthorized();
});
