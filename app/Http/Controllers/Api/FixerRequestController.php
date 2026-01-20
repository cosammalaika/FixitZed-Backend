<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyCustomerNoFixerJob;
use App\Models\Fixer;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestDecline;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Setting;
use App\Services\PriorityPointService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FixerRequestController extends Controller
{
    public function __construct(private PriorityPointService $priorityPoints)
    {
    }
    /**
     * GET /api/fixer/requests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            abort(403, 'Forbidden');
        }

        $expired = $this->expirePendingRequests();
        $status = $request->query('status');
        if ($status === 'declined') {
            $declines = ServiceRequestDecline::with(['serviceRequest.service', 'serviceRequest.customer'])
                ->where('fixer_id', $fixer->id)
                ->latest('declined_at')
                ->paginate(20)
                ->through(function (ServiceRequestDecline $decline) {
                    $sr = $decline->serviceRequest;
                    if (! $sr) {
                        return [
                            'id' => $decline->service_request_id,
                            'status' => 'declined',
                            'declined_at' => $decline->declined_at,
                        ];
                    }

                    $data = $this->transformForFixer($sr);
                    $data['status'] = 'declined';
                    $data['declined_at'] = $decline->declined_at;
                    return $data;
                });

            return response()->json(['success' => true, 'data' => $declines]);
        }

        $q = ServiceRequest::with(['service', 'customer'])
            ->where(function ($query) use ($fixer) {
                $query->where('fixer_id', $fixer->id)
                      ->orWhere(function ($inner) use ($fixer) {
                          $inner->whereNull('fixer_id')
                                ->whereHas('service.fixers', function ($svc) use ($fixer) {
                                    $svc->where('fixers.id', $fixer->id);
                                });
                      });
            })
            ->latest();
        if ($status) {
            $q->where('status', $status);
        }

        if (! $status || $status === 'pending') {
            $cutoff = $this->expiryCutoff();
            if ($cutoff) {
                $q->where('created_at', '>=', $cutoff);
            }
            $q->whereDoesntHave('declines', function ($query) use ($fixer) {
                $query->where('fixer_id', $fixer->id);
            });
        }

        $requests = $q->paginate(20)->through(function (ServiceRequest $sr) {
            return $this->transformForFixer($sr);
        });

        Log::info('[FIXITZED_TRACE] fixer.feed', [
            'user_id' => $user->id,
            'fixer_id' => $fixer->id,
            'status_filter' => $status,
            'cutoff' => optional($this->expiryCutoff())->toDateTimeString(),
            'timezone' => now()->timezoneName,
            'expired_marked' => $expired,
            'results_count' => $requests->total(),
        ]);

        return response()->json(['success' => true, 'data' => $requests]);
    }

    /**
     * POST /api/service-requests/{id}/accept
     * Assigns the request to the fixer (if unassigned) and deducts 1 coin atomically.
     */
    public function accept(ServiceRequest $serviceRequest, Request $request, WalletService $wallets): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            abort(403, 'Forbidden');
        }

        $response = null;

        DB::transaction(function () use (
            $serviceRequest,
            $fixer,
            $wallets,
            &$response
        ) {
            $locked = ServiceRequest::whereKey($serviceRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isExpired($locked) || $locked->status === 'cancelled') {
                $this->markExpired($locked);
                $response = response()->json([
                    'success' => false,
                    'message' => 'This request is no longer available.',
                ], 410);
                return;
            }

            if ($locked->status === 'accepted' && $locked->fixer_id === $fixer->id) {
                $response = response()->json([
                    'success' => true,
                    'message' => 'Request already accepted.',
                    'data' => $locked->fresh()->load(['service', 'fixer.user']),
                ]);
                return;
            }

            if ($locked->status !== 'pending') {
                $response = response()->json([
                    'success' => false,
                    'message' => 'This request has already been taken.',
                ], 409);
                return;
            }

            if ($locked->fixer_id && $locked->fixer_id !== $fixer->id) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'This request has already been assigned.',
                ], 409);
                return;
            }

            if (! $locked->fixer_id) {
                $eligible = $locked->service()
                    ->whereHas('fixers', fn ($q) => $q->where('fixers.id', $fixer->id))
                    ->exists();
                if (! $eligible) {
                    $response = response()->json([
                        'success' => false,
                        'message' => 'You are not eligible for this request.',
                    ], 403);
                    return;
                }
            }

            $alreadyDeclined = ServiceRequestDecline::where('service_request_id', $locked->id)
                ->where('fixer_id', $fixer->id)
                ->exists();
            if ($alreadyDeclined) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'You already declined this request.',
                ], 409);
                return;
            }

            // Deduct 1 coin first to enforce business rules
            $wallets->deductOnAccept($fixer->id, $locked->id);

            if (! $locked->fixer_id) {
                $locked->fixer_id = $fixer->id;
            }
            $locked->status = 'accepted';
            $locked->save();

            $response = response()->json([
                'success' => true,
                'data' => $locked->fresh()->load(['service', 'fixer.user']),
                'message' => '1 coin deducted. Request accepted.',
            ]);
        });

        if ($response instanceof JsonResponse) {
            return $response;
        }

        $this->priorityPoints->onAssignment($fixer, [
            'service_request_id' => $serviceRequest->id,
        ]);

        $fixer->forceFill(['last_assigned_at' => now()])->save();

        return response()->json([
            'success' => false,
            'message' => 'Unable to process request at this time.',
        ], 500);
    }

    public function decline(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) {
            abort(403, 'Forbidden');
        }

        $nextFixer = null;
        $response = null;

        DB::transaction(function () use ($serviceRequest, $fixer, &$nextFixer, &$response) {
            $locked = ServiceRequest::whereKey($serviceRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->isExpired($locked) || $locked->status === 'cancelled') {
                $this->markExpired($locked);
                $response = response()->json([
                    'success' => false,
                    'message' => 'This request is no longer available.',
                ], 410);
                return;
            }

            if ($locked->status !== 'pending') {
                $response = response()->json([
                    'success' => false,
                    'message' => 'This request has already been taken.',
                ], 409);
                return;
            }

            if ($locked->fixer_id && $locked->fixer_id !== $fixer->id) {
                $response = response()->json([
                    'success' => false,
                    'message' => 'This request is assigned to another fixer.',
                ], 409);
                return;
            }

            $decline = ServiceRequestDecline::firstOrCreate(
                [
                    'service_request_id' => $locked->id,
                    'fixer_id' => $fixer->id,
                ],
                ['declined_at' => now()],
            );

            // If this fixer is not assigned, just record decline and exit.
            if (! $locked->fixer_id) {
                $eligible = $locked->service()
                    ->whereHas('fixers', fn ($q) => $q->where('fixers.id', $fixer->id))
                    ->exists();
                if (! $eligible) {
                    $response = response()->json([
                        'success' => false,
                        'message' => 'You are not eligible for this request.',
                    ], 403);
                    return;
                }
                $response = response()->json([
                    'success' => true,
                    'message' => 'Request declined.',
                    'data' => $locked->fresh(['service', 'fixer']),
                ]);
                return;
            }

            $locked->fixer_snoozed_until = null;
            $locked->fixer_id = null;
            $locked->status = 'pending';
            $locked->save();

            $this->notifyCustomerDeclined($locked, $fixer);

            $nextFixer = $this->assignNextFixer($locked, $fixer->id);

            if (! $nextFixer) {
                NotifyCustomerNoFixerJob::dispatch($locked->id)
                    ->delay(now()->addMinutes(5));
            }
        });

        if ($response instanceof JsonResponse) {
            return $response;
        }

        $this->priorityPoints->onTimeout($fixer, [
            'service_request_id' => $serviceRequest->id,
        ]);

        if (! $nextFixer) {
            return response()->json([
                'success' => true,
                'message' => 'Fixer declined the request. We will notify you if another fixer becomes available.',
                'data' => $serviceRequest->fresh(['service', 'fixer']),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Request declined and reassigned.',
            'data' => $serviceRequest->fresh(['service', 'fixer']),
            'reassigned_to' => $nextFixer?->id,
        ]);
    }

    public function snooze(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer || $serviceRequest->fixer_id !== $fixer->id) {
            abort(403, 'Forbidden');
        }

        $serviceRequest->fixer_snoozed_until = now()->addHour();
        $serviceRequest->save();

        return response()->json([
            'success' => true,
            'message' => 'We will remind you again in one hour.',
            'data' => $serviceRequest->fresh(),
        ]);
    }

    /**
     * POST /api/fixer/requests/{id}/bill
     * Allows an assigned fixer to create/update a bill for a service request.
     */
    public function bill(ServiceRequest $serviceRequest, Request $request): JsonResponse
    {
        $user = $request->user();
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;
        if (! $fixer) abort(403, 'Forbidden');

        // Must be assigned to this fixer
        if ($serviceRequest->fixer_id !== $fixer->id) abort(403, 'Forbidden');

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $serviceRequest->payment;
        if ($payment) {
            if ($payment->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment has already been completed for this request.',
                ], 422);
            }

            $payment->update([
                'amount' => $validated['amount'],
                'status' => 'pending',
            ]);
        } else {
            $payment = Payment::create([
                'service_request_id' => $serviceRequest->id,
                'amount' => $validated['amount'],
                'status' => 'pending',
            ]);
        }

        // Set awaiting_payment so both apps can reflect the state clearly
        $serviceRequest->status = 'awaiting_payment';
        $serviceRequest->save();

        // Optionally: set request state to accepted (or leave) and notify customer here
        // $serviceRequest->status = 'accepted';
        // $serviceRequest->save();

        // Create an in-app notification for the customer
        try {
            Notification::create([
                'user_id' => $serviceRequest->customer_id,
                'title' => 'Payment Required',
                'message' => 'A bill of ' . number_format((float) $validated['amount'], 2) . ' has been issued for your ' . optional($serviceRequest->service)->name . ' request.',
                'read' => false,
            ]);
        } catch (\Throwable $e) {
            // ignore if notification model/schema differs
        }

        return response()->json([
            'success' => true,
            'data' => $payment->fresh(),
            'message' => 'Bill created and sent to customer.',
        ]);
    }

    protected function transformForFixer(ServiceRequest $serviceRequest): array
    {
        $data = $serviceRequest->toArray();

        $status = strtolower((string) ($serviceRequest->status ?? ''));
        $contactVisible = in_array($status, ['accepted', 'awaiting_payment', 'completed'], true);

        if (isset($data['customer']) && ! $contactVisible) {
            foreach (['contact_number', 'phone', 'mobile', 'email'] as $field) {
                if (array_key_exists($field, $data['customer'])) {
                    $data['customer'][$field] = null;
                }
            }
        }

        $data['customer_contact_visible'] = $contactVisible;
        $data['fixer_snoozed_until'] = $serviceRequest->fixer_snoozed_until;

        return $data;
    }

    protected function assignNextFixer(ServiceRequest $serviceRequest, int $excludeFixerId): ?Fixer
    {
        if ($this->isExpired($serviceRequest) || $serviceRequest->status === 'cancelled') {
            $this->markExpired($serviceRequest);
            return null;
        }

        $candidates = Fixer::query()
            ->with(['user'])
            ->withCount([
                'serviceRequests as accepted_requests_count' => function ($q) {
                    $q->whereIn('status', ['accepted', 'completed']);
                },
                'serviceRequests as total_requests_count',
            ])
            ->where('status', 'approved')
            ->where('id', '!=', $excludeFixerId)
            ->whereHas('wallet', function ($q) {
                $q->where('coin_balance', '>', 0);
            })
            ->whereDoesntHave('declines', function ($q) use ($serviceRequest) {
                $q->where('service_request_id', $serviceRequest->id);
            })
            ->get();

        if ($candidates->isEmpty()) {
            NotifyCustomerNoFixerJob::dispatch($serviceRequest->id)
                ->delay(now()->addMinutes(5));
            return null;
        }

        $selected = $candidates
            ->map(function (Fixer $fixer) use ($serviceRequest) {
                $total = (int) ($fixer->total_requests_count ?? 0);
                $accepted = (int) ($fixer->accepted_requests_count ?? 0);
                $acceptRate = $total > 0 ? $accepted / $total : 0;
                $distance = $this->estimateDistanceKm($fixer, $serviceRequest);

                $score = $this->priorityPoints->compositeScore($fixer, [
                    'distance_km' => $distance,
                    'accept_rate' => $acceptRate,
                ]);

                return compact('fixer', 'score');
            })
            ->sortByDesc('score')
            ->first();

        if (! $selected) {
            return null;
        }

        /** @var Fixer $candidate */
        $candidate = $selected['fixer'];

        $this->priorityPoints->onOffer($candidate, [
            'service_request_id' => $serviceRequest->id,
        ]);

        $serviceRequest->fixer_id = $candidate->id;
        $serviceRequest->status = 'pending';
        $serviceRequest->fixer_snoozed_until = null;
        $serviceRequest->save();

        $this->notifyFixerAssigned($serviceRequest, $candidate);

        $candidate->forceFill(['last_assigned_at' => now()])->save();

        return $candidate;
    }

    protected function isExpired(ServiceRequest $serviceRequest): bool
    {
        $cutoff = $this->expiryCutoff();
        if (! $cutoff) {
            return false;
        }
        return $serviceRequest->status === 'pending' && $serviceRequest->created_at < $cutoff;
    }

    protected function markExpired(ServiceRequest $serviceRequest): void
    {
        if ($serviceRequest->status === 'expired') {
            return;
        }
        $serviceRequest->status = 'expired';
        $serviceRequest->fixer_id = null;
        $serviceRequest->fixer_snoozed_until = null;
        $serviceRequest->save();
    }

    protected function expiryCutoff(): ?\Illuminate\Support\Carbon
    {
        $minutes = (int) Setting::get('requests.expiry_minutes', 15);
        if ($minutes <= 0) {
            return null;
        }
        return now()->subMinutes($minutes);
    }

    protected function expirePendingRequests(): int
    {
        $cutoff = $this->expiryCutoff();
        if (! $cutoff) {
            return 0;
        }
        return ServiceRequest::where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->update([
                'status' => 'expired',
                'fixer_id' => null,
                'fixer_snoozed_until' => null,
            ]);
    }

    protected function estimateDistanceKm(Fixer $fixer, ServiceRequest $serviceRequest): float
    {
        // TODO: integrate actual geo distance once coordinates are available.
        return (float) ($serviceRequest->distance_km ?? 0);
    }

    protected function notifyCustomerDeclined(ServiceRequest $serviceRequest, Fixer $fixer): void
    {
        try {
            Notification::create([
                'user_id' => $serviceRequest->customer_id,
                'recipient_type' => 'Individual',
                'title' => 'Fixer declined your request',
                'message' => sprintf(
                    '%s declined your %s request. We are finding another available fixer.',
                    optional($fixer->user)->name ?? 'A fixer',
                    optional($serviceRequest->service)->name ?? 'service'
                ),
                'read' => false,
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    protected function notifyFixerAssigned(ServiceRequest $serviceRequest, Fixer $fixer): void
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
        } catch (\Throwable $e) {
            // ignore
        }

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
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
