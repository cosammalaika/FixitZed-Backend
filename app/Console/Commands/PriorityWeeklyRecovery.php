<?php

namespace App\Console\Commands;

use App\Models\Fixer;
use App\Models\Setting;
use App\Services\PriorityPointService;
use Illuminate\Console\Command;

class PriorityWeeklyRecovery extends Command
{
    protected $signature = 'priority:weekly-recovery';

    protected $description = 'Award weekly priority point recovery to eligible fixers.';

    public function handle(PriorityPointService $priorityPoints): int
    {
        $cap = (int) Setting::get('priority.cap', PriorityPointService::DEFAULT_CAP);
        $chunk = (int) Setting::get('priority.command_chunk', 200);
        $award = (int) Setting::get('priority.weekly_recovery', 5);

        if ($award === 0) {
            $this->info('Weekly recovery disabled (value = 0).');
            return self::SUCCESS;
        }

        Fixer::query()
            ->where('status', 'approved')
            ->where('priority_points', '<', $cap)
            ->chunkById($chunk, function ($fixers) use ($priorityPoints) {
                foreach ($fixers as $fixer) {
                    $priorityPoints->onWeeklyRecovery($fixer, [
                        'command' => 'weekly-recovery',
                    ]);
                }
            });

        $this->info('Weekly recovery processed.');
        return self::SUCCESS;
    }
}
