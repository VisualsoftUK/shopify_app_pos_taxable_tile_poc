<?php

use App\Framework\Providers\AppServiceProvider;
use App\Framework\Support\DisabledPartnerApiClient;
use Illuminate\Support\Facades\Http;
use Visualsoft\ShopifyAppPricing\Client\PartnerApiClient;

test('the partner api client is disabled by default for this custom app', function () {
    $client = app(PartnerApiClient::class);

    expect($client)->toBeInstanceOf(DisabledPartnerApiClient::class)
        ->and($client->activeSubscription('gid://partners/App/1', 'gid://partners/Shop/1'))->toBeNull()
        ->and($client->subscriptionEvents('gid://partners/App/1', 'gid://partners/Shop/1'))->toBe([])
        ->and($client->appSubscriptionPlanEvents('gid://partners/App/1')['nodes'])->toBe([]);

    Http::assertNothingSent();
});

test('the package partner api client is used when enabled', function () {
    config(['services.shopify_partner_api.enabled' => true]);
    app()->forgetInstance(PartnerApiClient::class);
    app()->singleton(PartnerApiClient::class);

    (new AppServiceProvider(app()))->register();

    expect(app(PartnerApiClient::class))->not->toBeInstanceOf(DisabledPartnerApiClient::class);
});
