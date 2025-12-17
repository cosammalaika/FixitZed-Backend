<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\Request;

class SettingsThrottle extends ThrottleRequests
{
    public function handle(Request $request, Closure $next, string $profile = 'tight')
    {
        if ($profile === 'auth_login') {
            $raw = (string) Setting::get('auth.throttle_login', '6,1');
        } else {
            $fallback = $profile === 'relaxed' ? '6,1' : '10,1';
            $raw = (string) Setting::get('api.rate_limits.default_' . $profile, $fallback);
        }
        $parts = array_map('trim', explode(',', $raw));
        $maxAttempts = (int) ($parts[0] ?? 10);
        $decayMinutes = (int) ($parts[1] ?? 1);

        $maxAttempts = max(1, min($maxAttempts, 500));
        $decayMinutes = max(1, min($decayMinutes, 60));

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, 'settings:' . $profile);
    }
}
