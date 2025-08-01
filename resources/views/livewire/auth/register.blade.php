<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $first_name = '',
        $last_name = '',
        $username = '',
        $email = '',
        $contact_number = '',
        $user_type = 'Customer'; // Default role
    public string $status = 'Active',
        $address = '',
        $password = '',
        $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['required', 'string', 'max:20'],
            'user_type' => ['required', 'in:Customer,Fixer,Admin,Support'],
            'status' => ['required', 'in:Active,Inactive'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
};
?>

{{-- 


<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div> --}}
<div class="auth-content my-auto">
    <div class="text-center">
        <a href="login" class="d-block auth-logo">
            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="70">
        </a>
        <h5 class="mb-0">Register Account</h5>
        <p class="text-muted mt-2">Get your free account now.</p>
    </div>
    <form class="needs-validation mt-4 pt-2" novalidate wire:submit="register">
        <div class="row">
            <div class="col-md-6">
                <label for="useremail" class="form-label">First Name</label>
                <input type="text" class="form-control" wire:model="first_name" placeholder="Enter First Name"
                    required>
                <div class="invalid-feedback">
                    Please Enter First Name
                </div>
            </div>
            <div class="col-md-6">
                <label for="useremail" class="form-label">Last Name</label>
                <input type="text" class="form-control" wire:model="last_name" placeholder="Enter Last Name"
                    required>
                <div class="invalid-feedback">
                    Please Enter Last Name
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" wire:model="username" placeholder="Enter username" required>
                <div class="invalid-feedback">
                    Please Enter Username
                </div>
            </div>

            <div class="col-md-6">
                <label for="username" class="form-label">Email</label>
                <input type="email" class="form-control" wire:model="email" placeholder="Enter Email" required>
                <div class="invalid-feedback">
                    Please Enter Email
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="username" class="form-label">Contact Number</label>
                <input type="text" class="form-control" wire:model="contact_number"
                    placeholder="Enter Contact Number" required>
                <div class="invalid-feedback">
                    Please Enter Contact Number
                </div>
            </div>
            <div class="col-md-6">
                <label for="username" class="form-label">Address</label>
                <input type="text" class="form-control" wire:model="address" placeholder="Enter Address" required>
                <div class="invalid-feedback">
                    Please Enter Address
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="userpassword" class="form-label">Password</label>
                <input type="password" class="form-control" wire:model="password" placeholder="Enter Password" required>
                <div class="invalid-feedback">
                    Please Enter Password
                </div>
            </div>
            <div class="col-md-6">
                <label for="userpassword" class="form-label">Password</label>
                <input type="password" class="form-control" wire:model="password_confirmation"
                    placeholder="Enter Confirm Password" required>
                <div class="invalid-feedback">
                    Please Enter Confirm Password
                </div>
            </div>
        </div><br>
        <div class="mb-4">
            <p class="mb-0">By registering you agree to Fixit Zed <a href="#" class="text-primary">Terms of
                    Use</a></p>
        </div>
        <div class="mb-3">
            <button class="btn btn-primary w-100 waves-effect waves-light"type="submit">{{ __('Create account') }}</button>
        </div>
    </form>

    <div class="mt-5 text-center">
        <p class="text-muted mb-0">Already have an account ? <a href="{{ route('login') }}"
                class="text-primary fw-semibold">
                Login </a> </p>
    </div>
</div>
