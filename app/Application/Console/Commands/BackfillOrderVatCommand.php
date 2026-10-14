<?php

namespace App\Application\Console\Commands;

use App\Domain\Models\User;
use App\Domain\Services\OrderVatService;
use App\Jobs\ProcessOrderVatJob;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;
use Visualsoft\Shopify\Enum\InternalFakeStore;

/**
 * Classify recent orders that were never tagged as eat-in or takeaway (for
 * example when an orders/create webhook was not delivered).
 */
class BackfillOrderVatCommand extends Command
{
    protected $signature = 'eat-in:backfill-orders
        {--days= : How many days back to look (defaults to config eat-in.backfill_days)}
        {--shop= : Limit to one myshopify domain}';

    protected $description = 'Process orders that have no eat-in / takeaway classification';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('eat-in.backfill_days'));
        $since = CarbonImmutable::today()->subDays($days);

        $shops = User::query()
            ->where('active', true)
            ->where('name', '!=', InternalFakeStore::STORE_NAME->value)
            ->withoutKnownInvalidAccessToken()
            ->when($this->option('shop') !== null, fn ($query) => $query->where('name', $this->option('shop')))
            ->get();

        foreach ($shops as $shop) {
            try {
                $orderIds = OrderVatService::for($shop)->findUnclassifiedOrderIds($since);
            } catch (Throwable $exception) {
                Log::error('[BackfillOrderVat] Failed to list unclassified orders', [
                    'shop' => $shop->name,
                    'error' => $exception->getMessage(),
                ]);

                continue;
            }

            foreach ($orderIds as $orderId) {
                ProcessOrderVatJob::dispatch($shop->name, $orderId, true);
            }

            $this->info(sprintf('%s: dispatched %d order(s).', $shop->name, count($orderIds)));
        }

        return self::SUCCESS;
    }
}
