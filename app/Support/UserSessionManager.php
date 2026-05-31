<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserSessionManager
{
    /**
     * Optionally delete existing API tokens for the given user on login.
     *
     * @return int number of revoked tokens.
     */
    public static function revokeActiveTokens(User $user): int
    {
        if (! config('auth.mobile_tokens.revoke_existing_on_login', false)) {
            return 0;
        }

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

    public static function isAccountActive(User $user): bool
    {
        $status = strtolower(trim((string) ($user->status ?? '')));

        if ($status === '') {
            return true;
        }

        return ! in_array($status, ['inactive', 'disabled', 'suspended', 'banned', 'deleted'], true);
    }
}
