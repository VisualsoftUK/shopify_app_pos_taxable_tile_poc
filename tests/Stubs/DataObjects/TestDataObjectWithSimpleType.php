<?php

namespace Tests\Stubs\DataObjects;

use Visualsoft\Shopify\DataObjects\Data;

class TestDataObjectWithSimpleType extends Data
{
    protected static array $metafieldsAttributeTypes = [
        'foo' => 'color',
    ];

    public function __construct(public string $foo = 'bar')
    {
        //
    }
}
