<?php

use App\Domain\Actions\Settings\SyncEatInSettingsAction;
use App\Domain\DataObjects\CustomSettingsData;
use App\Domain\Enums\OrderType;
use App\Domain\Exceptions\ShopifyUserErrorsException;
use App\Domain\Models\User;
use App\Domain\Services\EatInSetupService;
use Visualsoft\Shopify\Enum\UserSettingsType;
use Visualsoft\Shopify\Models\UserSetting;

beforeEach(function () {
    $this->shop = User::factory()->withMantlePlan()->create();
    $this->chargeVariantId = 'gid://shopify/ProductVariant/999';
});

/**
 * Decode the Cart Transform config metafield from a mutation's params.
 *
 * @param  array<int, array<string, mixed>>  $metafields
 * @return array<string, mixed>
 */
function cartTransformConfig(array $metafields): array
{
    return json_decode(collect($metafields)->firstWhere('key', 'config')['value'], true);
}

test('it provisions definitions, the charge product and the cart transform for a new shop', function () {
    $this->mockGraphQlOperation('CreateDefinition', ['metafieldDefinitionCreate' => ['createdDefinition' => ['id' => 'gid://shopify/MetafieldDefinition/1'], 'userErrors' => []]])->times(6);
    $this->mockGraphQlOperation('FindChargeVariant', ['productVariants' => ['nodes' => []]])->once();
    $this->mockGraphQlOperation('CreateChargeProduct', ['productCreate' => [
        'product' => ['id' => 'gid://shopify/Product/9', 'variants' => ['nodes' => [['id' => $this->chargeVariantId]]]],
        'userErrors' => [],
    ]], fn (array $params): bool => $params['product']['title'] === 'Eat-in charge')->once();
    $this->mockGraphQlOperation('UpdateChargeVariant', ['productVariantsBulkUpdate' => ['productVariants' => [], 'userErrors' => []]],
        fn (array $params): bool => $params['variants'][0]['taxable'] === false && $params['variants'][0]['inventoryItem']['sku'] === 'EAT-IN-CHARGE'
    )->once();
    $this->mockGraphQlOperation('Publications', ['publications' => ['nodes' => [['id' => 'gid://shopify/Publication/1'], ['id' => 'gid://shopify/Publication/2']]]])->once();
    $this->mockGraphQlOperation('PublishChargeProduct', ['publishablePublish' => ['userErrors' => []]],
        fn (array $params): bool => count($params['input']) === 2
    )->once();
    $this->mockGraphQlOperation('CartTransforms', ['cartTransforms' => ['nodes' => []]])->once();
    $this->mockGraphQlOperation('CreateCartTransform', ['cartTransformCreate' => ['cartTransform' => ['id' => 'gid://shopify/CartTransform/1'], 'userErrors' => []]],
        fn (array $params): bool => $params['functionHandle'] === 'eat-in-cart-transform'
            && cartTransformConfig($params['metafields']) === [
                'eatInChargeVariantId' => $this->chargeVariantId,
                'defaultUpliftPercent' => 20,
                'defaultOrderType' => 'eat_in',
            ]
    )->once();

    EatInSetupService::for($this->shop)->setUp(OrderType::EatIn);
});

test('re-running set-up reuses existing resources and only refreshes the configuration', function () {
    $this->mockGraphQlOperation('CreateDefinition', ['metafieldDefinitionCreate' => [
        'createdDefinition' => null,
        'userErrors' => [['field' => ['definition', 'key'], 'message' => 'Key is in use', 'code' => 'TAKEN']],
    ]])->times(6);
    $this->mockGraphQlOperation('FindChargeVariant', ['productVariants' => ['nodes' => [['id' => $this->chargeVariantId, 'product' => ['id' => 'gid://shopify/Product/9']]]]]);
    $this->mockGraphQlOperation('CreateChargeProduct', [])->never();
    $this->mockGraphQlOperation('CartTransforms', ['cartTransforms' => ['nodes' => [['id' => 'gid://shopify/CartTransform/1']]]]);
    $this->mockGraphQlOperation('CreateCartTransform', [])->never();
    $this->mockGraphQlOperation('SetCartTransformConfig', ['metafieldsSet' => ['metafields' => [], 'userErrors' => []]],
        fn (array $params): bool => $params['metafields'][0]['ownerId'] === 'gid://shopify/CartTransform/1'
            && cartTransformConfig($params['metafields'])['defaultOrderType'] === 'takeaway'
    )->once();

    EatInSetupService::for($this->shop)->setUp(OrderType::Takeaway);
});

test('unexpected definition errors are raised', function () {
    $this->mockGraphQlOperation('CreateDefinition', ['metafieldDefinitionCreate' => [
        'createdDefinition' => null,
        'userErrors' => [['field' => ['definition'], 'message' => 'Access denied', 'code' => 'INVALID']],
    ]]);

    EatInSetupService::for($this->shop)->ensureMetafieldDefinitions();
})->throws(ShopifyUserErrorsException::class, 'Access denied');

test('saving settings refreshes the settings cache and the cart transform configuration', function () {
    UserSetting::saveForUser($this->shop->id, ['defaultOrderType' => 'takeaway'], UserSettingsType::METAFIELDS);

    $this->mockGraphQlOperation('FindChargeVariant', ['productVariants' => ['nodes' => [['id' => $this->chargeVariantId, 'product' => ['id' => 'gid://shopify/Product/9']]]]]);
    $this->mockGraphQlOperation('CartTransforms', ['cartTransforms' => ['nodes' => [['id' => 'gid://shopify/CartTransform/1']]]]);
    $this->mockGraphQlOperation('SetCartTransformConfig', ['metafieldsSet' => ['metafields' => [], 'userErrors' => []]],
        fn (array $params): bool => cartTransformConfig($params['metafields'])['defaultOrderType'] === 'eat_in'
    )->once();

    SyncEatInSettingsAction::for($this->shop)->sync(new CustomSettingsData(defaultOrderType: 'eat_in'));

    expect(UserSetting::getForUser($this->shop->id, UserSettingsType::METAFIELDS)['defaultOrderType'])->toBe('eat_in');
});
