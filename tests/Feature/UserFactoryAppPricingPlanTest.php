<?php

use App\Domain\Enums\TemplatePlan;
use App\Domain\Models\User;

it('seeds a functional free app pricing plan', function () {
    $shop = User::factory()
        ->withAppPricingPlan()
        ->create();

    $shop->unsetRelation('appPricingState');

    expect($shop->hasActiveAppPricingPlan())->toBeTrue()
        ->and($shop->appPricingState->plan->shopify_plan_handle)->toBe(TemplatePlan::Free->value);
});
