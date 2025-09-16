<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);
        return response()->json(['success' => true, 'data' => $notifications]);
    }

    public function markRead(Notification $notification, Request $request)
    {
        abort_if($notification->user_id !== $request->user()->id, 403, 'Forbidden');
        $notification->update(['read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->update(['read' => true]);
        return response()->json(['success' => true]);
    }
}

