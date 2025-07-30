<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <h4 class="">Fixer Profile</h4>
            <hr>
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1 fw-semibold text-muted">Name</p>
                    <h6>{{ $fixer->user->first_name }} {{ $fixer->user->last_name }}</h6>
                </div>

                <div class="col-md-6">
                    <p class="mb-1 fw-semibold text-muted">Email</p>
                    <h6>{{ $fixer->user->email }}</h6>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1 fw-semibold text-muted">Status</p>
                    <span
                        class="badge 
                    @if ($fixer->status == 'approved') bg-success 
                    @elseif($fixer->status == 'rejected') bg-danger 
                    @else bg-warning @endif px-3 py-1">
                        {{ ucfirst($fixer->status) }}
                    </span>
                </div>

                <div class="col-md-6">
                    <p class="mb-1 fw-semibold text-muted">Rating</p>
                    <h6>{{ number_format($fixer->rating_avg, 1) ?? 'N/A' }}/5</h6>
                </div>
            </div>

            <div class="mb-3">
                <p class="mb-1 fw-semibold text-muted">Bio</p>
                <p class="border rounded p-3 bg-light">
                    {{ $fixer->bio ?? 'No bio available.' }}
                </p>
            </div>

            <div>
                <p class="mb-1 fw-semibold text-muted">Joined On</p>
                <h6>{{ $fixer->created_at->format('M d, Y') }}</h6>
            </div>
        </div>
    </div>
</div>
