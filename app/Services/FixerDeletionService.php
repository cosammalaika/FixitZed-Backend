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

            if ($user) {
                $user->delete();
            }

            Log::info('FixerDeletionService: deleted fixer and user', [
                'fixer_id' => $fixerId,
                'user_id' => $user?->id,
            ]);
        });
    }
}
