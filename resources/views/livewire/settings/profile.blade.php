<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    <div class="page-content">
        <div class="container-fluid">

            <!-- start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Profile</h4>

                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Contacts</a></li>
                                <li class="breadcrumb-item active">Profile</li>
                            </ol>
                        </div>

                    </div>
                </div>
            </div>
            <!-- end page title -->

            <div class="row">
                <div class="col-xl-12 col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm order-2 order-sm-1">
                                    <div class="d-flex align-items-start mt-3 mt-sm-0">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xl me-3">
                                                <img src="assets/images/users/avatar-2.jpg" alt=""
                                                    class="img-fluid rounded-circle d-block">
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div>
                                                <h5 class="font-size-16 mb-1">{{ auth()->user()->name }}</h5>
                                                <p class="text-muted font-size-13">{{ auth()->user()->email }}</p>

                                                <div
                                                    class="d-flex flex-wrap align-items-start gap-2 gap-lg-3 text-muted font-size-13">
                                                    <div><i
                                                            class="mdi mdi-circle-medium me-1 text-success align-middle"></i>Development
                                                    </div>
                                                    <div><i
                                                            class="mdi mdi-circle-medium me-1 text-success align-middle"></i>phyllisgatlin@minia.com
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-auto order-1 order-sm-2">
                                    <div class="d-flex align-items-start justify-content-end gap-2">
                                        {{-- <div>
                                            <button type="button" class="btn btn-soft-light"><i class="me-1"></i>
                                                Message</button>
                                        </div>
                                        <div>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-link font-size-16 shadow-none text-muted dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Action</a></li>
                                                    <li><a class="dropdown-item" href="#">Another action</a></li>
                                                    <li><a class="dropdown-item" href="#">Something else here</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>

                            <ul class="nav nav-tabs-custom card-header-tabs border-top mt-4" id="pills-tab"
                                role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link px-3 active" data-bs-toggle="tab" href="#overview"
                                        role="tab">Overview</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link px-3" data-bs-toggle="tab" href="#about"
                                        role="tab">Password</a>
                                </li>
                            </ul>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <div class="tab-content">
                        <div class="tab-pane active" id="overview" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Profile</h5>
                                </div>
                                <div class="card-body">
                                    <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
                                        <flux:input wire:model="name" :label="__('Name')" type="text" required
                                            autofocus autocomplete="name" />

                                        <div>
                                            <flux:input wire:model="email" :label="__('Email')" type="email"
                                                required autocomplete="email" />

                                            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                                                <div>
                                                    <flux:text class="mt-4">
                                                        {{ __('Your email address is unverified.') }}

                                                        <flux:link class="text-sm cursor-pointer"
                                                            wire:click.prevent="resendVerificationNotification">
                                                            {{ __('Click here to re-send the verification email.') }}
                                                        </flux:link>
                                                    </flux:text>

                                                    @if (session('status') === 'verification-link-sent')
                                                        <flux:text
                                                            class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                                            {{ __('A new verification link has been sent to your email address.') }}
                                                        </flux:text>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center justify-end">
                                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                    Save
                                                </button>

                                            </div>

                                            <x-action-message class="me-3" on="profile-updated">
                                                {{ __('Saved.') }}
                                            </x-action-message>
                                        </div>
                                    </form>

                                    {{-- <livewire:settings.delete-user-form /> --}}

                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end tab pane -->

                        <div class="tab-pane" id="about" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">About</h5>
                                </div>
                                <div class="card-body">
                                    <div>
                                        @livewire('settings.password')
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end tab pane -->
                    </div>
                    <!-- end tab content -->
                </div>
                <!-- end col -->


                <!-- end col -->
            </div>
            <!-- end row -->

        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


</section>
