<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
             <h4 class="">Service Info</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Name:</strong>
                        <p>{{ $service->name }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Category:</strong>
                        <p>{{ $service->category }}</p>
                    </div>
    
                    <div class="mb-4">
                        <strong>Description:</strong>
                        <p>{{ $service->description ?? 'No description' }}</p>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="mb-4">
                        <h6 class="text-muted mb-1">Status</h6>
                        <span class="badge {{ $service->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($service->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
