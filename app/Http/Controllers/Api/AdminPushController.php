<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;

class AdminPushController extends Controller
{
    public function __construct(private FcmService $fcm)
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['permission:send.notifications'])->only('send');
    }

    public function send(Request $request)
    {
        if (! $this->fcm->enabled()) {
            return response()->json([
                'success' => false,
                'message' => 'FCM is not configured on the server.',
            ], 400);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:1000'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'tokens' => ['sometimes', 'array'],
            'tokens.*' => ['string'],
            'data' => ['sometimes', 'array'],
            'app' => ['nullable', 'string', 'in:customer,fixer'],
        ]);

        $userIds = $validated['user_ids'] ?? [];
        $tokens = $validated['tokens'] ?? [];
        $data = $validated['data'] ?? [];

        if (empty($userIds) && empty($tokens)) {
            return response()->json([
                'success' => false,
                'message' => 'Provide at least one user_id or device token.',
            ], 422);
        }

        if (! empty($userIds)) {
            $users = User::whereIn('id', $userIds)->get();
            foreach ($users as $user) {
                $this->fcm->sendToUser(
                    $user,
                    $validated['title'],
                    $validated['body'],
                    $data,
                    $validated['app'] ?? null,
                );
            }
        }

        if (! empty($tokens)) {
            $this->fcm->sendToTokens($tokens, $validated['title'], $validated['body'], $data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Push sent',
        ]);
    }
}
