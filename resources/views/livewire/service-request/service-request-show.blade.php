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

                @if (in_array(strtolower((string) $serviceRequest->status), ['cancelled', 'canceled'], true))
                    <div class="col-12">
                        <div class="border rounded-3 p-3 bg-light">
                            <h5 class="mb-3">Cancellation details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Canceled by</strong>
                                    <p class="mb-0">
                                        {{ $serviceRequest->canceled_by ? ucfirst(str_replace('_', ' ', $serviceRequest->canceled_by)) : '—' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Cancellation reason</strong>
                                    <p class="mb-0">{{ $serviceRequest->cancellation_reason_label ?? '—' }}</p>
                                </div>
                                @if ($serviceRequest->cancellation_note)
                                    <div class="col-md-6 mb-3">
                                        <strong>Additional note</strong>
                                        <p class="mb-0">{{ $serviceRequest->cancellation_note }}</p>
                                    </div>
                                @endif
                                <div class="col-md-6 mb-3">
                                    <strong>Canceled at</strong>
                                    <p class="mb-0">{{ optional($serviceRequest->canceled_at)->toDayDateTimeString() ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
