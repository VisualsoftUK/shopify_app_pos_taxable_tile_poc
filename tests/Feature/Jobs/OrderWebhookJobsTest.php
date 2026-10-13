<?php

use App\Domain\Models\User;
use App\Jobs\OrdersCreateJob;
use App\Jobs\ProcessOrderVatJob;
use App\Jobs\RefundsCreateJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();

    $this->shop = User::factory()->withMantlePlan()->create();
});

test('orders/create webhooks are routed to the OrdersCreateJob', function () {
    $this->actingAs($this->shop)
        ->postWebhookJson('orders/create', ['id' => 1001])
        ->assertSuccessful();

    Queue::assertPushed(OrdersCreateJob::class);
});

test('refunds/create webhooks are routed to the RefundsCreateJob', function () {
    $this->actingAs($this->shop)
        ->postWebhookJson('refunds/create', ['id' => 55, 'order_id' => 1001])
        ->assertSuccessful();

    Queue::assertPushed(RefundsCreateJob::class);
});

test('a new order is processed and tagged', function () {
    (new OrdersCreateJob($this->shop->name, (object) ['id' => 1001]))->handle();

    Queue::assertPushed(ProcessOrderVatJob::class, fn (ProcessOrderVatJob $job): bool => $job->orderId === 1001 && $job->shouldTag === true);
});

test('a refund recalculates the order without re-tagging it', function () {
    (new RefundsCreateJob($this->shop->name, (object) ['id' => 55, 'order_id' => 1001]))->handle();

    Queue::assertPushed(ProcessOrderVatJob::class, fn (ProcessOrderVatJob $job): bool => $job->orderId === 1001 && $job->shouldTag === false);
});
