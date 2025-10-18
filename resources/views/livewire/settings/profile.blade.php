@section('page-title', 'Profile')

<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public $photo = null;

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

    public function savePhoto(): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ], [
            'photo.image' => 'Please choose a valid image file (JPG, PNG, or GIF).',
            'photo.max' => 'Profile photos must be smaller than 2MB.',
        ]);

        /** @var UploadedFile $upload */
        $upload = $this->photo;

        if (! $upload instanceof UploadedFile) {
            return;
        }

        $user = Auth::user();
        $path = $upload->store('profile-photos', 'public');

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = $path;
        $user->save();

        $this->dispatch('profile-photo-updated');
        $this->photo = null;
    }

    public function removePhoto(): void
    {
        $user = Auth::user();
        if (! $user->profile_photo_path) {
            return;
        }

        Storage::disk('public')->delete($user->profile_photo_path);

        $user->profile_photo_path = null;
        $user->save();

        $this->dispatch('profile-photo-removed');
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

                                        @if ($this->photo)
                                            <img src="{{ $this->photo->temporaryUrl() }}" alt="Profile Photo Preview"
                                                class="rounded-circle"
                                                style="width:100%; height:100%; object-fit:cover;">
                                        @elseif ($photo)
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
                                        {{ $user->primary_role ?? 'Member' }}
                                    </div>
                                    <p class="mb-0 mt-2 text-white-50"><i
                                            class="bx bx-envelope me-1"></i>{{ $user->email }}</p>
                                    <div class="mt-3">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <label class="btn btn-outline-light btn-sm mb-0">
                                                <input type="file" class="d-none" accept="image/*" wire:model="photo">
                                                <i class="bx bx-camera me-1"></i> Change photo
                                            </label>
                                            @if ($photo && !$this->photo)
                                                <button type="button" class="btn btn-outline-light btn-sm"
                                                    wire:click="removePhoto"
                                                    wire:loading.attr="disabled"
                                                    wire:target="removePhoto">
                                                    <i class="bx bx-trash me-1"></i> Remove
                                                </button>
                                            @endif
                                            @if ($this->photo)
                                                <button type="button" class="btn btn-light btn-sm"
                                                    wire:click="savePhoto"
                                                    wire:loading.attr="disabled"
                                                    wire:target="savePhoto,photo">
                                                    <i class="bx bx-save me-1"></i> Save photo
                                                </button>
                                            @endif
                                        </div>
                                        <div class="mt-2">
                                            <div wire:loading wire:target="photo" class="text-white-50 small">
                                                Uploading...
                                            </div>
                                            @error('photo')
                                                <div class="text-warning small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        @if ($this->photo)
                                            <div class="mt-2">
                                                <span class="badge bg-light text-primary">Preview ready</span>
                                            </div>
                                        @endif
                                    </div>
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
*** End Patch
