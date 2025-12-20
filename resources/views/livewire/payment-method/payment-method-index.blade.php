<div class="page-content">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Payment Methods</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                            Add Method
                        </button>
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
                                                    data-confirm-event="deletePaymentMethod"
                                                    data-confirm-id="{{ $i->id }}"
                                                    data-confirm-title="Delete payment method?"
                                                    data-confirm-message="This payment method will be removed permanently."
                                                    data-confirm-button="Yes, delete it">
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

    <!-- Add Payment Method Modal -->
    <div wire:ignore.self class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentMethodModalLabel">Add Payment Method</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="add">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g., Cash">
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Code</label>
                                <input type="text" class="form-control" wire:model.defer="code" placeholder="e.g., cash">
                                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sort Order</label>
                                <input type="number" class="form-control" wire:model.defer="sort_order" value="0">
                            </div>
                            <div class="col-md-8 d-flex align-items-center pt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="requiresIntegration" wire:model.defer="requires_integration">
                                    <label class="form-check-label" for="requiresIntegration">Requires external integration</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Integration note</label>
                                <textarea class="form-control" rows="2" wire:model.defer="integration_note" placeholder="Shown to the apps when integration is pending"></textarea>
                                @error('integration_note') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Add Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        window.addEventListener('close-add-payment-modal', () => {
            const modalEl = document.getElementById('addPaymentMethodModal');
            if (!modalEl) return;
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
        });
    });
</script>
