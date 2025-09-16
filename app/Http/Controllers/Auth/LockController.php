<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LockController
{
    public function activate(Request $request): RedirectResponse
    {
        $request->session()->put('locked', true);

        return redirect()->route('lock.screen');
    }

    public function show(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! $request->session()->has('locked')) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.lock-screen', [
            'user' => Auth::user(),
        ]);
    }

    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        if (! $user || ! Hash::check($request->password, $user->getAuthPassword())) {
            return back()->withErrors([
                'password' => __('The provided password is incorrect.'),
            ]);
        }

        $request->session()->forget('locked');

        return redirect()->intended(route('dashboard'));
    }
}
