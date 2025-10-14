<?php

namespace App\Console\Commands;

use App\Models\Fixer;
use App\Models\Setting;
use App\Services\PriorityPointService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PriorityDormancyGuard extends Command
{
    protected $signature = 'priority:dormancy-guard';

    protected $description = 'Lift fixers stuck below the priority threshold.';

    public function handle(PriorityPointService $priorityPoints): int
    {
        $threshold = (int) Setting::get('priority.dormancy_threshold', 20);
        $days = (int) Setting::get('priority.dormancy_days', 14);
        $lift = (int) Setting::get('priority.dormancy_lift_amount', 15);
        $minPoints = (int) Setting::get('priority.dormancy_min_points', 35);
        $chunk = (int) Setting::get('priority.command_chunk', 200);

        $cutoff = Carbon::now()->subDays(max($days, 1));

        Fixer::query()
            ->where('status', 'approved')
            ->where('priority_points', '<', $threshold)
            ->whereNotNull('priority_low_since_at')
            ->where('priority_low_since_at', '<=', $cutoff)
            ->chunkById($chunk, function ($fixers) use ($priorityPoints, $lift, $minPoints) {
                foreach ($fixers as $fixer) {
                    $current = (int) ($fixer->priority_points ?? 0);
                    $target = min($minPoints, $current + max($lift, 0));
                    $delta = $target - $current;
                    if ($delta <= 0) {
                        continue;
                    }

                    $priorityPoints->manualAdjust(
                        $fixer,
                        $delta,
                        PriorityPointService::REASON_DORMANCY_GUARD,
                        ['command' => 'dormancy-guard']
                    );
                }
            });

        $this->info('Dormancy guard processed.');
        return self::SUCCESS;
    }
}
