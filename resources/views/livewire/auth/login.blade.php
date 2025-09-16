<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // Redirect to dashboard with full page load to ensure assets init
        $this->redirectRoute('dashboard');
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email) . '|' . request()->ip());
    }
}; ?>

{{-- <div class="flex flex-col gap-6">
    <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">{{ __('Log in') }}</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div> --}}
<div class="auth-content my-auto">
    <div class="text-center">
        <a href="login" class="d-block auth-logo">
            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="70">
        </a>
        <h5 class="mb-0">Welcome Back !</h5>
        <p class="text-muted mt-2">Please Sign in</p>
    </div>
    @if ($errors->any())
        <div id="login-error-alert"
            class="alert alert-danger alert-top-border alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-outline me-3 align-middle text-danger"></i>
            <strong>Login error</strong> - {{ $errors->first('email') ?? $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('login-error-alert');
                if (alert) {
                    let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 4000);
        </script>
    @endif
    <form class="mt-4 pt-2" wire:submit="login">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" wire:model="email" placeholder="Enter username">
        </div>
        <div class="mb-3">
            <div class="d-flex align-items-start">
                <div class="flex-grow-1">
                    <label class="form-label">Password</label>
                </div>
                <div class="flex-shrink-0">
                    <div class="">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-muted">Forgot
                                password?</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="input-group auth-pass-inputgroup">
                <input type="password" wire:model="password" class="form-control" placeholder="Enter password"
                    aria-label="Password" aria-describedby="password-addon">
                <button class="btn btn-light shadow-none ms-0" type="button" id="password-addon"><i
                        class="mdi mdi-eye-outline"></i></button>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-check" wire:model="remember" name="remember">
                    <label class="form-check-label" for="remember-check">
                        Remember me
                    </label>
                </div>
            </div>

        </div>
        <div class="mb-3">
            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Log
                In</button>
        </div>
    </form>

    <div class="mt-5 text-center">
        <p class="text-muted mb-0">Don't have an account ? <a href="{{ route('register') }}"
                class="text-primary fw-semibold"> Signup now </a> </p>
    </div>
</div>
