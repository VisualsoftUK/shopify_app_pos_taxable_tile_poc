<?php

namespace Tests\Stubs\DataObjects;

use Visualsoft\Shopify\DataObjects\Data;

class TestDataObjectWithNamespace extends Data
{
    protected static string $metafieldsNamespace = 'test';

    public function __construct(public string $foo = 'bar')
    {
        //
    }
}
