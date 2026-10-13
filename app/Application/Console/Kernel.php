<?php

namespace App\Application\Console;

use App\Application\Console\Commands\BackfillOrderVatCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Osiset\ShopifyApp\Console\RefreshExpiringOfflineTokensCommand;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTaskLogItem;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('queue:prune-failed --hours=0')->everyFiveMinutes()->doNotMonitor();
        $schedule->command('model:prune', ['--model' => MonitoredScheduledTaskLogItem::class])->daily()->doNotMonitor();
        $schedule->command(RefreshExpiringOfflineTokensCommand::class)
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();
        $schedule->command('telescope:prune --hours=168')->daily();
        $schedule->command(BackfillOrderVatCommand::class)
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
