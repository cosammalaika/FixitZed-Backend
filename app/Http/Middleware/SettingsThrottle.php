<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\Request;

class SettingsThrottle extends ThrottleRequests
{
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        $profile = is_string($maxAttempts) && ! is_numeric($maxAttempts)
            ? trim($maxAttempts)
            : null;

        if ($profile) {
            if ($profile === 'auth_login') {
                $raw = (string) Setting::get('auth.throttle_login', '6,1');
            } else {
                $fallback = $profile === 'relaxed' ? '6,1' : '10,1';
                $raw = (string) Setting::get('api.rate_limits.default_' . $profile, $fallback);
            }

            $parts = array_map('trim', explode(',', $raw));
            $maxAttempts = (int) ($parts[0] ?? 10);
            $decayMinutes = (int) ($parts[1] ?? 1);
            $prefix = 'settings:' . $profile;
        } else {
            $maxAttempts = (int) $maxAttempts;
            $decayMinutes = (int) $decayMinutes;
        }

        $maxAttempts = max(1, min((int) $maxAttempts, 500));
        $decayMinutes = max(1, min((int) $decayMinutes, 60));

        return parent::handle($request, $next, $maxAttempts, $decayMinutes, $prefix);
    }
}
