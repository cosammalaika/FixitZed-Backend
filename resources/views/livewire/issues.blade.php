@section('page-title', 'Issues')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0 font-size-18">Issues</h4>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h4 class="card-title mb-1">Issue inbox</h4>
                                    <p class="text-muted mb-0">Submitted from customer and fixer apps.</p>
                                </div>
                                <div class="d-flex flex-wrap gap-2" style="min-width: 360px;">
                                    <input type="text" class="form-control" placeholder="Search subject/message"
                                        wire:model.debounce.400ms="search">
                                    <select class="form-select" wire:model="statusFilter">
                                        <option value="all">All status</option>
                                        <option value="open">Open</option>
                                        <option value="reviewed">Reviewed</option>
                                        <option value="action_taken">Action taken</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                    <select class="form-select" wire:model="typeFilter">
                                        <option value="all">All types</option>
                                        <option value="user">User</option>
                                        <option value="fixer">Fixer</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">#</th>
                                            <th>Subject</th>
                                            <th>Reporter</th>
                                            <th>Target</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th>Created</th>
                                            <th class="text-end">Manage</th>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle">
                                        @forelse ($reports as $report)
                                            <tr>
                                                <td class="fw-semibold text-muted">{{ $report->id }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $report->subject }}</div>
                                                    <div class="text-muted small text-truncate" style="max-width: 320px;">
                                                        {{ $report->message }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">
                                                        {{ $report->reporter?->first_name }} {{ $report->reporter?->last_name }}
                                                    </div>
                                                    <div class="text-muted small">{{ $report->reporter?->email ?? 'Unknown' }}</div>
                                                </td>
                                                <td>
                                                    @if ($report->target)
                                                        <div class="fw-semibold">
                                                            {{ $report->target?->first_name }} {{ $report->target?->last_name }}
                                                        </div>
                                                        <div class="text-muted small">{{ $report->target?->email }}</div>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusMap = [
                                                            'open' => 'warning',
                                                            'reviewed' => 'info',
                                                            'action_taken' => 'success',
                                                            'closed' => 'secondary',
                                                        ];
                                                        $status = $report->status ?? 'open';
                                                        $statusClass = $statusMap[$status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} px-2 py-1">
                                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @php
                                                        $actionMap = [
                                                            'none' => 'secondary',
                                                            'warn' => 'warning',
                                                            'suspend' => 'info',
                                                            'ban' => 'danger',
                                                        ];
                                                        $action = $report->action ?? 'none';
                                                        $actionClass = $actionMap[$action] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $actionClass }}-subtle text-{{ $actionClass }} px-2 py-1">
                                                        {{ ucfirst($action) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-muted small">
                                                        {{ $report->created_at?->format('Y-m-d H:i') }}
                                                    </div>
                                                    @if ($report->resolved_at)
                                                        <div class="text-success small">Resolved {{ $report->resolved_at->diffForHumans() }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Update
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><button class="dropdown-item" wire:click="markStatus({{ $report->id }}, 'open')">Mark Open</button></li>
                                                            <li><button class="dropdown-item" wire:click="markStatus({{ $report->id }}, 'reviewed')">Mark Reviewed</button></li>
                                                            <li><button class="dropdown-item" wire:click="markStatus({{ $report->id }}, 'action_taken')">Mark Action Taken</button></li>
                                                            <li><button class="dropdown-item" wire:click="markStatus({{ $report->id }}, 'closed')">Close</button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button class="dropdown-item text-warning" wire:click="takeAction({{ $report->id }}, 'warn')">Warn</button></li>
                                                            <li><button class="dropdown-item text-info" wire:click="takeAction({{ $report->id }}, 'suspend')">Suspend (7d)</button></li>
                                                            <li><button class="dropdown-item text-danger" wire:click="takeAction({{ $report->id }}, 'ban')">Ban</button></li>
                                                            <li><button class="dropdown-item" wire:click="takeAction({{ $report->id }}, 'none')">Clear action</button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">No issues yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                                <div class="text-muted small">
                                    Showing {{ $reports->firstItem() ?? 0 }} - {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }} issues
                                </div>
                                {{ $reports->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
