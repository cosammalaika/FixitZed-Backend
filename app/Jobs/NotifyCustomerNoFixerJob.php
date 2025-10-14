<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $serviceRequest = ServiceRequest::with('service')->find($this->serviceRequestId);
        if (! $serviceRequest) {
            return;
        }

        if ($serviceRequest->status !== 'pending') {
            return;
        }

        if ($serviceRequest->fixer_id) {
            return;
        }

        try {
            Notification::create([
                'user_id' => $serviceRequest->customer_id,
                'recipient_type' => 'customer',
                'title' => 'Still finding a fixer',
                'message' => sprintf(
                    'No fixer is available yet for your %s booking (#%d). You can cancel the request or wait a little longer.',
                    optional($serviceRequest->service)->name ?? 'service',
                    $serviceRequest->id
                ),
                'read' => false,
            ]);
        } catch (\Throwable) {
            // Silently ignore notification failures so the job does not retry endlessly.
        }
    }
}
