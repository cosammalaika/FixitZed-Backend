<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 mb-4 align-items-end">
                <div>
                    <label class="form-label text-muted small mb-1">Search</label>
                    <input type="search" class="form-control" wire:model.debounce.500ms="search"
                        placeholder="Email, username, IP, user agent">
                </div>
                <div>
                    <label class="form-label text-muted small mb-1">Status</label>
                    <select class="form-select" wire:model="status">
                        <option value="all">All</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-muted small mb-1">Event</label>
                    <select class="form-select" wire:model="event">
                        <option value="all">All</option>
                        @foreach ($events as $item)
                            <option value="{{ $item }}">{{ ucfirst(str_replace('.', ' ', $item)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>IP</th>
                            <th>User Agent</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($audits as $audit)
                            <tr>
                                <td>{{ $audit->id }}</td>
                                <td>
                                    @if ($audit->user)
                                        <div class="fw-semibold">{{ $audit->user->email }}</div>
                                        <div class="text-muted small">
                                            {{ $audit->user->first_name }} {{ $audit->user->last_name }}
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Unknown user</span>
                                    @endif
                                </td>
                                <td>{{ ucfirst(str_replace('.', ' ', $audit->event)) }}</td>
                                <td>
                                    <span class="badge {{ $audit->status === 'success' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($audit->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $audit->ip_address ?? '—' }}</div>
                                    @if (! empty($audit->metadata['identifier']))
                                        <div class="text-muted small">{{ $audit->metadata['identifier'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small">
                                        {{ \Illuminate\Support\Str::limit($audit->user_agent, 60) ?: '—' }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $audit->created_at->diffForHumans() }}</div>
                                    <div class="text-muted small">{{ $audit->created_at->toDayDateTimeString() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No audit entries yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $audits->links() }}
            </div>
        </div>
    </div>
</div>
