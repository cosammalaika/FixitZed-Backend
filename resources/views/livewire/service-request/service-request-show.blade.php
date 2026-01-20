<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <h4 class="">Service Request Details</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Customer</strong>
                        @php($customer = optional($serviceRequest->customer))
                        <p>{{ $customer->first_name ?? 'Deleted user' }} {{ $customer->last_name ?? '' }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Fixer</strong>
                        @php
                            $fixerUser = optional(optional($serviceRequest->fixer)->user);
                        @endphp
                        <p>{{ $fixerUser->first_name ?? 'Deleted fixer' }} {{ $fixerUser->last_name ?? '' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Service</strong>
                        <p> {{ $serviceRequest->service->name ?? 'N/A' }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Scheduled At</strong>
                        <p> {{ \Carbon\Carbon::parse($serviceRequest->scheduled_at)->toDayDateTimeString() }}</p>
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
