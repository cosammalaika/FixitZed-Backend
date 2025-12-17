<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Prune old notifications daily
        $schedule->command('notifications:prune')
            ->dailyAt('02:00')
            ->withoutOverlapping();

        $schedule->command('priority:weekly-recovery')
            ->weeklyOn(1, '02:00');

        $schedule->command('priority:idle-bonus')
            ->weeklyOn(1, '02:30');

        $schedule->command('priority:dormancy-guard')
            ->dailyAt('03:00');

        $schedule->command('fixitzed:sync-provinces')
            ->dailyAt('04:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
