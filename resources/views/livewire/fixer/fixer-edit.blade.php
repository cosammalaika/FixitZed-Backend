<form wire:submit.prevent="submit">
    <div class="modal-body">
        <div class="row g-4">
            <div class="col-md-6">
                <label for="user_id" class="form-label fw-semibold">User</label>
                <select wire:model="user_id" id="user_id" class="form-control" disabled>
                    <option value="">-- Choose User --</option>
                    @foreach ($users as $user)
                        <option value="{{ (string) $user->id }}">
                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select wire:model="status" id="status" class="form-control" data-trigger required>
                    <option value="" disabled>-- Select Status --</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                @error('status')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <label for="bio" class="form-label fw-semibold">Bio</label>
            <textarea wire:model="bio" id="bio" rows="4" class="form-control" placeholder="Short description or background"></textarea>
            @error('bio')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="mt-4">
            <div class="d-flex align-items-center justify-content-between">
                <label for="selected_services" class="form-label fw-semibold mb-0">Services Skilled In</label>
                <small class="text-muted">Selected ({{ count($selected_services ?? []) }})</small>
            </div>
            <div class="mt-2">
                @php
                    $serviceMap = $services->keyBy('id');
                @endphp
                <div class="border rounded p-3" style="min-height: 120px; background: #fff;">
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($selected_services as $sid)
                            @php
                                $service = $serviceMap->get((int) $sid) ?? $serviceMap->get($sid);
                                $name = $service->name ?? 'Unknown';
                            @endphp
                            <span class="badge rounded-pill d-inline-flex align-items-center" style="background:#ff7f32; color:#fff; padding:6px 10px; font-size:0.82rem; gap:6px;">
                                <span>{{ $name }}</span>
                                <button type="button" class="btn btn-sm btn-link text-white p-0 m-0" style="line-height:1; font-size:0.9rem;" wire:click.prevent="removeService('{{ $sid }}')">
                                    &times;
                                </button>
                            </span>
                        @empty
                            <span class="text-muted">No services selected.</span>
                        @endforelse
                    </div>
                </div>
                <div class="mt-3 position-relative">
                    <button type="button" class="form-control text-start d-flex justify-content-between align-items-center" wire:click="toggleServiceDropdown">
                        <span>Select services</span>
                        <span class="badge bg-light text-muted">{{ count($services) }} total</span>
                    </button>
                    @if($showServiceDropdown)
                        <div class="border rounded shadow-sm bg-white mt-1 p-2" style="max-height: 260px; overflow-y: auto; position: absolute; width: 100%; z-index: 1050;">
                            <input type="text" class="form-control form-control-sm mb-2" placeholder="Search services…" oninput="filterServiceList(event)">
                            <div id="service-list" class="d-flex flex-column gap-1">
                                @foreach ($services as $service)
                                    <button type="button"
                                        class="btn btn-sm text-start {{ in_array((string) $service->id, $selected_services ?? []) ? 'btn-outline-primary' : 'btn-light' }}"
                                        data-name="{{ strtolower($service->name) }}"
                                        wire:click="toggleService('{{ $service->id }}')">
                                        {{ $service->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                @error('selected_services')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Update Fixer</span>
            <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </button>
    </div>
</form>

<script>
    function filterServiceList(event) {
        const term = event.target.value.toLowerCase();
        const list = document.getElementById('service-list');
        if (!list) return;
        Array.from(list.children).forEach(item => {
            const match = item.dataset.name?.includes(term);
            item.style.display = match ? '' : 'none';
        });
    }
</script>
