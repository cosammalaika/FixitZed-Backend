<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginAudit;
use App\Models\User;
use App\Support\MfaService;
use App\Support\UserSessionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MfaController extends Controller
{
    public function setup(Request $request, MfaService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $secret = $service->generateSecret();

        $user->forceFill([
            'mfa_temp_secret' => Crypt::encryptString($secret),
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
        ])->save();

        $otpauth = $service->makeQrUrl(config('app.name'), $user->email, $secret);

        return response()->json([
            'success' => true,
            'secret' => $secret,
            'otpauth_url' => $otpauth,
        ]);
    }

    public function enable(Request $request, MfaService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = $user->mfa_temp_secret ?: $user->mfa_secret;
        if (! $secret) {
            throw ValidationException::withMessages([
                'code' => ['No MFA secret is set up. Please request a new setup.'],
            ]);
        }

        $plaintextSecret = Crypt::decryptString($secret);

        if (! $service->verify($plaintextSecret, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid.'],
            ]);
        }

        $backupCodes = $service->generateBackupCodes();
        $hashedCodes = array_map(fn ($code) => Hash::make($code), $backupCodes);

        $user->forceFill([
            'mfa_secret' => Crypt::encryptString($plaintextSecret),
            'mfa_temp_secret' => null,
            'mfa_enabled' => true,
            'mfa_backup_codes' => $hashedCodes,
            'mfa_last_confirmed_at' => now(),
        ])->save();

        LoginAudit::create([
            'user_id' => $user->id,
            'event' => 'mfa.enable',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'backup_codes' => $backupCodes,
        ]);
    }

    public function disable(Request $request, MfaService $service): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'code' => ['nullable', 'string'],
            'backup_code' => ['nullable', 'string'],
        ]);

        if (! $user->mfa_enabled) {
            return response()->json([
                'success' => true,
                'message' => 'MFA already disabled.',
            ]);
        }

        $verified = false;

        if (!empty($data['backup_code'])) {
            $verified = $user->consumeBackupCode($data['backup_code']);
        } elseif (!empty($data['code'])) {
            $secret = $user->mfa_secret ? Crypt::decryptString($user->mfa_secret) : null;
            if ($secret) {
                $verified = $service->verify($secret, $data['code']);
            }
        }

        if (! $verified) {
            throw ValidationException::withMessages([
                'code' => ['The code provided is invalid.'],
            ]);
        }

        $user->forceFill([
            'mfa_secret' => null,
            'mfa_temp_secret' => null,
            'mfa_enabled' => false,
            'mfa_backup_codes' => null,
            'mfa_last_confirmed_at' => null,
        ])->save();

        LoginAudit::create([
            'user_id' => $user->id,
            'event' => 'mfa.disable',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Multi-factor authentication disabled.',
        ]);
    }

    public function complete(Request $request, MfaService $service): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'code' => ['nullable', 'string'],
            'backup_code' => ['nullable', 'string'],
            'device_name' => ['nullable', 'string', 'max:191'],
        ]);

        $payload = Cache::pull($this->mfaCacheKey($data['token']));

        if (! $payload) {
            return response()->json([
                'success' => false,
                'message' => 'The MFA challenge has expired. Please login again.',
            ], 410);
        }

        $user = User::find($payload['user_id']);
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'The MFA challenge is no longer valid.',
            ], 410);
        }

        $verified = false;

        if (! empty($data['backup_code'])) {
            $verified = $user->consumeBackupCode($data['backup_code']);
        } elseif (! empty($data['code'])) {
            $secret = $user->mfa_secret ? Crypt::decryptString($user->mfa_secret) : null;
            if ($secret) {
                $verified = $service->verify($secret, $data['code']);
            }
        }

        if (! $verified) {
            LoginAudit::create([
                'user_id' => $user->id,
                'event' => 'login.mfa',
                'status' => 'failed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'identifier' => $payload['identifier'] ?? null,
                ],
            ]);

            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or expired.'],
            ]);
        }

        $user->forceFill([
            'mfa_last_confirmed_at' => now(),
        ])->save();

        $deviceToken = null;
        if ($payload['remember_device']) {
            $deviceToken = $user->issueTrustedDevice($data['device_name'] ?? null);
        }

        $revokedSessions = UserSessionManager::revokeActiveTokens($user);
        $token = $user->createToken('mobile')->plainTextToken;

        $metadata = [
            'user_id' => $user->id,
            'event' => 'login.mfa',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'identifier' => $payload['identifier'] ?? null,
                'remember_device' => (bool) $payload['remember_device'],
            ],
        ];

        if ($revokedSessions > 0) {
            $metadata['metadata']['revoked_sessions'] = $revokedSessions;
        }
        if (!empty($deviceToken)) {
            $metadata['metadata']['trusted_device_token_issued'] = true;
        }

        LoginAudit::create($metadata);

        return response()->json([
            'success' => true,
            'token' => $token,
            'requires_verification' => ! $user->hasVerifiedEmail(),
            'device_token' => $deviceToken,
        ]);
    }

    private function mfaCacheKey(string $token): string
    {
        return 'mfa:challenge:' . $token;
    }

}
