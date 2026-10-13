<?php

use App\Domain\Actions\Orders\CalculateOrderVatBreakdownAction;
use App\Domain\Enums\OrderType;
use Tests\Fixtures\EatInFixtures;

test('eat-in orders total the surcharge and pair it with its dynamic product line', function () {
    $breakdown = CalculateOrderVatBreakdownAction::for(EatInFixtures::eatInOrder(), OrderType::Takeaway)->calculate();

    expect($breakdown->orderType)->toBe(OrderType::EatIn)
        ->and($breakdown->eatInTotalMinor)->toBe(79)
        ->and($breakdown->zeroRatedTotalMinor)->toBe(0)
        ->and($breakdown->lines)->toHaveCount(1)
        ->and($breakdown->lines[0]->productTitle)->toBe('Cheese Sandwich')
        ->and($breakdown->lines[0]->lineTotalMinor)->toBe(395)
        ->and($breakdown->lines[0]->eatInChargeMinor)->toBe(79)
        ->and($breakdown->lines[0]->upliftPercent())->toBe('20.00');
});

test('takeaway orders record dynamic lines as zero-rated and ignore standard and zero products', function () {
    $order = EatInFixtures::order([
        EatInFixtures::lineItem('SAND-1', '3.95', quantity: 2),
        EatInFixtures::lineItem('LATTE', '2.95', behaviour: 'standard', title: 'Latte'),
        EatInFixtures::lineItem('BAG-1KG', '18.00', behaviour: 'zero', title: 'Coffee Beans 1kg'),
    ], 'takeaway');

    $breakdown = CalculateOrderVatBreakdownAction::for($order, OrderType::EatIn)->calculate();

    expect($breakdown->orderType)->toBe(OrderType::Takeaway)
        ->and($breakdown->eatInTotalMinor)->toBe(0)
        ->and($breakdown->zeroRatedTotalMinor)->toBe(790)
        ->and($breakdown->lines)->toHaveCount(1)
        ->and($breakdown->lines[0]->quantity)->toBe(2);
});

test('the order type falls back to the shop default when the attribute is missing', function (OrderType $default) {
    $order = EatInFixtures::order([EatInFixtures::lineItem('SAND-1', '3.95')]);

    expect(CalculateOrderVatBreakdownAction::for($order, $default)->calculate()->orderType)->toBe($default);
})->with([OrderType::EatIn, OrderType::Takeaway]);

test('an order with eat-in charges but no attribute is treated as eat-in', function () {
    $order = EatInFixtures::eatInOrder();
    $order['customAttributes'] = [];

    expect(CalculateOrderVatBreakdownAction::for($order, OrderType::Takeaway)->calculate()->orderType)->toBe(OrderType::EatIn);
});

test('charges are paired by the _eat_in_for attribute when there is no bundle group', function () {
    $order = EatInFixtures::order([
        EatInFixtures::lineItem('SAND-1', '3.95', quantity: 2, variantId: 'gid://shopify/ProductVariant/11'),
        EatInFixtures::lineItem('EAT-IN-CHARGE', '0.79', quantity: 2, behaviour: null, title: 'Eat-in charge',
            customAttributes: [['key' => '_eat_in_for', 'value' => 'gid://shopify/ProductVariant/11']]),
    ], 'eat_in');

    $breakdown = CalculateOrderVatBreakdownAction::for($order, OrderType::Takeaway)->calculate();

    expect($breakdown->eatInTotalMinor)->toBe(158)
        ->and($breakdown->lines[0]->eatInChargeMinor)->toBe(158);
});

test('fully refunded lines are excluded using the current quantity', function () {
    $order = EatInFixtures::order([
        EatInFixtures::lineItem('SAND-1', '3.95', quantity: 0, groupId: 'group-1'),
        EatInFixtures::lineItem('EAT-IN-CHARGE', '0.79', quantity: 0, behaviour: null, groupId: 'group-1', title: 'Eat-in charge'),
    ], 'eat_in');

    $breakdown = CalculateOrderVatBreakdownAction::for($order, OrderType::Takeaway)->calculate();

    expect($breakdown->eatInTotalMinor)->toBe(0)
        ->and($breakdown->lines)->toBeEmpty();
});

test('the breakdown is converted to order metafield inputs', function () {
    $inputs = collect(CalculateOrderVatBreakdownAction::for(EatInFixtures::eatInOrder(), OrderType::Takeaway)
        ->calculate()
        ->toMetafieldInputs())
        ->keyBy('key');

    expect($inputs['order_type']['value'])->toBe('eat_in')
        ->and(json_decode($inputs['eat_in_total']['value'], true))->toBe(['amount' => '0.79', 'currency_code' => 'GBP'])
        ->and(json_decode($inputs['zero_rated_total']['value'], true))->toBe(['amount' => '0.00', 'currency_code' => 'GBP'])
        ->and(json_decode($inputs['vat_breakdown']['value'], true)[0])->toMatchArray([
            'productTitle' => 'Cheese Sandwich',
            'lineTotal' => '3.95',
            'eatInCharge' => '0.79',
            'upliftPercent' => '20.00',
        ])
        ->and($inputs->pluck('ownerId')->unique()->all())->toBe(['gid://shopify/Order/1001'])
        ->and($inputs->pluck('namespace')->unique()->all())->toBe(['$app']);
});
