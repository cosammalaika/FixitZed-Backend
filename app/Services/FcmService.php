<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
    * Send to a list of device tokens.
    */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (! $this->enabled() || empty($tokens)) {
            return;
        }

        // Ensure string values in data
        $data = collect($data)->map(fn ($v) => (string) $v)->all();

        $accessToken = $this->accessToken();
        if (! $accessToken) {
            return;
        }

        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }
            try {
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
                        'token' => $token,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('FCM send exception', [
                    'token' => $token,
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
}
