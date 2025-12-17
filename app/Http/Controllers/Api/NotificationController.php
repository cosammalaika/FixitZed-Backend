<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ResolvesPerPage;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ResolvesPerPage;

    public function index(Request $request)
    {
        $user = $request->user();
        $audiences = $this->audiencesForUser($user);

        $baseQuery = Notification::query()->where(function ($query) use ($user, $audiences) {
            $query->where(function ($q) use ($user) {
                $q->where('recipient_type', 'Individual')
                  ->where('user_id', $user->id);
            })->orWhere(function ($q) use ($audiences) {
                if (empty($audiences)) {
                    return;
                }
                $q->whereIn('recipient_type', $audiences);
            });
        })->latest();

        $unreadCount = (clone $baseQuery)->where('read', false)->count();

        $limit = (int) $request->query('limit', 0);
        if ($limit > 0) {
            $items = $baseQuery->take($limit)->get();
            return response()->json([
                'success' => true,
                'data' => $items,
                'unread_count' => $unreadCount,
            ]);
        }

        $perPage = $this->resolvePerPage($request, 'notifications.per_page_default');
        $paginated = $baseQuery->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $paginated,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(Notification $notification, Request $request)
    {
        $user = $request->user();
        $audiences = $this->audiencesForUser($user);
        $isIndividualForUser = $notification->recipient_type === 'Individual' && $notification->user_id === $user->id;
        $isBroadcastForUser = in_array($notification->recipient_type, $audiences, true);
        abort_unless($isIndividualForUser || $isBroadcastForUser, 403, 'Forbidden');
        $notification->update(['read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $audiences = $this->audiencesForUser($user);

        Notification::where(function ($q) use ($user, $audiences) {
            $q->where(function ($qq) use ($user) {
                $qq->where('recipient_type', 'Individual')->where('user_id', $user->id);
            })->orWhere(function ($qq) use ($audiences) {
                if (empty($audiences)) {
                    return;
                }
                $qq->whereIn('recipient_type', $audiences);
            });
        })->update(['read' => true]);

        return response()->json(['success' => true]);
    }

    private function audiencesForUser($user): array
    {
        $roles = collect($user?->getRoleNames() ?? [])
            ->filter()
            ->map(fn ($role) => trim($role));

        if ($roles->isEmpty()) {
            return [];
        }

        return $roles->flatMap(function ($role) {
            $normalized = ucfirst(strtolower($role));
            return [
                $role,
                $normalized,
                strtoupper($role),
                strtolower($role),
            ];
        })->push('All')
          ->unique()
          ->values()
          ->all();
    }
}
