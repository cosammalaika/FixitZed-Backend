<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <h4 class="">Service Request Details</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Customer</strong>
                        <p>{{ $serviceRequest->customer?->first_name ?? 'Deleted user' }} {{ $serviceRequest->customer?->last_name ?? '' }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Fixer</strong>
                        <p>
                            @if ($serviceRequest->fixer?->user)
                                {{ $serviceRequest->fixer->user->first_name }} {{ $serviceRequest->fixer->user->last_name }}
                            @elseif ($serviceRequest->fixer)
                                Deleted user
                            @else
                                Unassigned
                            @endif
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Service</strong>
                        <p>{{ $serviceRequest->service?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Scheduled At</strong>
                        <p>{{ optional($serviceRequest->scheduled_at)->toDayDateTimeString() ?? 'Not scheduled' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Status</strong>
                        <p>{{ ucfirst($serviceRequest->status) }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Location</strong>
                        <p>{{ $serviceRequest->location ?? 'Not specified' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
