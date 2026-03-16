<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\CancellationReasonOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ServiceRequestCancellationService
{
    public function __construct(private FcmService $fcm)
    {
    }

    public function cancelByCustomer(
        ServiceRequest $serviceRequest,
        User $customer,
        string $reasonKey,
        ?string $note = null
    ): ServiceRequest {
        $reasonLabel = CancellationReasonOptions::labelFor($reasonKey);
        $note = $this->normalizeNote($note);

        if ($reasonLabel === null) {
            throw ValidationException::withMessages([
                'reason_key' => 'Select a valid cancellation reason.',
            ]);
        }

        if ($reasonKey === CancellationReasonOptions::OTHER && $note === null) {
            throw ValidationException::withMessages([
                'note' => 'Please specify the cancellation reason.',
            ]);
        }

        /** @var ServiceRequest $cancelled */
        $cancelled = DB::transaction(function () use (
            $serviceRequest,
            $customer,
            $reasonKey,
            $reasonLabel,
            $note
        ) {
            $locked = ServiceRequest::query()
                ->with(['service', 'customer', 'fixer.user'])
                ->lockForUpdate()
                ->findOrFail($serviceRequest->id);

            if ((int) $locked->customer_id !== (int) $customer->id) {
                abort(403, 'Forbidden');
            }

            $status = strtolower(trim((string) ($locked->status ?? '')));

            if (in_array($status, ['cancelled', 'canceled'], true)) {
                throw ValidationException::withMessages([
                    'request' => 'This booking has already been cancelled.',
                ]);
            }

            if (! in_array($status, ['pending', 'accepted'], true)) {
                throw ValidationException::withMessages([
                    'request' => 'This booking can no longer be cancelled.',
                ]);
            }

            $locked->forceFill([
                'status' => 'cancelled',
                'cancellation_reason_key' => $reasonKey,
                'cancellation_reason_label' => $reasonLabel,
                'cancellation_note' => $note,
                'canceled_by' => 'customer',
                'canceled_at' => now(),
                'fixer_snoozed_until' => null,
            ])->save();

            return $locked->fresh(['service', 'customer', 'fixer.user']);
        });

        $this->notifyAssignedFixer($cancelled);

        return $cancelled;
    }

    protected function notifyAssignedFixer(ServiceRequest $serviceRequest): void
    {
        $fixerUser = $serviceRequest->fixer?->user;
        if (! $fixerUser) {
            return;
        }

        $customerName = trim((string) (($serviceRequest->customer?->first_name ?? '') . ' ' . ($serviceRequest->customer?->last_name ?? '')));
        if ($customerName === '') {
            $customerName = 'A customer';
        }

        $serviceName = (string) ($serviceRequest->service?->name ?? 'service');
        $message = sprintf(
            '%s canceled the %s request. Reason: %s.',
            $customerName,
            $serviceName,
            $serviceRequest->cancellation_reason_label ?? 'Not provided'
        );

        if ($serviceRequest->cancellation_note) {
            $message .= ' Additional note: ' . $serviceRequest->cancellation_note;
        }

        $data = [
            'type' => 'service_request_cancelled',
            'service_request_id' => (int) $serviceRequest->id,
            'status' => 'cancelled',
        ];

        try {
            Notification::create([
                'user_id' => $fixerUser->id,
                'recipient_type' => 'Individual',
                'title' => 'Booking cancelled',
                'message' => $message,
                'data' => $data,
                'read' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to create fixer cancellation notification', [
                'service_request_id' => $serviceRequest->id,
                'fixer_user_id' => $fixerUser->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $this->fcm->sendToUser(
                $fixerUser,
                'Booking cancelled',
                $message,
                [
                    'payload' => 'booking_detail:' . $serviceRequest->id,
                    'service_request_id' => (string) $serviceRequest->id,
                    'sync_topics' => 'requests,notifications,dashboard',
                    'type' => 'service_request_cancelled',
                ],
                'fixer'
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to push fixer cancellation notification', [
                'service_request_id' => $serviceRequest->id,
                'fixer_user_id' => $fixerUser->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function normalizeNote(?string $note): ?string
    {
        if ($note === null) {
            return null;
        }

        $normalized = trim($note);

        return $normalized === '' ? null : $normalized;
    }
}
