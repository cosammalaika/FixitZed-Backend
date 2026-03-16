<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class FcmService
{
    protected ?ServiceAccountCredentials $credentials = null;
    protected string $projectId = '';
    protected bool $enabled = false;

    public function __construct()
    {
        $path = config('services.fcm.credentials');
        $this->projectId = (string) config('services.fcm.project_id', '');
        if (empty($path) || empty($this->projectId)) {
            return;
        }

        try {
            $this->credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $path
            );
            $this->enabled = true;
        } catch (\Throwable $e) {
            Log::warning('FCM disabled: could not load credentials', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function enabled(): bool
    {
        return $this->enabled && $this->credentials !== null && $this->projectId !== '';
    }

    /**
    * Send a push to all device tokens for a user.
    */
    public function sendToUser(User $user, string $title, string $body, array $data = [], ?string $app = null): void
    {
        $tokens = DeviceToken::where('user_id', $user->id)
            ->when($app, fn ($q) => $q->where('app', $app))
            ->pluck('token')
            ->all();

        Log::info('push.dispatch.recipient_resolved', [
            'user_id' => $user->id,
            'app' => $app,
            'token_count' => count($tokens),
        ]);

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
    * Send to a list of device tokens.
    */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = collect($tokens)
            ->filter(fn ($token) => is_string($token) && trim($token) !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($tokens)) {
            Log::info('push.dispatch.skipped_empty_tokens', [
                'title' => $title,
                'payload_keys' => array_keys($data),
            ]);
            return;
        }

        if (! $this->enabled()) {
            Log::warning('push.dispatch.skipped_disabled', [
                'project_id' => $this->projectId,
                'token_count' => count($tokens),
                'payload_keys' => array_keys($data),
            ]);
            return;
        }

        // Ensure string values in data
        $data = collect($data)->map(function ($value) {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            if (is_scalar($value)) {
                return (string) $value;
            }

            return json_encode($value) ?: '';
        })->all();

        $accessToken = $this->accessToken();
        if (! $accessToken) {
            return;
        }

        foreach ($tokens as $token) {
            try {
                Log::info('push.fcm.request', [
                    'project_id' => $this->projectId,
                    'token' => $this->maskToken($token),
                    'title' => $title,
                    'payload_keys' => array_keys($data),
                ]);

                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->post(
                        "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                        [
                            'message' => [
                                'token' => $token,
                                'notification' => [
                                    'title' => $title,
                                    'body' => $body,
                                ],
                                'data' => $data,
                            ],
                        ]
                    );

                if ($response->failed()) {
                    Log::warning('FCM send failed', [
                        'token' => $this->maskToken($token),
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    $this->cleanupInvalidTokenIfNeeded($token, $response);
                    continue;
                }

                Log::info('push.fcm.response', [
                    'project_id' => $this->projectId,
                    'token' => $this->maskToken($token),
                    'status' => $response->status(),
                    'message_name' => $response->json('name'),
                ]);
            } catch (\Throwable $e) {
                Log::warning('FCM send exception', [
                    'token' => $this->maskToken($token),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function accessToken(): ?string
    {
        if (! $this->credentials) {
            return null;
        }
        try {
            $token = $this->credentials->fetchAuthToken();
            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('FCM access token error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function cleanupInvalidTokenIfNeeded(string $token, Response $response): void
    {
        $error = $response->json('error') ?? [];
        $status = strtoupper((string) ($error['status'] ?? ''));
        $message = strtolower((string) ($error['message'] ?? ''));
        $detailCodes = collect($error['details'] ?? [])
            ->map(function ($detail) {
                if (! is_array($detail)) {
                    return null;
                }

                $code = $detail['errorCode'] ?? $detail['reason'] ?? null;

                return is_string($code) ? strtoupper($code) : null;
            })
            ->filter()
            ->values()
            ->all();

        $shouldDelete = in_array($status, ['INVALID_ARGUMENT', 'NOT_FOUND'], true)
            || collect($detailCodes)->intersect(['UNREGISTERED', 'INVALID_ARGUMENT'])->isNotEmpty()
            || str_contains($message, 'not registered')
            || str_contains($message, 'registration token is not a valid');

        if (! $shouldDelete) {
            return;
        }

        DeviceToken::where('token', $token)->delete();

        Log::info('push.token.cleaned', [
            'token' => $this->maskToken($token),
            'status' => $status !== '' ? $status : null,
            'detail_codes' => $detailCodes,
        ]);
    }

    protected function maskToken(string $token): string
    {
        $trimmed = trim($token);

        if (strlen($trimmed) <= 16) {
            return $trimmed;
        }

        return substr($trimmed, 0, 8) . '...' . substr($trimmed, -8);
    }
}
