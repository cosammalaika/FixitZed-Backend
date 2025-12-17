<?php

namespace App\Console\Commands;

use App\Services\NotificationPruner;
use Illuminate\Console\Command;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune';
    protected $description = 'Delete notifications older than the configured retention window';

    public function handle(NotificationPruner $pruner): int
    {
        $deleted = $pruner->prune();
        $this->info("Deleted {$deleted} notifications.");
        return self::SUCCESS;
    }
}
