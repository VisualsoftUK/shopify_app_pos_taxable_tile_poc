<?php

use App\Domain\Enums\TemplatePlan;

return [
    'features' => [],

    'plan_entitlements' => [
        TemplatePlan::Free->value => [],
    ],

    /*
    | Integer ranking per shopify_plan_handle. The template only ships Free.
    */
    'plan_tier_levels' => [
        TemplatePlan::Free->value => 1,
    ],

    'private_plan_handles' => [],

    'addon_packs' => [],

    'collated_usage_events' => [],
];
