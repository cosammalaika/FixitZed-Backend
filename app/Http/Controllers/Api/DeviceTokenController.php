<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'token' => ['required', 'string'],
            'platform' => ['nullable', 'string', 'max:50'],
            'app' => ['required', 'string', 'in:customer,fixer'],
            'device_id' => ['nullable', 'string', 'max:191'],
        ]);

        $token = DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id' => $user->id,
                'platform' => $data['platform'] ?? $request->header('X-Platform'),
                'app' => $data['app'],
                'device_id' => $data['device_id'] ?? $request->header('X-Device-Id'),
                'last_seen_at' => now(),
            ],
        );

        return response()->json([
            'success' => true,
            'data' => $token,
        ]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        DeviceToken::where('token', $data['token'])
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token removed',
        ]);
    }
}
