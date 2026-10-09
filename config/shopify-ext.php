<?php

use Illuminate\Support\Str;

/**
 *  We are only "gifted" with the theme app ID once a call to `shopify app deploy`
 *  has been called, either manually or via CI
 *
 *  This information cannot be retrieved using the API, naturally
 *
 *  Therefore realistically we will only have this information once an app is just about
 *  to be published on whichever environment we're currently working on, so do not be
 *  surprised if any of these variables are missing
 */
$extensionName = env('SHOPIFY_APP_HANDLE', 'main');
$extensionEnv = Str::upper(Str::slug($extensionName, '_'));

return [
    /**
     *  Shopify extension name
     */
    'name' => $extensionName,

    /**
     *  Shopify extension ID
     */
    'id' => env('SHOPIFY_' . $extensionEnv . '_ID'),

    /**
     *  Default shopify template
     */
    'template' => env('SHOPIFY_' . $extensionEnv . '_TEMPLATE', 'app-template'),
];
