<div>
    <h4 class="mb-4">Earning Details</h4>

    <div class="mb-3">
        <strong>Fixer:</strong>
        <p>{{ $earning->fixer->user->first_name ?? 'N/A' }}</p>

    </div>

    <div class="mb-3">
        <strong>Service Request:</strong>
        <p>#{{ $earning->service_count }}</p>
    </div>

    <div class="mb-3">
        <strong>Amount:</strong>
        <p>ZMW {{ number_format($earning->amount, 2) }}</p>
    </div>

    <div class="mb-3">
        <strong>Created At:</strong>
        <p>{{ $earning->created_at}}</p>
    </div>
</div>
