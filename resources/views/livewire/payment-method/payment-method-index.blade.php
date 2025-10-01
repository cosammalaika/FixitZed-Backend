<div class="page-content">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Payment Methods</h4>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div id="payment-method-success"
                                class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>Success:</strong> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <script>
                                setTimeout(() => {
                                    const alert = document.getElementById('payment-method-success');
                                    if (alert) {
                                        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                        bsAlert.close();
                                    }
                                }, 3500);
                            </script>
                        @endif

                        <form wire:submit.prevent="add" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g., Cash">
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" wire:model.defer="code" placeholder="e.g., cash">
                                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" wire:model.defer="sort_order" value="0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100" type="submit">Add Method</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">#</th>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th style="width: 140px;">Active</th>
                                        <th style="width: 100px;">Sort</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $i)
                                        <tr>
                                            <td>{{ $i->id }}</td>
                                            <td class="fw-semibold">{{ $i->name }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $i->code }}</span></td>
                                            <td>
                                                <button type="button"
                                                    class="btn btn-sm {{ $i->active ? 'btn-success' : 'btn-outline-secondary' }}"
                                                    wire:click="toggle({{ $i->id }})">
                                                    {{ $i->active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </td>
                                            <td>{{ $i->sort_order }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                    wire:click="delete({{ $i->id }})"
                                                    wire:confirm="Delete this payment method?">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No payment methods yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
