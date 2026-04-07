<?php

namespace App\Services;

use App\Models\User;
use App\Support\ApiCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountDeletionService
{
    /**
     * Delete a customer-owned account without cascading away operational history.
     *
     * The users table uses soft deletes and several historical tables have
     * non-null user foreign keys. We revoke access, remove private app data,
     * anonymize the user row, then soft-delete it so bookings, payments, fixer
     * reports, and admin history stay referentially intact.
     *
     * @return array{user_id:int, revoked_tokens:int, deleted_at:string}
     */
    public function delete(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $user = $user->fresh(['fixer.services']) ?? $user;

            $userId = (int) $user->id;
            $deletedAt = now();
            $stamp = $deletedAt->format('YmdHis');
            $originalEmail = (string) ($user->email ?? '');

            $this->deleteStoredFiles($user);
            $this->removePrivateUserData($userId, $originalEmail);
            $this->anonymizeCustomerHistory($userId);
            $this->deactivateFixerProfile($user);

            $revokedTokens = $this->revokeSessions($user, $originalEmail);

            $anonymousHandle = "deleted_user_{$userId}_{$stamp}";

            $user->forceFill([
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'username' => $anonymousHandle,
                'email' => "{$anonymousHandle}@deleted.fixitzed.invalid",
                'contact_number' => "deleted-{$userId}-{$stamp}",
                'status' => 'Deleted',
                'address' => null,
                'province' => null,
                'district' => null,
                'email_verified_at' => null,
                'password' => bcrypt(Str::random(64)),
                'remember_token' => null,
                'profile_photo_path' => null,
                'nrc_front_path' => null,
                'nrc_back_path' => null,
                'documents' => null,
                'work_photos' => null,
                'loyalty_points' => 0,
                'mfa_secret' => null,
                'mfa_temp_secret' => null,
                'mfa_enabled' => false,
                'mfa_backup_codes' => null,
                'mfa_last_confirmed_at' => null,
            ])->save();

            try {
                $user->syncRoles([]);
            } catch (\Throwable $e) {
                Log::warning('Account deletion could not clear user roles', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }

            $user->delete();

            ApiCache::flush(['fixers']);
            ApiCache::flush(['fixers:top']);
            ApiCache::flush(['user:'.$userId]);

            Log::info('User account deleted through in-app account deletion', [
                'user_id' => $userId,
                'revoked_tokens' => $revokedTokens,
                'email_hash' => $originalEmail !== '' ? hash('sha256', $originalEmail) : null,
            ]);

            return [
                'user_id' => $userId,
                'revoked_tokens' => $revokedTokens,
                'deleted_at' => $deletedAt->toISOString(),
            ];
        });
    }

    private function revokeSessions(User $user, string $originalEmail): int
    {
        $revokedTokens = 0;

        try {
            $revokedTokens = (int) $user->tokens()->delete();
        } catch (\Throwable $e) {
            Log::warning('Account deletion could not revoke Sanctum tokens', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        if ($originalEmail !== '' && Schema::hasTable('password_reset_tokens')) {
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
        }

        return $revokedTokens;
    }

    private function removePrivateUserData(int $userId, string $originalEmail): void
    {
        $this->deleteRows('device_tokens', 'user_id', $userId);
        $this->deleteRows('user_trusted_devices', 'user_id', $userId);
        $this->deleteRows('locations', 'user_id', $userId);
        $this->deleteRows('notifications', 'user_id', $userId);
        $this->deleteRows('reviews', 'user_id', $userId);

        if (Schema::hasTable('reports')) {
            if (Schema::hasColumn('reports', 'target_user_id')) {
                DB::table('reports')->where('target_user_id', $userId)->update([
                    'target_user_id' => null,
                    'updated_at' => now(),
                ]);
            }

            // Reports filed by the deleted user are private support data.
            if (Schema::hasColumn('reports', 'user_id')) {
                DB::table('reports')->where('user_id', $userId)->delete();
            }
        }

        if ($originalEmail !== '' && Schema::hasTable('password_reset_tokens')) {
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
        }
    }

    private function anonymizeCustomerHistory(int $userId): void
    {
        if (Schema::hasTable('ratings')) {
            $updates = [];
            if (Schema::hasColumn('ratings', 'comment')) {
                $updates['comment'] = null;
            }
            if (Schema::hasColumn('ratings', 'updated_at')) {
                $updates['updated_at'] = now();
            }
            if ($updates !== []) {
                DB::table('ratings')
                    ->where('rater_id', $userId)
                    ->orWhere('rated_user_id', $userId)
                    ->update($updates);
            }
        }

        if (! Schema::hasTable('service_requests')) {
            return;
        }

        $updates = [];
        foreach ([
            'location',
            'location_lat',
            'location_lng',
            'customer_note',
            'cancellation_note',
        ] as $column) {
            if (Schema::hasColumn('service_requests', $column)) {
                $updates[$column] = null;
            }
        }

        if (Schema::hasColumn('service_requests', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        if ($updates !== []) {
            DB::table('service_requests')
                ->where('customer_id', $userId)
                ->update($updates);
        }

        $cancelUpdates = [];
        if (Schema::hasColumn('service_requests', 'status')) {
            $cancelUpdates['status'] = 'cancelled';
        }
        if (Schema::hasColumn('service_requests', 'cancellation_reason_key')) {
            $cancelUpdates['cancellation_reason_key'] = 'account_deleted';
        }
        if (Schema::hasColumn('service_requests', 'cancellation_reason_label')) {
            $cancelUpdates['cancellation_reason_label'] = 'Customer account deleted';
        }
        if (Schema::hasColumn('service_requests', 'canceled_by')) {
            $cancelUpdates['canceled_by'] = 'customer';
        }
        if (Schema::hasColumn('service_requests', 'canceled_at')) {
            $cancelUpdates['canceled_at'] = now();
        }
        if (Schema::hasColumn('service_requests', 'updated_at')) {
            $cancelUpdates['updated_at'] = now();
        }

        if ($cancelUpdates !== [] && Schema::hasColumn('service_requests', 'status')) {
            DB::table('service_requests')
                ->where('customer_id', $userId)
                ->whereNotIn('status', ['completed', 'cancelled', 'expired'])
                ->update($cancelUpdates);
        }
    }

    private function deactivateFixerProfile(User $user): void
    {
        $fixer = $user->fixer;
        if (! $fixer) {
            return;
        }

        $fixer->services()->detach();

        if (Schema::hasTable('service_request_declines')) {
            DB::table('service_request_declines')->where('fixer_id', $fixer->id)->delete();
        }

        $fixerUpdates = [
            'status' => 'rejected',
            'bio' => null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('fixers', 'priority_points')) {
            $fixerUpdates['priority_points'] = 0;
        }

        DB::table('fixers')->where('id', $fixer->id)->update($fixerUpdates);

        if (Schema::hasTable('service_requests') && Schema::hasColumn('service_requests', 'fixer_id')) {
            DB::table('service_requests')
                ->where('fixer_id', $fixer->id)
                ->whereNotIn('status', ['completed', 'cancelled', 'expired'])
                ->update([
                    'fixer_id' => null,
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
        }
    }

    private function deleteRows(string $table, string $column, mixed $value): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)->where($column, $value)->delete();
    }

    private function deleteStoredFiles(User $user): void
    {
        foreach ($this->collectStoredPaths($user) as $path) {
            if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                continue;
            }

            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                Log::warning('Account deletion could not remove stored user file', [
                    'user_id' => $user->id,
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function collectStoredPaths(User $user): array
    {
        $paths = [];

        foreach ([
            $user->profile_photo_path,
            $user->nrc_front_path,
            $user->nrc_back_path,
        ] as $path) {
            if (is_string($path) && trim($path) !== '') {
                $paths[] = trim($path);
            }
        }

        foreach ([$user->documents, $user->work_photos] as $items) {
            $paths = array_merge($paths, $this->flattenPathList($items));
        }

        return array_values(array_unique($paths));
    }

    /**
     * @return list<string>
     */
    private function flattenPathList(mixed $value): array
    {
        if (is_string($value) && trim($value) !== '') {
            return [trim($value)];
        }

        if (! is_array($value)) {
            return [];
        }

        $paths = [];
        foreach ($value as $item) {
            $paths = array_merge($paths, $this->flattenPathList($item));
        }

        return $paths;
    }
}
