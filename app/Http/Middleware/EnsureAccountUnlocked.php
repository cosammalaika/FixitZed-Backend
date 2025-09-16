<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountUnlocked
{
    public function handle(Request $request, Closure $next)
    {
        if (
            Auth::check() &&
            session('locked') &&
            ! $request->routeIs(['lock.screen', 'lock.unlock', 'lock.activate', 'logout'])
        ) {
            return redirect()->route('lock.screen');
        }

        return $next($request);
    }
}
