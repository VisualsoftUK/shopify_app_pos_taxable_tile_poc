<?php

namespace Tests\Concerns;

use Visualsoft\ShopifyBrowserTesting\Concerns\InteractsWithShopifyGraphQl;

trait InteractsWithGraphQlBrowser
{
    use InteractsWithShopifyGraphQl;

    /**
     * @return array<string, callable>
     */
    protected function defaultShopifyGraphQlHandlers(): array
    {
        return [];
    }
}
