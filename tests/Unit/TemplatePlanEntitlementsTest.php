<?php

use App\Domain\Enums\TemplatePlan;
use Tests\TestCase;

uses(TestCase::class);

it('publishes only the free plan with an empty feature catalog', function () {
    expect(config('app-pricing-features.features'))->toBe([])
        ->and(config('app-pricing-features.private_plan_handles'))->toBe([])
        ->and(config('app-pricing-features.addon_packs'))->toBe([])
        ->and(config('app-pricing-features.collated_usage_events'))->toBe([])
        ->and(array_keys(config('app-pricing-features.plan_entitlements')))->toBe([TemplatePlan::Free->value])
        ->and(config('app-pricing-features.plan_entitlements.' . TemplatePlan::Free->value))->toBe([])
        ->and(config('app-pricing-features.plan_tier_levels.' . TemplatePlan::Free->value))->toBe(1);
});
