@php
    use Illuminate\Support\Facades\Storage;
    $user = $fixer->user;
    $workPhotos = (array) ($user->work_photos ?? []);
    $supporting = (array) ($user->documents ?? []);
    $resolveUrl = function (?string $path) {
        if (! $path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return route('files.show', ['path' => $path]);
    };
@endphp

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="card border-0 shadow-sm w-100">
            <div class="p-4 pb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1 fw-bold">Fixer Application</h4>
                    <span
                        class="badge px-3 py-1 text-uppercase"
                        style="letter-spacing:0.02em; background:#FFF4E5; color:#B26A00;">
                        {{ ucfirst($fixer->status) }}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success px-3" wire:click="approve">Approve</button>
                    <button class="btn btn-outline-danger px-3" wire:click="reject">Reject</button>
                </div>
            </div>
            <div class="px-4">
                <hr class="my-2">
            </div>

            <div class="px-4 pb-4">
                <div class="row g-4 mb-3">
                    <div class="col-md-4">
                        <p class="mb-1 text-muted fw-semibold">Name</p>
                        <h6 class="mb-0 fw-bold">{{ $user->first_name }} {{ $user->last_name }}</h6>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted fw-semibold">Email</p>
                        <h6 class="mb-0">{{ $user->email }}</h6>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted fw-semibold">Contact</p>
                        <h6 class="mb-0">{{ $user->contact_number ?? 'N/A' }}</h6>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="mb-2 text-muted fw-semibold">Profile Photo</p>
                            @php $profileUrl = $resolveUrl($user->profile_photo_path); @endphp
                            @if ($profileUrl)
                                <a href="{{ $profileUrl }}" target="_blank">
                                    <img src="{{ $profileUrl }}" alt="Profile photo" class="img-fluid rounded border" style="max-height: 180px; object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted">Not provided</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="mb-2 text-muted fw-semibold">NRC Front</p>
                            @php $frontUrl = $resolveUrl($user->nrc_front_path); @endphp
                            @if ($frontUrl)
                                <a href="{{ $frontUrl }}" target="_blank" class="btn btn-link p-0">View</a>
                            @else
                                <span class="text-muted">Not uploaded</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-3 h-100 bg-light-subtle">
                            <p class="mb-2 text-muted fw-semibold">NRC Back</p>
                            @php $backUrl = $resolveUrl($user->nrc_back_path); @endphp
                            @if ($backUrl)
                                <a href="{{ $backUrl }}" target="_blank" class="btn btn-link p-0">View</a>
                            @else
                                <span class="text-muted">Not uploaded</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="mb-0 text-muted fw-semibold">Work Photos (3)</p>
                        <span class="badge bg-light text-dark">{{ count($workPhotos) }}/3</span>
                    </div>
                    @if (!empty($workPhotos))
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($workPhotos as $path)
                                @php $url = $resolveUrl($path); @endphp
                                @if ($url)
                                    <a href="{{ $url }}" target="_blank" class="shadow-sm rounded border overflow-hidden" style="width: 120px; height: 120px;">
                                        <img src="{{ $url }}" class="w-100 h-100" style="object-fit: cover;" alt="Work photo">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">No work photos uploaded.</span>
                    @endif
                </div>

                <div class="mb-4">
                    <p class="mb-2 text-muted fw-semibold">Supporting Documents</p>
                    @if (!empty($supporting))
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($supporting as $index => $path)
                                @php
                                    $url = $resolveUrl($path);
                                    $name = basename($path);
                                @endphp
                                @if ($url)
                                    <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                        {{ $name ?: 'Document ' . ($index + 1) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span class="text-muted">No supporting documents.</span>
                    @endif
                </div>

                <div class="mb-4">
                    <p class="mb-1 text-muted fw-semibold">Bio</p>
                    <div class="border rounded bg-light p-3">
                        {{ $fixer->bio ?? 'No bio available.' }}
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Skilled Services:</strong>
                    <ul class="mb-1">
                        @forelse($fixer->services as $service)
                            <li>{{ $service->name }}</li>
                        @empty
                            <li class="text-muted">No services listed.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <p class="mb-1 text-muted fw-semibold">Applied</p>
                        <h6>{{ $fixer->created_at->format('M d, Y') }}</h6>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted fw-semibold">Accepted Terms</p>
                        <h6>{{ $fixer->accepted_terms_at ? $fixer->accepted_terms_at->format('M d, Y H:i') : 'Pending' }}</h6>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-muted fw-semibold">Rating</p>
                        <h6>{{ number_format($fixer->rating_avg, 1) ?? 'N/A' }}/5</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
