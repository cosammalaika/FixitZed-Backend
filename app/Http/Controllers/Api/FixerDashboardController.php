<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fixer;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestDecline;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class FixerDashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->loadMissing('fixer.wallet');
        /** @var Fixer|null $fixer */
        $fixer = $user->fixer;

        if (! $fixer) {
            abort(403, 'Forbidden');
        }

        $limit = max(1, min((int) $request->query('limit', 5), 10));
        $isApprovedFixer = $fixer->status === 'approved';

        $notificationsQuery = $this->notificationsQueryForUser($user);
        $unreadNotifications = (clone $notificationsQuery)->where('read', false)->count();
        $latestNotificationAt = (clone $notificationsQuery)->max('updated_at')
            ?: (clone $notificationsQuery)->max('created_at');

        $wallet = $fixer->wallet;
        $coins = (int) ($wallet?->coin_balance ?? 0);
        $totalEarnings = (float) $fixer->earnings()->sum('amount');

        $activeStatuses = ['pending', 'accepted', 'awaiting_payment', 'in_progress'];
        $activeBookingsQuery = ServiceRequest::query()
            ->with(['service', 'customer'])
            ->where('fixer_id', $fixer->id)
            ->whereIn('status', $activeStatuses)
            ->latest();

        $activeBookings = (clone $activeBookingsQuery)
            ->limit($limit)
            ->get()
            ->map(fn (ServiceRequest $serviceRequest) => $this->transformRequest($serviceRequest))
            ->values()
            ->all();

        $activeBookingsCount = (clone $activeBookingsQuery)->count();
        $completedCount = ServiceRequest::query()
            ->where('fixer_id', $fixer->id)
            ->where('status', 'completed')
            ->count();

        $recentRequestsQuery = ServiceRequest::query()
            ->with(['service', 'customer'])
            ->where(function ($query) use ($fixer, $isApprovedFixer) {
                $query->where('fixer_id', $fixer->id);

                if ($isApprovedFixer) {
                    $query->orWhere(function ($inner) use ($fixer) {
                        $inner->whereNull('fixer_id')
                            ->whereHas('service.fixers', function ($svc) use ($fixer) {
                                $svc->where('fixers.id', $fixer->id);
                            });
                    });
                }
            })
            ->latest();

        // Keep pending feed aligned with FixerRequestController (hide declines / expired old pending)
        $statusFilter = $request->query('status');
        if ($statusFilter) {
            $recentRequestsQuery->where('status', (string) $statusFilter);
        }
        if (! $statusFilter || $statusFilter === 'pending') {
            $cutoff = $this->expiryCutoff();
            if ($cutoff) {
                $recentRequestsQuery->where('created_at', '>=', $cutoff);
            }
            $recentRequestsQuery->whereDoesntHave('declines', function ($query) use ($fixer) {
                $query->where('fixer_id', $fixer->id);
            });
        }

        $recentRequests = (clone $recentRequestsQuery)
            ->limit($limit)
            ->get()
            ->map(fn (ServiceRequest $serviceRequest) => $this->transformRequest($serviceRequest))
            ->values()
            ->all();

        $requestsCount = (clone $recentRequestsQuery)->count();
        $latestRequestAt = $this->latestRequestTimestamp($fixer, $isApprovedFixer);
        $latestPaymentAt = $this->latestPaymentTimestamp($fixer);

        $dashboardUpdatedAt = $this->maxCarbon([
            $user->updated_at,
            $fixer->updated_at,
            $wallet?->updated_at,
            $this->parseCarbon($latestNotificationAt),
            $this->parseCarbon($latestRequestAt),
            $this->parseCarbon($latestPaymentAt),
        ]) ?? now()->utc();

        $version = sha1(json_encode([
            'u' => (int) $user->id,
            'f' => (int) $fixer->id,
            'fs' => (string) $fixer->status,
            'coins' => $coins,
            'earnings' => round($totalEarnings, 2),
            'unread' => $unreadNotifications,
            'requests_count' => $requestsCount,
            'completed_count' => $completedCount,
            'active_bookings_count' => $activeBookingsCount,
            'latest_notification_at' => $latestNotificationAt,
            'latest_request_at' => $latestRequestAt,
            'latest_payment_at' => $latestPaymentAt,
            'updated_at' => $dashboardUpdatedAt->toIso8601String(),
        ], JSON_UNESCAPED_SLASHES) ?: '');

        $payload = [
            'success' => true,
            'data' => [
                'version' => $version,
                'updated_at' => $dashboardUpdatedAt->toIso8601String(),
                'server_time' => now()->utc()->toIso8601String(),
                'fixer' => [
                    'id' => (int) $fixer->id,
                    'status' => (string) $fixer->status,
                    'priority_points' => (int) ($fixer->priority_points ?? 0),
                    'priorityPoints' => (int) ($fixer->priority_points ?? 0),
                ],
                'user' => [
                    'id' => (int) $user->id,
                    'first_name' => (string) ($user->first_name ?? ''),
                    'last_name' => (string) ($user->last_name ?? ''),
                    'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'avatar_url' => $user->avatar_url,
                    'address' => (string) ($user->address ?? ''),
                ],
                'stats' => [
                    'coins' => $coins,
                    'coin_balance' => $coins,
                    'total_earnings' => $totalEarnings,
                    'unread_notifications' => $unreadNotifications,
                    'requests_count' => $requestsCount,
                    'completed_count' => $completedCount,
                    'active_bookings_count' => $activeBookingsCount,
                ],
                'active_bookings' => $activeBookings,
                'recent_requests' => $recentRequests,
            ],
        ];

        return $this->etagJsonResponse($request, $payload, $dashboardUpdatedAt);
    }

    protected function etagJsonResponse(Request $request, array $payload, ?Carbon $lastModified = null): JsonResponse
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $etag = '"' . sha1($json ?: '') . '"';
        $ifNoneMatch = trim((string) $request->header('If-None-Match'));
        $headers = [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, must-revalidate',
            'ETag' => $etag,
            'Vary' => 'Authorization',
        ];

        if ($lastModified) {
            $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModified->getTimestamp()) . ' GMT';
        }

        if ($ifNoneMatch !== '' && ($ifNoneMatch === $etag || $ifNoneMatch === '*')) {
            return Response::json(null, 304, $headers);
        }

        return Response::json($payload, 200, $headers);
    }

    protected function notificationsQueryForUser(User $user)
    {
        $audiences = $this->audiencesForUser($user);

        return Notification::query()->where(function ($query) use ($user, $audiences) {
            $query->where(function ($q) use ($user) {
                $q->where('recipient_type', 'Individual')
                    ->where('user_id', $user->id);
            })->orWhere(function ($q) use ($audiences) {
                if (empty($audiences)) {
                    return;
                }
                $q->whereIn('recipient_type', $audiences);
            });
        });
    }

    protected function audiencesForUser(User $user): array
    {
        $roles = collect($user->getRoleNames() ?? [])
            ->filter()
            ->map(fn ($role) => trim((string) $role));

        if ($roles->isEmpty()) {
            return ['All'];
        }

        return $roles->flatMap(function ($role) {
            $normalized = ucfirst(strtolower($role));
            return [$role, $normalized, strtoupper($role), strtolower($role)];
        })->push('All')
            ->unique()
            ->values()
            ->all();
    }

    protected function transformRequest(ServiceRequest $serviceRequest): array
    {
        $service = $serviceRequest->service;
        $customer = $serviceRequest->customer;
        $assignedFixer = $serviceRequest->fixer;
        $contactVisible = in_array((string) $serviceRequest->status, ['accepted', 'completed'], true);

        return [
            'id' => (int) $serviceRequest->id,
            'service_id' => (int) ($serviceRequest->service_id ?? 0),
            'service_name' => (string) ($service?->name ?? 'Service'),
            'service' => [
                'id' => (int) ($service?->id ?? $serviceRequest->service_id ?? 0),
                'name' => (string) ($service?->name ?? 'Service'),
            ],
            'customer_id' => (int) ($serviceRequest->customer_id ?? 0),
            'customer' => [
                'id' => (int) ($customer?->id ?? $serviceRequest->customer_id ?? 0),
                'first_name' => (string) ($customer?->first_name ?? ''),
                'last_name' => (string) ($customer?->last_name ?? ''),
                'name' => trim((string) (($customer?->first_name ?? '') . ' ' . ($customer?->last_name ?? ''))),
                'contact_number' => $contactVisible ? ($customer?->contact_number ?? null) : null,
            ],
            'fixer_id' => $serviceRequest->fixer_id ? (int) $serviceRequest->fixer_id : null,
            'fixer' => $assignedFixer ? [
                'id' => (int) $assignedFixer->id,
                'priority_points' => (int) ($assignedFixer->priority_points ?? 0),
                'priorityPoints' => (int) ($assignedFixer->priority_points ?? 0),
            ] : null,
            'scheduled_at' => optional($serviceRequest->scheduled_at)->toIso8601String(),
            'fixer_snoozed_until' => optional($serviceRequest->fixer_snoozed_until)->toIso8601String(),
            'status' => (string) $serviceRequest->status,
            'location' => $serviceRequest->location,
            'customer_contact_visible' => $contactVisible,
            'customer_contact' => $contactVisible ? ($customer?->contact_number ?? null) : null,
            'created_at' => optional($serviceRequest->created_at)->toIso8601String(),
            'updated_at' => optional($serviceRequest->updated_at)->toIso8601String(),
        ];
    }

    protected function expiryCutoff(): ?Carbon
    {
        $minutes = (int) \App\Models\Setting::get('requests.expiry_minutes', 15);
        if ($minutes < 1) {
            $minutes = 15;
        }

        return now('UTC')->subMinutes($minutes);
    }

    protected function latestRequestTimestamp(Fixer $fixer, bool $isApprovedFixer): ?Carbon
    {
        $linked = ServiceRequest::query()
            ->where('fixer_id', $fixer->id)
            ->max('updated_at');

        $candidate = null;
        if ($isApprovedFixer) {
            $candidate = ServiceRequest::query()
                ->whereNull('fixer_id')
                ->whereHas('service.fixers', function ($query) use ($fixer) {
                    $query->where('fixers.id', $fixer->id);
                })
                ->max('updated_at');
        }

        $declines = ServiceRequestDecline::query()
            ->where('fixer_id', $fixer->id)
            ->max('updated_at');

        return $this->maxCarbon([
            $this->parseCarbon($linked),
            $this->parseCarbon($candidate),
            $this->parseCarbon($declines),
        ]);
    }

    protected function latestPaymentTimestamp(Fixer $fixer): ?Carbon
    {
        $timestamp = Payment::query()
            ->whereHas('serviceRequest', function ($query) use ($fixer) {
                $query->where('fixer_id', $fixer->id);
            })
            ->max('updated_at');

        return $this->parseCarbon($timestamp);
    }

    protected function parseCarbon(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    /**
     * @param array<int, Carbon|null> $values
     */
    protected function maxCarbon(array $values): ?Carbon
    {
        return collect($values)
            ->filter(fn ($value) => $value instanceof Carbon)
            ->sortByDesc(fn (Carbon $value) => $value->getTimestamp())
            ->first();
    }
}
