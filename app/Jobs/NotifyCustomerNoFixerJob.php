<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Notifications\NoFixerFoundNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class NotifyCustomerNoFixerJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $serviceRequestId)
    {
    }

    public static function dispatchIfNeeded(ServiceRequest $serviceRequest, int $delayMinutes = 5): void
    {
        if (! $serviceRequest->customer_id || $serviceRequest->no_fixer_notified_at !== null) {
            return;
        }

        $job = static::dispatch($serviceRequest->id);

        if ($delayMinutes > 0) {
            $job->delay(now()->addMinutes($delayMinutes));
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::transaction(function (): void {
                $serviceRequest = ServiceRequest::query()
                    ->with(['service', 'fixer.user'])
                    ->lockForUpdate()
                    ->find($this->serviceRequestId);

                if (! $serviceRequest || ! $this->shouldNotify($serviceRequest)) {
                    return;
                }

                $notification = new NoFixerFoundNotification($serviceRequest);
                $payload = $notification->toLegacyPayload();

                Notification::create([
                    'user_id' => $serviceRequest->customer_id,
                    'recipient_type' => 'Individual',
                    'title' => $payload['title'],
                    'message' => $payload['message'],
                    'read' => false,
                ]);

                $serviceRequest->forceFill([
                    'no_fixer_notified_at' => now(),
                ])->save();
            });
        } catch (\Throwable) {
            // Silently ignore notification failures so the job does not retry endlessly.
        }
    }

    protected function shouldNotify(ServiceRequest $serviceRequest): bool
    {
        if (! $serviceRequest->customer_id || $serviceRequest->no_fixer_notified_at !== null) {
            return false;
        }

        if ($serviceRequest->status !== 'pending') {
            return false;
        }

        return ! $serviceRequest->fixer || ! $serviceRequest->fixer->user;
    }
}
