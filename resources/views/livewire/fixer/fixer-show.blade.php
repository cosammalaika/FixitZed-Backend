<div class="container-fluid py-6">
    <div class="row justify-content-center">
        <div class="card p-4 shadow-sm w-100">
            <h4 class="mb-3">Fixer Profile</h4>
            <hr class="mb-4">

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <p class="mb-1 text-muted fw-semibold">Name</p>
                    <h6>{{ $fixer->user->first_name }} {{ $fixer->user->last_name }}</h6>
                </div>

                <div class="col-md-6">
                    <p class="mb-1 text-muted fw-semibold">Email</p>
                    <h6>{{ $fixer->user->email }}</h6>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <p class="mb-1 text-muted fw-semibold">Status</p>
                    <span
                        class="badge px-3 py-1
                        @if ($fixer->status == 'approved') bg-success
                        @elseif ($fixer->status == 'rejected') bg-danger
                        @else bg-warning @endif">
                        {{ ucfirst($fixer->status) }}
                    </span>
                </div>

                <div class="col-md-6">
                    <p class="mb-1 text-muted fw-semibold">Rating</p>
                    <h6>{{ number_format($fixer->rating_avg, 1) ?? 'N/A' }}/5</h6>
                </div>
            </div>

            <div class="mb-4">
                <p class="mb-1 text-muted fw-semibold">Bio</p>
                <div class="border rounded bg-light p-3">
                    {{ $fixer->bio ?? 'No bio available.' }}
                </div>
            </div>

            <div class="mb-2">
                <strong>Skilled Services:</strong>
                <ul>
                    @forelse($fixer->services as $service)
                        <li>{{ $service->name }}</li>
                    @empty
                        <li>No services listed.</li>
                    @endforelse
                </ul>
            </div>


            <div>
                <p class="mb-1 text-muted fw-semibold">Joined On</p>
                <h6>{{ $fixer->created_at->format('M d, Y') }}</h6>
            </div>
        </div>
    </div>
</div>
