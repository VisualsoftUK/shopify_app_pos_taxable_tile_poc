<?php

use App\Domain\Actions\Orders\ProcessOrderVatAction;
use App\Domain\Enums\OrderType;
use App\Domain\Exceptions\ShopifyUserErrorsException;
use App\Domain\Models\User;
use Illuminate\Support\Facades\Queue;
use Tests\Fixtures\EatInFixtures;
use Visualsoft\Shopify\Enum\UserSettingsType;
use Visualsoft\Shopify\Jobs\AddTagsToResourceJob;
use Visualsoft\Shopify\Models\UserSetting;

beforeEach(function () {
    Queue::fake();

    $this->shop = User::factory()->withMantlePlan()->create();

    UserSetting::saveForUser($this->shop->id, ['defaultOrderType' => 'takeaway'], UserSettingsType::METAFIELDS);
});

test('it saves the breakdown metafields and tags the order', function () {
    $this->mockGraphQlOperation('OrderVat', ['order' => EatInFixtures::eatInOrder()])->once();
    $this->mockGraphQlOperation('SaveOrderVat', ['metafieldsSet' => ['metafields' => [], 'userErrors' => []]],
        fn (array $params): bool => collect($params['metafields'])->firstWhere('key', 'order_type')['value'] === 'eat_in'
    )->once();

    $breakdown = ProcessOrderVatAction::for($this->shop)->process(1001);

    expect($breakdown->orderType)->toBe(OrderType::EatIn);

    Queue::assertPushed(AddTagsToResourceJob::class, 1);
});

test('it does not tag the order when tagging is disabled', function () {
    $this->mockGraphQlOperation('OrderVat', ['order' => EatInFixtures::eatInOrder()]);
    $this->mockGraphQlOperation('SaveOrderVat', ['metafieldsSet' => ['metafields' => [], 'userErrors' => []]]);

    ProcessOrderVatAction::for($this->shop)->process(1001, shouldTag: false);

    Queue::assertNotPushed(AddTagsToResourceJob::class);
});

test('it returns null without saving when the order cannot be found', function () {
    $this->mockGraphQlOperation('OrderVat', ['order' => null]);
    $this->mockGraphQlOperation('SaveOrderVat', [])->never();

    expect(ProcessOrderVatAction::for($this->shop)->process(1001))->toBeNull();

    Queue::assertNotPushed(AddTagsToResourceJob::class);
});

test('metafield user errors are raised', function () {
    $this->mockGraphQlOperation('OrderVat', ['order' => EatInFixtures::eatInOrder()]);
    $this->mockGraphQlOperation('SaveOrderVat', ['metafieldsSet' => [
        'metafields' => [],
        'userErrors' => [['field' => ['metafields'], 'message' => 'Owner not found', 'code' => 'INVALID']],
    ]]);

    ProcessOrderVatAction::for($this->shop)->process(1001);
})->throws(ShopifyUserErrorsException::class, 'Owner not found');
