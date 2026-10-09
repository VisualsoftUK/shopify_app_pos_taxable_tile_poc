<?php

namespace Tests\Stubs\DataObjects;

use Visualsoft\Shopify\DataObjects\Data;

class TestDataObjectWithBoolean extends Data
{
    public function __construct(public bool $foo = true)
    {
        //
    }
}
