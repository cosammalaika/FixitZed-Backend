<div class="page-content">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Payment Methods</h4>
                    </div>
                    <div class="card-body">
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
                            <div class="col-md-6 d-flex align-items-center mt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="requiresIntegration" wire:model.defer="requires_integration">
                                    <label class="form-check-label" for="requiresIntegration">Requires external integration</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Integration note</label>
                                <textarea class="form-control" rows="1" wire:model.defer="integration_note"
                                    placeholder="Shown to the apps when integration is pending"></textarea>
                                @error('integration_note') <small class="text-danger">{{ $message }}</small> @enderror
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
                                        <th>Integration</th>
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
                                            <td>
                                                @if ($i->requires_integration)
                                                    <span class="badge bg-warning text-dark">Integration required</span>
                                                    @if ($i->integration_note)
                                                        <div class="small text-muted mt-1">{{ $i->integration_note }}</div>
                                                    @endif
                                                @else
                                                    <span class="badge bg-soft-success text-success">Ready</span>
                                                @endif
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
