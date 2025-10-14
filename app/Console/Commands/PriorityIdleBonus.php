<?php

namespace App\Console\Commands;

use App\Models\Fixer;
use App\Models\Setting;
use App\Services\PriorityPointService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PriorityIdleBonus extends Command
{
    protected $signature = 'priority:idle-bonus';

    protected $description = 'Grant idle bonus to fixers without recent completions.';

    public function handle(PriorityPointService $priorityPoints): int
    {
        $idleDays = (int) Setting::get('priority.idle_days', 7);
        $chunk = (int) Setting::get('priority.command_chunk', 200);
        $since = Carbon::now()->subDays(max($idleDays, 1));

        Fixer::query()
            ->where('status', 'approved')
            ->where(function ($query) use ($since) {
                $query->whereNull('last_completed_at')
                    ->orWhere('last_completed_at', '<=', $since);
            })
            ->where(function ($query) use ($since) {
                $query->whereNull('last_idle_bonus_at')
                    ->orWhere('last_idle_bonus_at', '<=', $since);
            })
            ->chunkById($chunk, function ($fixers) use ($priorityPoints) {
                foreach ($fixers as $fixer) {
                    $priorityPoints->onIdleBonus($fixer, [
                        'command' => 'idle-bonus',
                    ]);
                    $fixer->forceFill(['last_idle_bonus_at' => now()])->save();
                }
            });

        $this->info('Idle bonus processed.');
        return self::SUCCESS;
    }
}
