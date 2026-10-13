<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Eat-in charge product
    |--------------------------------------------------------------------------
    |
    | The untaxed product used as the surcharge component when a dynamic
    | (eat-in/takeaway) product is sold for eating in. The SKU is how the app
    | identifies surcharge lines on orders.
    |
    */

    'charge_product' => [
        'title' => env('EAT_IN_CHARGE_PRODUCT_TITLE', 'Eat-in charge'),
        'sku' => env('EAT_IN_CHARGE_PRODUCT_SKU', 'EAT-IN-CHARGE'),
        'product_type' => 'Eat-in charge',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart Transform function
    |--------------------------------------------------------------------------
    */

    'function_handle' => env('EAT_IN_FUNCTION_HANDLE', 'eat-in-cart-transform'),

    'function_config' => [
        'namespace' => '$app:eat-in',
        'key' => 'config',
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | The order type used when a cart/order has no `_order_type` attribute and
    | the shop has not saved a preference, and the uplift percentage used when
    | a dynamic product has no `vat.eat_in_uplift_percent` metafield.
    |
    */

    'default_order_type' => env('EAT_IN_DEFAULT_ORDER_TYPE', 'takeaway'),

    'default_uplift_percent' => (float) env('EAT_IN_DEFAULT_UPLIFT_PERCENT', 20),

    'order_type_attribute' => '_order_type',

    /*
    |--------------------------------------------------------------------------
    | Product metafields (merchant-editable)
    |--------------------------------------------------------------------------
    */

    'product_metafields' => [
        'namespace' => 'vat',
        'behaviour_key' => 'behaviour',
        'uplift_key' => 'eat_in_uplift_percent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Order metafields (app-owned)
    |--------------------------------------------------------------------------
    */

    'order_metafields' => [
        'namespace' => '$app',
        'order_type_key' => 'order_type',
        'eat_in_total_key' => 'eat_in_total',
        'zero_rated_total_key' => 'zero_rated_total',
        'breakdown_key' => 'vat_breakdown',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reporting
    |--------------------------------------------------------------------------
    |
    | Reports are built live from Shopify orders. `page_size` is kept low enough
    | to stay under the GraphQL single-query cost limit, `max_pages` caps a
    | single report run, and results are cached briefly per shop and filter.
    |
    */

    'reports' => [
        'page_size' => (int) env('EAT_IN_REPORT_PAGE_SIZE', 100),
        'max_pages' => (int) env('EAT_IN_REPORT_MAX_PAGES', 50),
        'cache_ttl_seconds' => (int) env('EAT_IN_REPORT_CACHE_TTL', 600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backfill
    |--------------------------------------------------------------------------
    |
    | How far back the nightly backfill looks for orders that were never
    | classified (for example, because a webhook was not delivered).
    |
    */

    'backfill_days' => (int) env('EAT_IN_BACKFILL_DAYS', 3),

];
