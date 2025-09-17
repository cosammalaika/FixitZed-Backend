@section('page-title', 'Profile')

<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->first_name . ' ' . $user->last_name);
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
};
?>


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

            @php
                $user = auth()->user();
                $serviceRequestBuilder =
                    $user && method_exists($user, 'serviceRequests') ? $user->serviceRequests() : null;
                $completedCount = $serviceRequestBuilder
                    ? (clone $serviceRequestBuilder)->where('status', 'completed')->count()
                    : 0;
                $activeCount = $serviceRequestBuilder
                    ? (clone $serviceRequestBuilder)->whereIn('status', ['pending', 'accepted', 'in_progress'])->count()
                    : 0;
                $recentRequests = $serviceRequestBuilder
                    ? (clone $serviceRequestBuilder)->latest()->take(5)->get()
                    : collect();
            @endphp

            <div class="row g-4">
                <div class="col-xxl-4">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="position-relative bg-primary bg-gradient text-white p-4">
                            <div class="position-absolute top-0 end-0 opacity-25 display-1">
                                <i class="bx bx-user-circle"></i>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span
                                        class="avatar avatar-xxl rounded-circle border border-white border-opacity-25 d-flex align-items-center justify-content-center overflow-hidden"
                                        style="width:100px; height:100px;">
                                        @php($photo = $user?->profile_photo_path)

                                        @if ($photo)
                                            <img src="{{ asset('storage/' . ltrim($photo, '/')) }}" alt="Profile Photo"
                                                class="rounded-circle"
                                                style="width:100%; height:100%; object-fit:cover;">
                                        @else
                                            <span class="fw-bold text-white"
                                                style="font-size:32px; background:#6c757d; width:100%; height:100%; display:flex; align-items:center; justify-content:center; border-radius:50%;">
                                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </span>
                                </div>


                                <div>
                                    <h3 class="fw-semibold mb-1 text-white">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                    <div class="badge bg-light text-primary fw-semibold">
                                        {{ $user->user_type ?? 'Member' }}
                                    </div>
                                    <p class="mb-0 mt-2 text-white-50"><i
                                            class="bx bx-envelope me-1"></i>{{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row text-center g-3 mb-4">
                                <div class="col-6">
                                    <div class="border rounded py-3 px-2">
                                        <h4 class="mb-0">{{ $completedCount }}</h4>
                                        <p class="text-muted mb-0 small">Completed Jobs</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded py-3 px-2">
                                        <h4 class="mb-0">{{ $activeCount }}</h4>
                                        <p class="text-muted mb-0 small">Active Pipeline</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-semibold text-uppercase text-muted mb-3">Contact Information</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-2"><i
                                            class="bx bx-phone me-2 text-primary"></i>{{ $user->contact_number ?? 'Not provided' }}
                                    </li>
                                    <li class="mb-2"><i
                                            class="bx bx-map me-2 text-primary"></i>{{ $user->address ?? 'Address not set' }}
                                    </li>
                                    <li class="mb-0"><i class="bx bx-time me-2 text-primary"></i>Joined
                                        {{ optional($user->created_at)->format('d M, Y') }}</li>
                                </ul>
                            </div>

                            <div class="bg-light rounded-3 p-3 border">
                                <h6 class="fw-semibold text-muted text-uppercase mb-3">Account Insights</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Account Status</span>
                                    <span class="fw-semibold text-success">{{ $user->status ?? 'Active' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Email Verified</span>
                                    <span
                                        class="fw-semibold {{ $user->email_verified_at ? 'text-success' : 'text-warning' }}">
                                        {{ $user->email_verified_at ? 'Yes' : 'Pending' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Preferred Language</span>
                                    <span
                                        class="fw-semibold">{{ strtoupper($user->preferred_locale ?? config('app.locale')) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-0">
                            <ul class="nav nav-pills nav-fill" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-details-tab" data-bs-toggle="pill"
                                        data-bs-target="#profile-details" type="button" role="tab">
                                        <i class="bx bx-user-circle me-1"></i> Profile Details
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="account-security-tab" data-bs-toggle="pill"
                                        data-bs-target="#account-security" type="button" role="tab">
                                        <i class="bx bx-lock-alt me-1"></i> Security
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="recent-activity-tab" data-bs-toggle="pill"
                                        data-bs-target="#recent-activity" type="button" role="tab">
                                        <i class="bx bx-time-five me-1"></i> Recent Activity
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content p-4">
                                <div class="tab-pane fade show active" id="profile-details" role="tabpanel"
                                    aria-labelledby="profile-details-tab">
                                    <form wire:submit="updateProfileInformation" class="needs-validation" novalidate>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <flux:input wire:model="first_name" :label="__('First Name')"
                                                    type="text" required autocomplete="given-name" />
                                            </div>
                                            <div class="col-md-6">
                                                <flux:input wire:model="last_name" :label="__('Last Name')"
                                                    type="text" required autocomplete="family-name" />
                                            </div>
                                            <div class="col-12">
                                                <flux:input wire:model="email" :label="__('Email Address')"
                                                    type="email" required autocomplete="email" />
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-3 mt-4">
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                <i class="bx bx-save me-1"></i> Save Changes
                                            </button>
                                            <x-action-message class="text-success" on="profile-updated">
                                                Profile updated successfully.
                                            </x-action-message>
                                        </div>

                                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                                            <div class="alert alert-warning mt-4 mb-0" role="alert">
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-error-circle fs-4 me-2"></i>
                                                    <div>
                                                        <p class="mb-1">
                                                            Your email address is unverified.</p>
                                                        <flux:link class="text-decoration-underline"
                                                            wire:click.prevent="resendVerificationNotification">
                                                            Click here to re-send the verification email.
                                                        </flux:link>
                                                    </div>
                                                </div>
                                                @if (session('status') === 'verification-link-sent')
                                                    <span class="d-block mt-2 text-success fw-semibold">
                                                        A new verification link has been sent to your email address.
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </form>
                                </div>

                                <div class="tab-pane fade" id="account-security" role="tabpanel"
                                    aria-labelledby="account-security-tab">
                                    <div class="border rounded p-4 bg-light">
                                        <h5 class="fw-semibold mb-3">Update Password</h5>
                                        <p class="text-muted mb-4">Keep your account secure by using a strong password
                                            and
                                            updating it regularly.</p>
                                        @livewire('settings.password')
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="recent-activity" role="tabpanel"
                                    aria-labelledby="recent-activity-tab">
                                    <h5 class="fw-semibold mb-3">Recent Service Requests</h5>
                                    <div class="table-responsive">
                                        <table class="table table-borderless align-middle mb-0">
                                            <thead class="text-muted">
                                                <tr>
                                                    <th scope="col">Service</th>
                                                    <th scope="col">Status</th>
                                                    <th scope="col" class="text-end">Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($recentRequests as $request)
                                                    <tr>
                                                        <td>{{ optional($request->service)->name ?? 'Service Request' }}
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge bg-light text-capitalize text-dark border">
                                                                {{ str_replace('_', ' ', $request->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end text-muted">
                                                            {{ optional($request->created_at)->format('d M Y, H:i') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">
                                                            No recent requests to display.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->

        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->


</section>
