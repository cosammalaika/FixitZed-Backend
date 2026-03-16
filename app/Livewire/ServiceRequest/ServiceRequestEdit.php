<?php

namespace App\Livewire\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Fixer;
use App\Models\Notification;
use App\Models\Service;
use App\Models\Payment;
use App\Support\ProvinceDistrict;
use Livewire\Component;

class ServiceRequestEdit extends Component
{
    public $serviceRequestId;
    public $customer_id, $fixer_id, $service_id, $scheduled_at, $status;
    public $province = '';
    public $district = '';
    public array $provinceOptions = [];
    public array $districtOptions = [];
    public $customers, $fixers, $services, $hasValidPayment = false;
    public array $provinceDistricts = [];


    public function mount($id)
    {
        $this->serviceRequestId = $id;

        $serviceRequest = ServiceRequest::findOrFail($id);

        $this->customer_id = $serviceRequest->customer_id;
        $this->fixer_id = $serviceRequest->fixer_id;
        $this->service_id = $serviceRequest->service_id;
        $this->scheduled_at = $serviceRequest->scheduled_at;
        $this->status = $serviceRequest->status;
        $this->hydrateLocation($serviceRequest->location);

        $this->customers = User::all();
        $this->fixers = Fixer::all();
        $this->services = Service::all();

        $this->hasValidPayment = Payment::where('service_request_id', $id)
            ->whereIn('status', ['accepted', 'completed']) // adjust based on your logic
            ->exists();

        $this->loadProvinceData();
    }

    protected function loadProvinceData(): void
    {
        $map = ProvinceDistrict::map();
        $this->provinceDistricts = $map;
        $this->provinceOptions = array_keys($map);
        $this->districtOptions = $map[$this->province] ?? [];
        if (! in_array($this->district, $this->districtOptions, true)) {
            $this->district = '';
        }
    }

    protected function hydrateLocation(?string $location): void
    {
        if (! $location) {
            $this->province = '';
            $this->district = '';
            return;
        }

        $parts = array_map(
            static fn ($segment) => trim($segment),
            explode(',', $location, 2)
        );

        $this->province = $parts[0] ?? '';
        $this->district = $parts[1] ?? '';
    }

    public function render()
    {
        return view('livewire.service-request.service-request-edit', [
            'provinceMap' => $this->provinceDistricts,
        ]);
    }

    public function update()
    {
        $this->validate([
            'customer_id' => 'required|exists:users,id',
            'fixer_id' => 'required|exists:fixers,id',
            'service_id' => 'required|exists:services,id',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:pending,accepted,completed,cancelled',
            'province' => 'required|string',
            'district' => 'required|string',
        ]);

        // Ensure the selected fixer is approved and linked to the selected service
        $isQualified = Fixer::where('id', $this->fixer_id)
            ->where('status', 'approved')
            ->whereHas('services', function ($q) {
                $q->where('services.id', $this->service_id);
            })
            ->exists();
        if (!$isQualified) {
            $this->addError('fixer_id', 'Selected fixer is not qualified for the chosen service.');
            return;
        }

        $serviceRequest = ServiceRequest::findOrFail($this->serviceRequestId);
        $previousFixerId = (int) ($serviceRequest->fixer_id ?? 0);
        $previousStatus = (string) ($serviceRequest->status ?? '');

        if ($this->status === 'completed') {
            $payment = Payment::where('service_request_id', $this->serviceRequestId)
                ->whereIn('status', ['accepted', 'completed'])
                ->first();

            if (!$payment) {
                $this->dispatchBrowserEvent('flash-message', [
                    'type' => 'error',
                    'message' => 'Cannot mark as completed. Payment has not been made.',
                ]);
                return;
            }
        }

        $serviceRequest->update([
            'customer_id' => $this->customer_id,
            'fixer_id' => $this->fixer_id,
            'service_id' => $this->service_id,
            'scheduled_at' => $this->scheduled_at,
            'status' => $this->status,
            'location' => trim($this->province . ', ' . $this->district),
        ]);

        $serviceRequest->load(['service', 'customer', 'fixer.user']);
        $this->notifyRelevantParties($serviceRequest, $previousFixerId, $previousStatus);

        log_user_action('updated service request', "ServiceRequest ID: {$this->serviceRequestId}");

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Service request updated successfully.',
            'redirect' => route('serviceRequest.index'),
        ]);
    }

    public function updatedProvince($value): void
    {
        $value = (string) $value;
        $this->province = $value;
        if (empty($this->provinceDistricts)) {
            $this->loadProvinceData();
        }
        $this->districtOptions = $this->provinceDistricts[$value] ?? [];
        if (! in_array($this->district, $this->districtOptions, true)) {
            $this->district = '';
        }
    }

    protected function notifyRelevantParties(ServiceRequest $serviceRequest, int $previousFixerId, string $previousStatus): void
    {
        $status = (string) ($serviceRequest->status ?? '');
        $fixerUser = $serviceRequest->fixer?->user;
        $fixerChanged = (int) ($serviceRequest->fixer_id ?? 0) !== $previousFixerId;
        $statusChanged = $status !== $previousStatus;

        if ($fixerUser && ($fixerChanged || ($statusChanged && in_array($status, ['pending', 'accepted'], true)))) {
            try {
                Notification::create([
                    'user_id' => $fixerUser->id,
                    'recipient_type' => 'Individual',
                    'title' => 'New request available',
                    'message' => sprintf(
                        'A customer needs %s on %s.',
                        optional($serviceRequest->service)->name ?? 'a service',
                        optional($serviceRequest->scheduled_at)?->format('d M Y • H:i') ?? 'an upcoming date'
                    ),
                    'data' => [
                        'app' => 'fixer',
                        'type' => 'service_request_assigned',
                        'service_request_id' => (string) $serviceRequest->id,
                        'payload' => 'fixer_request:' . $serviceRequest->id,
                        'sync_topics' => 'requests,notifications,dashboard',
                    ],
                    'read' => false,
                ]);
            } catch (\Throwable) {
                // Admin edit should not fail if notification creation fails.
            }
        }

        if (($fixerChanged || $statusChanged) && in_array($status, ['pending', 'accepted'], true)) {
            try {
                $accepted = $status === 'accepted';
                Notification::create([
                    'user_id' => $serviceRequest->customer_id,
                    'recipient_type' => 'Individual',
                    'title' => $accepted ? 'Request accepted' : 'Fixer found',
                    'message' => $accepted
                        ? sprintf(
                            '%s accepted your %s request.',
                            optional($fixerUser)->name ?? 'A fixer',
                            optional($serviceRequest->service)->name ?? 'service'
                        )
                        : sprintf(
                            '%s is reviewing your %s request now.',
                            optional($fixerUser)->name ?? 'A fixer',
                            optional($serviceRequest->service)->name ?? 'service'
                        ),
                    'data' => [
                        'app' => 'customer',
                        'type' => $accepted ? 'service_request_accepted' : 'service_request_pending_acceptance',
                        'service_request_id' => (string) $serviceRequest->id,
                        'payload' => 'booking_detail:' . $serviceRequest->id,
                        'sync_topics' => 'bookings,notifications,dashboard',
                    ],
                    'read' => false,
                ]);
            } catch (\Throwable) {
                // Admin edit should not fail if notification creation fails.
            }
        }

        if ($statusChanged && $status === 'cancelled' && $fixerUser) {
            try {
                Notification::create([
                    'user_id' => $fixerUser->id,
                    'recipient_type' => 'Individual',
                    'title' => 'Booking cancelled',
                    'message' => sprintf(
                        'An admin cancelled the %s request.',
                        optional($serviceRequest->service)->name ?? 'service'
                    ),
                    'data' => [
                        'app' => 'fixer',
                        'type' => 'service_request_cancelled',
                        'service_request_id' => (string) $serviceRequest->id,
                        'payload' => 'booking_detail:' . $serviceRequest->id,
                        'sync_topics' => 'requests,notifications,dashboard',
                    ],
                    'read' => false,
                ]);
            } catch (\Throwable) {
                // Admin edit should not fail if notification creation fails.
            }
        }

        if ($statusChanged && $status === 'completed') {
            try {
                Notification::create([
                    'user_id' => $serviceRequest->customer_id,
                    'recipient_type' => 'Individual',
                    'title' => 'Booking completed',
                    'message' => sprintf(
                        'Your %s booking has been marked as completed.',
                        optional($serviceRequest->service)->name ?? 'service'
                    ),
                    'data' => [
                        'app' => 'customer',
                        'type' => 'service_request_completed',
                        'service_request_id' => (string) $serviceRequest->id,
                        'payload' => 'booking_detail:' . $serviceRequest->id,
                        'sync_topics' => 'bookings,notifications,dashboard',
                    ],
                    'read' => false,
                ]);
            } catch (\Throwable) {
                // Admin edit should not fail if notification creation fails.
            }

            if ($fixerUser) {
                try {
                    Notification::create([
                        'user_id' => $fixerUser->id,
                        'recipient_type' => 'Individual',
                        'title' => 'Booking completed',
                        'message' => sprintf(
                            '%s has been marked as completed.',
                            optional($serviceRequest->service)->name ?? 'This booking'
                        ),
                        'data' => [
                            'app' => 'fixer',
                            'type' => 'service_request_completed',
                            'service_request_id' => (string) $serviceRequest->id,
                            'payload' => 'booking_detail:' . $serviceRequest->id,
                            'sync_topics' => 'requests,notifications,dashboard',
                        ],
                        'read' => false,
                    ]);
                } catch (\Throwable) {
                    // Admin edit should not fail if notification creation fails.
                }
            }
        }
    }

}
