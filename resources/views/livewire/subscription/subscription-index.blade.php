@section('page-title', 'Fixer Purchases')

<div>
  <div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Purchases</h4>
          </div>
          <div class="card-body">
            @if (session('status'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif
            @if ($errorMessage && ! $selectedId)
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $errorMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif
            @if (!empty($missing) && $missing)
              <div class="alert alert-warning">
                <strong>Subscriptions tables not found.</strong>
                Run migrations to create them:
                <code>php artisan migrate</code>
              </div>
            @endif
            <table class="table table-bordered dt-responsive nowrap w-100">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Fixer</th>
                  <th>Plan</th>
                  <th>Coins</th>
                  <th>Status</th>
                  <th>Reference</th>
                  <th>Starts</th>
                  <th>Expires</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($purchases as $i => $s)
                  <tr>
                    <td>{{ $purchases->firstItem() + $i }}</td>
                    @php
                      $fixerMissing = ! $s->fixer;
                      $userMissing = $s->fixer && ! $s->fixer->user;
                    @endphp
                    <td>
                      @if($fixerMissing)
                        <span class="text-muted">Deleted fixer</span>
                        <span class="badge bg-secondary ms-1">Missing fixer</span>
                      @elseif($userMissing)
                        <span class="text-muted">Deleted user</span>
                        <span class="badge bg-warning text-dark ms-1">No user</span>
                      @else
                        {{ $s->fixer?->user?->first_name ?? '—' }} {{ $s->fixer?->user?->last_name ?? '' }}
                      @endif
                    </td>
                    <td>{{ optional($s->plan)->name }}</td>
                    <td>{{ $s->coins_awarded }}</td>
                    <td>
                      <span class="badge bg-{{ $s->status === 'approved' ? 'success' : ($s->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td class="text-monospace">{{ $s->payment_reference }}</td>
                    <td>{{ $s->starts_at }}</td>
                    <td>{{ $s->expires_at ?? '—' }}</td>
                    <td>
                      <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                          Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" href="#" wire:click.prevent="showSubscription({{ $s->id }})">View</a>
                          </li>
                          <li>
                            <a class="dropdown-item" href="#" wire:click.prevent="editSubscription({{ $s->id }})">Edit</a>
                          </li>
                          <li>
                            <a class="dropdown-item @if($s->status === 'approved') disabled text-muted @endif" href="#"
                              @if($s->status !== 'approved') wire:click.prevent="confirmApproval({{ $s->id }})" @endif>
                              Approve &amp; Credit
                            </a>
                          </li>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <a class="dropdown-item text-danger" href="#" wire:click.prevent="confirmDelete({{ $s->id }})">Delete</a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            @if (empty($missing) || !$missing)
{{ $purchases->links() }}
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>

  @if ($showApproveModal)
  <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="approveSubscriptionModalLabel">Approve Subscription</h5>
        <button type="button" class="btn-close" aria-label="Close" wire:click="cancelApproval"></button>
      </div>
      <div class="modal-body">
        @if ($errorMessage)
          <div class="alert alert-danger" role="alert">{{ $errorMessage }}</div>
        @endif

        @if ($selectedId)
          <dl class="row mb-0">
            @if($selectedFixer)
              <dt class="col-sm-4">Fixer</dt>
              <dd class="col-sm-8">{{ $selectedFixer }}</dd>
            @endif

            @if($selectedPlan)
              <dt class="col-sm-4">Plan</dt>
              <dd class="col-sm-8">{{ $selectedPlan }}</dd>
            @endif

            @if($selectedAmount !== null)
              <dt class="col-sm-4">Amount Due</dt>
              <dd class="col-sm-8">K{{ number_format($selectedAmount, 2, '.', ',') }}</dd>
            @endif

            @if($selectedReference)
              <dt class="col-sm-4">Reference</dt>
              <dd class="col-sm-8"><code>{{ $selectedReference }}</code></dd>
            @endif

            @if($selectedMethod)
              <dt class="col-sm-4">Payment Method</dt>
              <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $selectedMethod)) }}</dd>
            @endif
          </dl>

          @php
            $instructionLines = collect(preg_split('/\r?\n/', (string) $selectedInstructions))
              ->map(fn ($line) => trim($line))
              ->filter();
          @endphp

          @if($instructionLines->isNotEmpty())
            <hr>
            <p class="fw-semibold mb-2">Manual payment instructions</p>
            <ul class="list-unstyled ms-2">
              @foreach($instructionLines as $line)
                <li class="mb-1">&bull; {{ $line }}</li>
              @endforeach
            </ul>
          @endif

          <p class="text-muted small mb-0">Confirm that the payment has been received. Coins will be credited immediately.</p>
        @else
          <p class="text-muted mb-0">Select a subscription to review and approve.</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" wire:click="cancelApproval" @if($processing) disabled @endif>Close</button>
        <button type="button" class="btn btn-primary" wire:click="approveSelected" @if($processing || ! $selectedId) disabled @endif>
          <span wire:loading wire:target="approveSelected" class="spinner-border spinner-border-sm me-2" role="status"></span>
          {{ $processing ? 'Processing…' : 'Confirm Approval' }}
        </button>
      </div>
      </div>
    </div>
  </div>
  @endif

  @if ($showViewModal)
  <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="viewSubscriptionModalLabel">Subscription Details</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="closeView"></button>
        </div>
        <div class="modal-body">
          @if ($showData)
            <dl class="row mb-0">
              @foreach ($showData as $label => $value)
                <dt class="col-sm-4">{{ $label }}</dt>
                <dd class="col-sm-8">{{ $value }}</dd>
              @endforeach
            </dl>

            @if (!empty($showInstructions))
              <hr>
              <p class="fw-semibold mb-2">Payment Instructions</p>
              <ul class="list-unstyled ms-2">
                @foreach ($showInstructions as $line)
                  <li class="mb-1">&bull; {{ $line }}</li>
                @endforeach
              </ul>
            @endif
          @else
            <p class="text-muted mb-0">Select a subscription to view its details.</p>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" wire:click="closeView">Close</button>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if ($showEditModal)
  <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSubscriptionModalLabel">Edit Subscription</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="cancelEditing"></button>
        </div>
        <form wire:submit.prevent="updateSubscription">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Payment reference</label>
                <input type="text" class="form-control @error('editData.payment_reference') is-invalid @enderror" wire:model.defer="editData.payment_reference">
                @error('editData.payment_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Amount (K)</label>
                <input type="number" step="0.01" min="0" class="form-control @error('editData.amount') is-invalid @enderror" wire:model.defer="editData.amount">
                @error('editData.amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select @error('editData.status') is-invalid @enderror" wire:model.defer="editData.status">
                  @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                  @endforeach
                </select>
                @error('editData.status')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Coins</label>
                <input type="number" min="0" class="form-control @error('editData.coins_awarded') is-invalid @enderror" wire:model.defer="editData.coins_awarded">
                @error('editData.coins_awarded')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Starts at</label>
                <input type="datetime-local" class="form-control @error('editData.starts_at') is-invalid @enderror" wire:model.defer="editData.starts_at">
                @error('editData.starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Expires at</label>
                <input type="datetime-local" class="form-control @error('editData.expires_at') is-invalid @enderror" wire:model.defer="editData.expires_at">
                @error('editData.expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <p class="text-muted small mt-3 mb-0">If the status is set to Approved and the subscription was previously pending, coins will be credited automatically.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" wire:click="cancelEditing">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <span wire:loading wire:target="updateSubscription" class="spinner-border spinner-border-sm me-2" role="status"></span>
              Save changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  @if ($showDeleteModal)
  <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteSubscriptionModalLabel">Delete Subscription</h5>
          <button type="button" class="btn-close" aria-label="Close" wire:click="cancelDelete"></button>
        </div>
        <div class="modal-body">
          @if ($deleteSummary)
            <p class="mb-0">Are you sure you want to delete <strong>{{ $deleteSummary }}</strong>? This action cannot be undone.</p>
          @else
            <p class="mb-0">Select a subscription to delete.</p>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" wire:click="cancelDelete">Cancel</button>
          <button type="button" class="btn btn-danger" wire:click="deleteSubscription">
            <span wire:loading wire:target="deleteSubscription" class="spinner-border spinner-border-sm me-2" role="status"></span>
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif

</div>

{{-- No JS hook needed; modals render conditionally via Livewire. --}}
