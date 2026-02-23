<?php

namespace App\Services;

use App\Models\Fixer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixerDeletionService
{
    public function deactivateFixer(int $fixerId): void
    {
        DB::transaction(function () use ($fixerId) {
            $fixer = Fixer::with('user')->find($fixerId);

            if (! $fixer) {
                return;
            }

            $user = $fixer->user;

            // Keep the fixer profile row so historical bookings/requests remain linked.
            $fixer->forceFill(['status' => 'rejected'])->save();

            if ($user && $user->exists && ! $user->hasRole('Customer')) {
                $user->assignRole('Customer');
            }

            Log::info('FixerDeletionService: deactivated fixer profile and preserved linked history', [
                'fixer_id' => $fixerId,
                'user_id' => $user?->id,
                'fixer_status' => $fixer->status,
            ]);
        });
    }

    public function deleteFixerAndUser(int $fixerId): void
    {
        // Backward-compatible alias used by existing admin Livewire component.
        $this->deactivateFixer($fixerId);
    }

    /**
     * Cleanup reference (manual SQL if legacy orphans remain):
     * DELETE FROM fixer_wallets WHERE fixer_id NOT IN (SELECT id FROM fixers);
     * DELETE FROM fixer_subscriptions WHERE fixer_id NOT IN (SELECT id FROM fixers);
     */
}
