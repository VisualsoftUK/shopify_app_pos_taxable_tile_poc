<?php

use Tests\Stubs\DataObjects\TestDataObject;
use Tests\Stubs\DataObjects\TestDataObjectWithBoolean;
use Tests\Stubs\DataObjects\TestDataObjectWithNamespace;
use Tests\Stubs\DataObjects\TestDataObjectWithSimpleType;
use Visualsoft\Shopify\DataObjects\Contracts\Metafieldable;

test('data object implements metafields interface', function () {
    $this->assertInstanceOf(Metafieldable::class, TestDataObject::from());
});

test('data object casts string to boolean', function (string $string, bool $bool) {
    $object = TestDataObjectWithBoolean::from(['foo' => $string]);

    $this->assertEquals($bool, $object->foo);
})->with([
    ['true', true],
    ['false', false],
]);

test('data object imports metafields with simple type', function () {
    $object = TestDataObjectWithSimpleType::withMetafields([
        'foo' => [
            'value' => '#123456',
        ],
    ]);

    $this->assertEquals('#123456', $object->foo);
});

test('data object exports metafields from default namespace', function () {
    $object = TestDataObjectWithBoolean::from(['foo' => true]);
    $objectMetafields = $object->toMetafieldsData()->toArray();

    $expected = [
        [
            'namespace' => '$app',
            'key' => 'foo',
            'type' => 'boolean',
            'value' => 'true',
        ],
    ];

    $this->assertEquals($expected, $objectMetafields);
});

test('data object exports metafields from named namespace', function () {
    $object = TestDataObjectWithNamespace::from(['foo' => 'bar']);
    $objectMetafields = $object->toMetafieldsData()->toArray();

    $expected = [
        [
            'namespace' => 'test',
            'key' => 'foo',
            'type' => 'single_line_text_field',
            'value' => 'bar',
        ],
    ];

    $this->assertEquals($expected, $objectMetafields);
});

test('data object exports metafields with simple type', function () {
    $object = TestDataObjectWithSimpleType::from(['foo' => '#123456']);
    $objectMetafields = $object->toMetafieldsData()->toArray();

    $expected = [
        [
            'namespace' => '$app',
            'key' => 'foo',
            'type' => 'color',
            'value' => '#123456',
        ],
    ];

    $this->assertEquals($expected, $objectMetafields);
});

test('data object exports metafields subquery', function () {
    $object = TestDataObjectWithNamespace::from(['foo' => 'bar']);
    $objectMetafieldSubquery = $object->toMetafieldsSubquery();

    $expected = <<<'EOL'
    foo: metafield(namespace: "test" key: "foo") { value }
    EOL;

    $this->assertEquals($expected, $objectMetafieldSubquery);
});
