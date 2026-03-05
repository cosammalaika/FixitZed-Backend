<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Notifications\Notification;

class NoFixerFoundNotification extends Notification
{
    public function __construct(private readonly ServiceRequest $serviceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        // This project stores app notifications in App\Models\Notification.
        return [];
    }

    public function toLegacyPayload(): array
    {
        $reference = '#'.$this->serviceRequest->id;
        $serviceName = $this->serviceRequest->service?->name;

        $requestLabel = $serviceName
            ? sprintf('%s for %s', $reference, $serviceName)
            : $reference;

        return [
            'title' => 'No fixer found',
            'message' => sprintf(
                "We couldn't find an available fixer for your request (%s). We'll notify you when one is available.",
                $requestLabel
            ),
        ];
    }
}
