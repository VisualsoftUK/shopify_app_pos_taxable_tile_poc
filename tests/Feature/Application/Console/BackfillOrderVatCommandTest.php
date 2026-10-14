<?php

use App\Domain\Models\User;
use App\Jobs\ProcessOrderVatJob;
use Illuminate\Support\Facades\Queue;

test('unclassified orders are dispatched for processing', function () {
    Queue::fake();
    $this->travelTo('2026-10-09 03:00:00');

    $shop = User::factory()->withMantlePlan()->create();

    $this->mockGraphQlOperation('UnclassifiedOrders', [
        'orders' => [
            'pageInfo' => ['hasNextPage' => false, 'endCursor' => null],
            'nodes' => [['legacyResourceId' => '1001'], ['legacyResourceId' => '1002']],
        ],
    ], fn (array $params): bool => $params['query'] === "created_at:>=2026-10-06 tag_not:'Eat In' tag_not:'Takeaway'")->once();

    $this->artisan('eat-in:backfill-orders', ['--shop' => $shop->name])
        ->expectsOutput("{$shop->name}: dispatched 2 order(s).")
        ->assertSuccessful();

    Queue::assertPushed(ProcessOrderVatJob::class, 2);
    Queue::assertPushed(ProcessOrderVatJob::class, fn (ProcessOrderVatJob $job): bool => $job->orderId === 1002 && $job->shouldTag === true);
});

test('inactive shops are skipped', function () {
    Queue::fake();

    User::factory()->withMantlePlan()->create(['active' => false]);

    $this->mockGraphQlOperation('UnclassifiedOrders', [])->never();

    $this->artisan('eat-in:backfill-orders')->assertSuccessful();

    Queue::assertNothingPushed();
});
