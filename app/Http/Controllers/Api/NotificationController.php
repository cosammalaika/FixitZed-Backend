<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $baseQuery = Notification::query()->where(function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('recipient_type', 'Individual')
                  ->where('user_id', $user->id);
            })->orWhere(function ($q) use ($user) {
                $q->where('recipient_type', $user->user_type);
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

        $perPage = (int) $request->query('per_page', 20);
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
        $isIndividualForUser = $notification->recipient_type === 'Individual' && $notification->user_id === $user->id;
        $isBroadcastForUser = $notification->recipient_type === $user->user_type;
        abort_unless($isIndividualForUser || $isBroadcastForUser, 403, 'Forbidden');
        $notification->update(['read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        Notification::where(function ($q) use ($user) {
            $q->where(function ($qq) use ($user) {
                $qq->where('recipient_type', 'Individual')->where('user_id', $user->id);
            })->orWhere(function ($qq) use ($user) {
                $qq->where('recipient_type', $user->user_type);
            });
        })->update(['read' => true]);

        return response()->json(['success' => true]);
    }
}
