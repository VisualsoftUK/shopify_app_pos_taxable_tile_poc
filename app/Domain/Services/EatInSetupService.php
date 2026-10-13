<?php

namespace App\Domain\Services;

use App\Domain\Concerns\ReadsGraphQlResponses;
use App\Domain\Enums\OrderType;
use App\Domain\Enums\VatBehaviour;
use App\Domain\Exceptions\ShopifyUserErrorsException;
use Illuminate\Support\Facades\Log;
use Visualsoft\Shopify\Contracts\VsShopModel;
use Visualsoft\Shopify\Services\ShopifyGraphQlService;

/**
 * Idempotently provisions everything the eat-in surcharge flow needs in a
 * shop: metafield definitions, the untaxed "Eat-in charge" product, and the
 * Cart Transform (with its configuration metafield).
 */
class EatInSetupService
{
    use ReadsGraphQlResponses;

    protected const string CREATE_DEFINITION_MUTATION = <<<'GRAPHQL'
        mutation CreateDefinition($definition: MetafieldDefinitionInput!) {
            metafieldDefinitionCreate(definition: $definition) {
                createdDefinition { id }
                userErrors { field message code }
            }
        }
        GRAPHQL;

    protected const string FIND_CHARGE_VARIANT_QUERY = <<<'GRAPHQL'
        query FindChargeVariant($query: String!) {
            productVariants(first: 1, query: $query) {
                nodes { id product { id } }
            }
        }
        GRAPHQL;

    protected const string CREATE_CHARGE_PRODUCT_MUTATION = <<<'GRAPHQL'
        mutation CreateChargeProduct($product: ProductCreateInput!) {
            productCreate(product: $product) {
                product {
                    id
                    variants(first: 1) { nodes { id } }
                }
                userErrors { field message }
            }
        }
        GRAPHQL;

    protected const string UPDATE_CHARGE_VARIANT_MUTATION = <<<'GRAPHQL'
        mutation UpdateChargeVariant($productId: ID!, $variants: [ProductVariantsBulkInput!]!) {
            productVariantsBulkUpdate(productId: $productId, variants: $variants) {
                productVariants { id }
                userErrors { field message code }
            }
        }
        GRAPHQL;

    protected const string PUBLICATIONS_QUERY = <<<'GRAPHQL'
        query Publications {
            publications(first: 25) { nodes { id } }
        }
        GRAPHQL;

    protected const string PUBLISH_MUTATION = <<<'GRAPHQL'
        mutation PublishChargeProduct($id: ID!, $input: [PublicationInput!]!) {
            publishablePublish(id: $id, input: $input) {
                userErrors { field message }
            }
        }
        GRAPHQL;

    protected const string CART_TRANSFORMS_QUERY = <<<'GRAPHQL'
        query CartTransforms {
            cartTransforms(first: 10) { nodes { id } }
        }
        GRAPHQL;

    protected const string CREATE_CART_TRANSFORM_MUTATION = <<<'GRAPHQL'
        mutation CreateCartTransform($functionHandle: String!, $metafields: [MetafieldInput!]) {
            cartTransformCreate(functionHandle: $functionHandle, blockOnFailure: false, metafields: $metafields) {
                cartTransform { id }
                userErrors { field message code }
            }
        }
        GRAPHQL;

    protected const string SET_METAFIELDS_MUTATION = <<<'GRAPHQL'
        mutation SetCartTransformConfig($metafields: [MetafieldsSetInput!]!) {
            metafieldsSet(metafields: $metafields) {
                metafields { id }
                userErrors { field message code }
            }
        }
        GRAPHQL;

    public function __construct(
        protected VsShopModel $shop
    ) {}

    public static function for(VsShopModel $shop): static
    {
        return new static(shop: $shop);
    }

    /**
     * Run the full, idempotent setup.
     *
     * @throws ShopifyUserErrorsException
     */
    public function setUp(OrderType $defaultOrderType): void
    {
        $this->ensureMetafieldDefinitions();

        $chargeVariantId = $this->ensureChargeProduct();

        $this->ensureCartTransform($chargeVariantId, $defaultOrderType);

        Log::info('[EatInSetup] Setup complete', ['shop' => $this->shop->name]);
    }

    /**
     * Push the latest configuration into the Cart Transform metafield,
     * creating anything that is missing.
     *
     * @throws ShopifyUserErrorsException
     */
    public function syncCartTransformConfig(OrderType $defaultOrderType): void
    {
        $this->ensureCartTransform($this->ensureChargeProduct(), $defaultOrderType);
    }

    /**
     * Create product and order metafield definitions; existing ones are left untouched.
     *
     * @throws ShopifyUserErrorsException
     */
    public function ensureMetafieldDefinitions(): void
    {
        foreach ($this->metafieldDefinitions() as $definition) {
            $response = ShopifyGraphQlService::for($this->shop)
                ->withQuery(self::CREATE_DEFINITION_MUTATION)
                ->withParams(['definition' => $definition])
                ->get();

            $this->assertNoUserErrors($response, 'metafieldDefinitionCreate', ['TAKEN']);
        }
    }

    /**
     * Find or create the untaxed eat-in charge product, returning its variant GID.
     *
     * @throws ShopifyUserErrorsException
     */
    public function ensureChargeProduct(): string
    {
        $existingVariantId = $this->findChargeVariantId();

        if ($existingVariantId !== null) {
            return $existingVariantId;
        }

        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::CREATE_CHARGE_PRODUCT_MUTATION)
            ->withParams(['product' => [
                'title' => config('eat-in.charge_product.title'),
                'productType' => config('eat-in.charge_product.product_type'),
                'status' => 'ACTIVE',
                'tags' => ['eat-in-charge'],
                'metafields' => [
                    ['namespace' => 'seo', 'key' => 'hidden', 'type' => 'number_integer', 'value' => '1'],
                ],
            ]])
            ->get();

        $this->assertNoUserErrors($response, 'productCreate');

        $productId = $this->responseValue($response, 'body.data.productCreate.product.id');
        $variantId = $this->responseValue($response, 'body.data.productCreate.product.variants.nodes.0.id');

        $this->configureChargeVariant($productId, $variantId);
        $this->publishToAllChannels($productId);

        return $variantId;
    }

    /**
     * Create the Cart Transform if needed and write its configuration metafield.
     *
     * @throws ShopifyUserErrorsException
     */
    public function ensureCartTransform(string $chargeVariantId, OrderType $defaultOrderType): void
    {
        $configMetafield = [
            'namespace' => config('eat-in.function_config.namespace'),
            'key' => config('eat-in.function_config.key'),
            'type' => 'json',
            'value' => json_encode([
                'eatInChargeVariantId' => $chargeVariantId,
                'defaultUpliftPercent' => (float) config('eat-in.default_uplift_percent'),
                'defaultOrderType' => $defaultOrderType->value,
            ], JSON_THROW_ON_ERROR),
        ];

        $existingResponse = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::CART_TRANSFORMS_QUERY)
            ->get();

        $cartTransformId = $this->responseValue($existingResponse, 'body.data.cartTransforms.nodes.0.id');

        if ($cartTransformId === null) {
            $response = ShopifyGraphQlService::for($this->shop)
                ->withQuery(self::CREATE_CART_TRANSFORM_MUTATION)
                ->withParams([
                    'functionHandle' => config('eat-in.function_handle'),
                    'metafields' => [$configMetafield],
                ])
                ->get();

            $this->assertNoUserErrors($response, 'cartTransformCreate');

            return;
        }

        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::SET_METAFIELDS_MUTATION)
            ->withParams(['metafields' => [['ownerId' => $cartTransformId, ...$configMetafield]]])
            ->get();

        $this->assertNoUserErrors($response, 'metafieldsSet');
    }

    protected function findChargeVariantId(): ?string
    {
        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::FIND_CHARGE_VARIANT_QUERY)
            ->withParams(['query' => sprintf("sku:'%s'", config('eat-in.charge_product.sku'))])
            ->get();

        return $this->responseValue($response, 'body.data.productVariants.nodes.0.id');
    }

    /**
     * @throws ShopifyUserErrorsException
     */
    protected function configureChargeVariant(string $productId, string $variantId): void
    {
        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::UPDATE_CHARGE_VARIANT_MUTATION)
            ->withParams([
                'productId' => $productId,
                'variants' => [[
                    'id' => $variantId,
                    'price' => '0.00',
                    'taxable' => false,
                    'inventoryPolicy' => 'CONTINUE',
                    'inventoryItem' => [
                        'sku' => config('eat-in.charge_product.sku'),
                        'tracked' => false,
                        'requiresShipping' => false,
                    ],
                ]],
            ])
            ->get();

        $this->assertNoUserErrors($response, 'productVariantsBulkUpdate');
    }

    /**
     * Make the charge product available on every sales channel (POS and Online
     * Store included) so it can be used as a bundle component.
     *
     * @throws ShopifyUserErrorsException
     */
    protected function publishToAllChannels(string $productId): void
    {
        $publicationsResponse = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::PUBLICATIONS_QUERY)
            ->get();

        $publicationInputs = collect($this->responseValue($publicationsResponse, 'body.data.publications.nodes', []))
            ->map(fn (array $publication): array => ['publicationId' => $publication['id']])
            ->values()
            ->all();

        if (empty($publicationInputs) === true) {
            return;
        }

        $response = ShopifyGraphQlService::for($this->shop)
            ->withQuery(self::PUBLISH_MUTATION)
            ->withParams(['id' => $productId, 'input' => $publicationInputs])
            ->get();

        $this->assertNoUserErrors($response, 'publishablePublish');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function metafieldDefinitions(): array
    {
        $productNamespace = config('eat-in.product_metafields.namespace');
        $orderNamespace = config('eat-in.order_metafields.namespace');
        $orderAccess = ['admin' => 'MERCHANT_READ'];

        return [
            [
                'name' => 'VAT behaviour',
                'namespace' => $productNamespace,
                'key' => config('eat-in.product_metafields.behaviour_key'),
                'type' => 'single_line_text_field',
                'ownerType' => 'PRODUCT',
                'description' => 'standard = always 20%, zero = always 0%, dynamic = 20% eat-in / 0% takeaway (set Charge tax off).',
                'pin' => true,
                'validations' => [[
                    'name' => 'choices',
                    'value' => json_encode(array_column(VatBehaviour::cases(), 'value'), JSON_THROW_ON_ERROR),
                ]],
            ],
            [
                'name' => 'Eat-in uplift %',
                'namespace' => $productNamespace,
                'key' => config('eat-in.product_metafields.uplift_key'),
                'type' => 'number_decimal',
                'ownerType' => 'PRODUCT',
                'description' => 'Optional. Eat-in surcharge as a percentage of the price; defaults to 20.',
                'pin' => true,
            ],
            [
                'name' => 'Order type',
                'namespace' => $orderNamespace,
                'key' => config('eat-in.order_metafields.order_type_key'),
                'type' => 'single_line_text_field',
                'ownerType' => 'ORDER',
                'access' => $orderAccess,
                'pin' => true,
            ],
            [
                'name' => 'Eat-in charge total',
                'namespace' => $orderNamespace,
                'key' => config('eat-in.order_metafields.eat_in_total_key'),
                'type' => 'money',
                'ownerType' => 'ORDER',
                'access' => $orderAccess,
                'pin' => true,
            ],
            [
                'name' => 'Zero-rated total',
                'namespace' => $orderNamespace,
                'key' => config('eat-in.order_metafields.zero_rated_total_key'),
                'type' => 'money',
                'ownerType' => 'ORDER',
                'access' => $orderAccess,
                'pin' => true,
            ],
            [
                'name' => 'VAT breakdown',
                'namespace' => $orderNamespace,
                'key' => config('eat-in.order_metafields.breakdown_key'),
                'type' => 'json',
                'ownerType' => 'ORDER',
                'access' => $orderAccess,
            ],
        ];
    }
}
