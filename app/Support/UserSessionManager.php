<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserSessionManager
{
    /**
     * Delete all existing API tokens for the given user to enforce single-session login.
     *
     * @return int number of revoked tokens.
     */
    public static function revokeActiveTokens(User $user): int
    {
        try {
            return $user->tokens()->delete();
        } catch (\Throwable $e) {
            Log::warning('Failed to revoke active sessions', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return 0;
    }
}
