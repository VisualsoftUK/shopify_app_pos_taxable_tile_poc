<?php

namespace App\Framework\Support;

use Visualsoft\ShopifyAppPricing\Client\PartnerApiClient;

/**
 * Stand-in for the App Pricing Partner API client when the Partner API is
 * disabled (e.g. custom apps, where Shopify restricts the Active Subscription
 * API to public apps). Every call behaves as if the shop has no subscription
 * and no events, without contacting Shopify.
 */
class DisabledPartnerApiClient extends PartnerApiClient
{
    public function activeSubscription(string $appId, string $shopId): ?array
    {
        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function subscriptionEvents(string $appId, string $shopId, int $first = 25): array
    {
        return [];
    }

    public function latestRelationshipUninstalled(string $appId, string $shopId): ?array
    {
        return null;
    }

    /**
     * @return array{nodes: array<int, array<string, mixed>>, pageInfo: array{hasNextPage: bool, endCursor: ?string}}
     */
    public function appSubscriptionPlanEvents(string $appId, int $first = 50, ?string $after = null): array
    {
        return [
            'nodes' => [],
            'pageInfo' => ['hasNextPage' => false, 'endCursor' => null],
        ];
    }
}
