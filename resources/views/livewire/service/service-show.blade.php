<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Name:</strong>
                        <p>{{ $service->name }}</p>
                    </div>
    
                    <div class="mb-4">
                        <strong>Description:</strong>
                        <p>{{ $service->description ?? 'No description' }}</p>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Base Price:</strong>
                        <p>ZMW {{ number_format($service->price, 2) }}</p>
                    </div>
    
                    <div class="mb-4">
                        <h6 class="text-muted mb-1">Status</h6>
                        <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
