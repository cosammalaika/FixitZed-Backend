<?php

namespace App\Http\Middleware;

use App\Support\UserSessionManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnsureApiAccountIsActive
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (! $user || UserSessionManager::isAccountActive($user)) {
            return $next($request);
        }

        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return new JsonResponse([
            'success' => false,
            'code' => 'account_disabled',
            'message' => 'Your account is disabled. Please contact support.',
        ], 423);
    }
}
