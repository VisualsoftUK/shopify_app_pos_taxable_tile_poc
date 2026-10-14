<?php

use App\Application\Listeners\SetUpEatInOnAppInstalledListener;
use App\Domain\Models\User;
use App\Jobs\SetUpEatInJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Osiset\ShopifyApp\Messaging\Events\AppInstalledEvent;
use Osiset\ShopifyApp\Objects\Values\ShopId;

test('the listener is registered for the app installed event', function () {
    Event::fake();

    Event::assertListening(AppInstalledEvent::class, SetUpEatInOnAppInstalledListener::class);
});

test('installing the app dispatches the eat-in set-up for that shop', function () {
    Queue::fake();
    $shop = User::factory()->withMantlePlan()->create();

    (new SetUpEatInOnAppInstalledListener)->handle(new AppInstalledEvent(ShopId::fromNative($shop->id)));

    Queue::assertPushed(SetUpEatInJob::class, fn (SetUpEatInJob $job): bool => $job->shop->is($shop));
});

test('nothing is dispatched when the installed shop cannot be found', function () {
    Queue::fake();

    (new SetUpEatInOnAppInstalledListener)->handle(new AppInstalledEvent(ShopId::fromNative(999999)));

    Queue::assertNotPushed(SetUpEatInJob::class);
});
