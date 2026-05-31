<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\FcmService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchNotificationPush implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $notificationId)
    {
        $this->onQueue('notifications');
    }

    public function handle(FcmService $fcm): void
    {
        $notification = Notification::with('user')->find($this->notificationId);
        if (! $notification) {
            return;
        }

        $fcm->sendNotificationRecord($notification);
    }
}
