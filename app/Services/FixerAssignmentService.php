<?php

namespace App\Services;

use App\Jobs\NotifyCustomerNoFixerJob;
use App\Models\Fixer;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\Setting;

class FixerAssignmentService
{
    public function assign(ServiceRequest $serviceRequest, ?int $excludeFixerId = null): ?Fixer
    {
        $serviceId = $serviceRequest->service_id;
        if (! $serviceId) {
            return null;
        }

        $requestLat = $serviceRequest->location_lat;
        $requestLng = $serviceRequest->location_lng;
        $radiusKm = (float) Setting::get('priority.location_radius_km', 15);
        $applyRadiusFilter = $requestLat !== null && $requestLng !== null && $radiusKm > 0;

        $candidates = Fixer::query()
            ->with(['user.locations' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->where('status', 'approved')
            ->when($excludeFixerId, fn ($query) => $query->where('id', '!=', $excludeFixerId))
            ->whereHas('services', function ($query) use ($serviceId) {
                $query->where('services.id', $serviceId);
            })
            ->whereDoesntHave('declines', function ($query) use ($serviceRequest) {
                $query->where('service_request_id', $serviceRequest->id);
            })
            ->get()
            ->map(function (Fixer $fixer) use ($requestLat, $requestLng) {
                $location = $fixer->user?->locations?->first();
                $distance = $this->distanceKm(
                    $requestLat,
                    $requestLng,
                    $location?->latitude,
                    $location?->longitude
                );

                return [
                    'fixer' => $fixer,
                    'priority_points' => (int) ($fixer->priority_points ?? 0),
                    'distance' => $distance,
                    'rating' => (float) ($fixer->rating_avg ?? 0.0),
                    'last_assigned' => optional($fixer->last_assigned_at)->timestamp ?? 0,
                ];
            })
            ->filter(function (array $data) use ($applyRadiusFilter, $radiusKm) {
                if (! $applyRadiusFilter) {
                    return true;
                }

                $distance = $data['distance'];

                if ($distance === null) {
                    // No stored fixer location—still allow the fixer to receive the request.
                    return true;
                }

                return $distance <= $radiusKm;
            })
            ->sort(function (array $a, array $b) {
                $distanceA = $a['distance'] ?? INF;
                $distanceB = $b['distance'] ?? INF;

                return $b['priority_points'] <=> $a['priority_points']
                    ?: $distanceA <=> $distanceB
                    ?: $b['rating'] <=> $a['rating']
                    ?: $a['last_assigned'] <=> $b['last_assigned'];
            })
            ->values();

        if ($candidates->isEmpty()) {
            $this->scheduleNoFixerNotification($serviceRequest);
            return null;
        }

        /** @var Fixer $selected */
        $selected = $candidates->first()['fixer'];

        $serviceRequest->forceFill([
            'fixer_id' => $selected->id,
            'status' => 'pending',
            'fixer_snoozed_until' => null,
        ])->save();

        $selected->forceFill(['last_offered_at' => now()])->save();

        $this->notifyFixer($serviceRequest, $selected);
        $this->notifyCustomerAwaitingAcceptance($serviceRequest, $selected);

        return $selected;
    }

    protected function notifyFixer(ServiceRequest $serviceRequest, Fixer $fixer): void
    {
        try {
            Notification::create([
                'user_id' => $fixer->user_id,
                'recipient_type' => 'Individual',
                'title' => 'New request available',
                'message' => sprintf(
                    'A customer needs %s on %s.',
                    optional($serviceRequest->service)->name ?? 'a service',
                    optional($serviceRequest->scheduled_at)?->format('d M Y • H:i') ?? 'an upcoming date'
                ),
                'read' => false,
            ]);
        } catch (\Throwable) {
            // Ignore notification failures so assignment still completes.
        }
    }

    protected function notifyCustomerAwaitingAcceptance(ServiceRequest $serviceRequest, Fixer $fixer): void
    {
        try {
            Notification::create([
                'user_id' => $serviceRequest->customer_id,
                'recipient_type' => 'Individual',
                'title' => 'Fixer found',
                'message' => sprintf(
                    '%s is reviewing your %s request now.',
                    optional($fixer->user)->name ?? 'A fixer',
                    optional($serviceRequest->service)->name ?? 'service'
                ),
                'read' => false,
            ]);
        } catch (\Throwable) {
            // The customer notification should not block assignment flow.
        }
    }

    protected function scheduleNoFixerNotification(ServiceRequest $serviceRequest): void
    {
        if (! $serviceRequest->customer_id) {
            return;
        }

        NotifyCustomerNoFixerJob::dispatch($serviceRequest->id)
            ->delay(now()->addMinutes(5));
    }

    protected function distanceKm($lat1, $lng1, $lat2, $lng2): ?float
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return null;
        }

        $earthRadiusKm = 6371;
        $latFrom = deg2rad((float) $lat1);
        $latTo = deg2rad((float) $lat2);
        $latDelta = deg2rad((float) $lat2 - (float) $lat1);
        $lngDelta = deg2rad((float) $lng2 - (float) $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
