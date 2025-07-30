<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <h4>Payment Details</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Service Request ID</strong>
                        <p> {{ $payment->service_request_id }}:
                            <span class="text-muted">{{ $payment->serviceRequest->service->name ?? 'N/A' }}</span>
                        </p>
                    </div>
                    <div class="mb-4">
                        <strong>Amount</strong>
                        <p>{{ number_format($payment->amount, 2) }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Status</strong>
                        <p><span
                                class="
                        @if ($payment->status == 'accepted') badge rounded-pill badge-soft-success
                        @elseif($payment->status == 'completed') badge rounded-pill badge-soft-primary
                        @elseif($payment->status == 'in_progress') badge rounded-pill badge-soft-info
                        @elseif($payment->status == 'cancelled') badge rounded-pill badge-soft-danger
                        @else badge rounded-pill badge-soft-warning @endif">{{ ucfirst($payment->status) }}</span>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="mb-4">
                        <strong>Payment Method</strong>
                        <p>{{ $payment->payment_method ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Transaction ID</strong>
                        <p>{{ $payment->transaction_id ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Paid At</strong>
                        <p>{{ $payment->paid_at ?? 'Not Paid' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
