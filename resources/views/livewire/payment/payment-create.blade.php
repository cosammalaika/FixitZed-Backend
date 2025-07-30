<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <div class="row">
                    <div class="col-md-6">
                        <label>Service Request</label>
                        <select wire:model="service_request_id" class="form-control">
                            <option value="">-- Select Request --</option>
                            @foreach ($serviceRequests as $request)
                                <option value="{{ $request->id }}">
                                    {{ $request->service->name ?? 'Service' }}
                                    - Request #{{ $request->id }}
                                    @if ($request->customer)
                                        ({{ $request->customer->name ?? $request->customer->email }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Amount</label>
                        <input type="number" step="0.01" wire:model="amount" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label>Status</label>
                        <select wire:model="status" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Payment Method</label>
                        <select wire:model="payment_method" class="form-control">
                            <option value="Cash">Cash</option>
                            <option value="Airtel Money">Airtel Money</option>
                            <option value="MTN Money">MTN Money</option>
                            <option value="Zamtel Money">Zamtel Money</option>
                            <option value="Bank">Bank</option>
                        </select>
                    </div>
                </div>
                <br>
                <button class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>
