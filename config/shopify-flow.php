<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flow Actions Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace where your Flow action classes are located.
    | The controller will look for action classes in this namespace.
    |
    */
    'actions_namespace' => env('SHOPIFY_FLOW_ACTIONS_NAMESPACE', 'App\\Domain\\Actions\\FlowActions'),

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Flow actions route.
    |
    */
    'route_prefix' => env('SHOPIFY_FLOW_ROUTE_PREFIX', 'actions'),
    'middleware' => ['auth.flow_action'],
];
