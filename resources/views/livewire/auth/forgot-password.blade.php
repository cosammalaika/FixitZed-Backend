<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => __('A reset link will be sent if the account exists.'),
        ]);
    }
}; ?>

{{-- <div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input wire:model="email" :label="__('Email Address')" type="email" required autofocus
            placeholder="email@example.com" />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Email password reset link') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        <span>{{ __('Or, return to') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
    </div>
</div> --}}
<div class="auth-content my-auto">
    <div class="text-center">
        <a href="login" class="d-block auth-logo">
            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="70">
        </a>
        <h5 class="mb-0">Reset Password</h5>
        <p class="text-muted mt-2">Reset Password with Minia.</p>
    </div>
    <div class="alert alert-success text-center my-4" role="alert">
        Enter your Email and instructions will be sent to you!
    </div>
    <form class="mt-4" wire:submit="sendPasswordResetLink">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" wire:model="email" placeholder="Enter email">
        </div>
        <div class="mb-3 mt-4">
            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Reset</button>
        </div>
    </form>

    <div class="mt-5 text-center">
        <p class="text-muted mb-0">Remember It ? <a href="{{ route('login') }}" class="text-primary fw-semibold"> Sign In
            </a> </p>
    </div>
</div>
