<?php

namespace App\Services;

use App\Models\Fixer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixerDeletionService
{
    public function deleteFixerAndUser(int $fixerId): void
    {
        DB::transaction(function () use ($fixerId) {
            $fixer = Fixer::with('user')->find($fixerId);

            if (! $fixer) {
                return;
            }

            $user = $fixer->user;

            $fixer->delete();

            if ($user && $user->exists) {
                $user->refresh();
            }

            Log::info('FixerDeletionService: deleted fixer profile and preserved user account', [
                'fixer_id' => $fixerId,
                'user_id' => $user?->id,
            ]);
        });
    }

    /**
     * Cleanup reference (manual SQL if legacy orphans remain):
     * DELETE FROM fixer_wallets WHERE fixer_id NOT IN (SELECT id FROM fixers);
     * DELETE FROM fixer_subscriptions WHERE fixer_id NOT IN (SELECT id FROM fixers);
     */
}
