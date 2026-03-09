@section('page-title', 'Issues')

@php
    $visibleReports = $reports->getCollection();
    $visibleStatusCounts = [
        'open' => $visibleReports->where('status', 'open')->count(),
        'reviewed' => $visibleReports->where('status', 'reviewed')->count(),
        'action_taken' => $visibleReports->where('status', 'action_taken')->count(),
        'closed' => $visibleReports->where('status', 'closed')->count(),
    ];
@endphp

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-1 font-size-18">Issues</h4>
                            <p class="text-muted mb-0">Manage customer and fixer reports submitted from the apps.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Visible Issues</p>
                            <h4 class="mb-0">{{ $visibleReports->count() }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Open</p>
                            <h4 class="mb-0 text-warning">{{ $visibleStatusCounts['open'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Under Review</p>
                            <h4 class="mb-0 text-info">{{ $visibleStatusCounts['reviewed'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="text-muted text-uppercase small fw-semibold mb-1">Resolved/Actioned</p>
                            <h4 class="mb-0 text-success">{{ $visibleStatusCounts['action_taken'] + $visibleStatusCounts['closed'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="p-3 rounded border bg-light-subtle mb-3">
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-5">
                                        <label class="form-label text-muted small mb-1">Search</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            placeholder="Search subject or message"
                                            wire:model.debounce.400ms="search"
                                        >
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label text-muted small mb-1">Status</label>
                                        <select class="form-select" wire:model="statusFilter">
                                            <option value="all">All status</option>
                                            <option value="open">Open</option>
                                            <option value="reviewed">Reviewed</option>
                                            <option value="action_taken">Action taken</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <label class="form-label text-muted small mb-1">Type</label>
                                        <select class="form-select" wire:model="typeFilter">
                                            <option value="all">All types</option>
                                            <option value="user">User</option>
                                            <option value="fixer">Fixer</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 64px;">#</th>
                                            <th>Subject</th>
                                            <th>Reporter</th>
                                            <th>Target</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                            <th>Created</th>
                                            <th class="text-end" style="width: 110px;">Manage</th>
                                        </tr>
                                    </thead>
                                    <tbody class="align-middle">
                                        @forelse ($reports as $report)
                                            @php
                                                $reporterName = trim(($report->reporter?->first_name ?? '') . ' ' . ($report->reporter?->last_name ?? ''));
                                                $targetName = trim(($report->target?->first_name ?? '') . ' ' . ($report->target?->last_name ?? ''));

                                                $statusMap = [
                                                    'open' => 'warning',
                                                    'reviewed' => 'info',
                                                    'action_taken' => 'success',
                                                    'closed' => 'secondary',
                                                ];
                                                $status = $report->status ?? 'open';
                                                $statusClass = $statusMap[$status] ?? 'secondary';

                                                $actionMap = [
                                                    'none' => 'secondary',
                                                    'warn' => 'warning',
                                                    'suspend' => 'info',
                                                    'ban' => 'danger',
                                                ];
                                                $action = $report->action ?? 'none';
                                                $actionClass = $actionMap[$action] ?? 'secondary';
                                            @endphp

                                            <tr>
                                                <td class="fw-semibold text-muted">{{ $report->id }}</td>
                                                <td>
                                                    <div class="fw-semibold">{{ $report->subject }}</div>
                                                    <div class="text-muted small text-truncate" style="max-width: 360px;">
                                                        {{ $report->message }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $reporterName !== '' ? $reporterName : 'Unknown User' }}</div>
                                                    <div class="text-muted small">{{ $report->reporter?->email ?? 'N/A' }}</div>
                                                </td>
                                                <td>
                                                    @if ($report->target)
                                                        <div class="fw-semibold">{{ $targetName !== '' ? $targetName : 'Unknown User' }}</div>
                                                        <div class="text-muted small">{{ $report->target?->email ?? 'N/A' }}</div>
                                                    @else
                                                        <span class="badge badge-soft-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-{{ $statusClass }} text-{{ $statusClass }} px-2 py-1">
                                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-{{ $actionClass }} text-{{ $actionClass }} px-2 py-1">
                                                        {{ ucfirst($action) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-muted small">{{ $report->created_at?->format('Y-m-d H:i') }}</div>
                                                    @if ($report->resolved_at)
                                                        <div class="text-success small">Resolved {{ $report->resolved_at->diffForHumans() }}</div>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            Actions
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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
                                                <td colspan="8" class="py-5">
                                                    <div class="d-flex flex-column align-items-center justify-content-center text-center text-muted">
                                                        <span class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center mb-3">
                                                            <i class="bx bx-message-rounded-x font-size-20"></i>
                                                        </span>
                                                        <h5 class="mb-1 text-dark">No issues yet</h5>
                                                        <p class="mb-0">Reports from customer and fixer apps will appear here.</p>
                                                    </div>
                                                </td>
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
