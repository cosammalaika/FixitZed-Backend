<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class NotificationPruner
{
    public function prune(): int
    {
        $days = (int) Setting::get('notifications.retention_days', 7);
        $days = max(1, min($days, 3650));
        $cutoff = now()->subDays($days);

        $count = Notification::where('created_at', '<', $cutoff)->delete();

        Log::info('Notifications pruned', [
            'retention_days' => $days,
            'deleted' => $count,
        ]);

        return $count;
    }
}
