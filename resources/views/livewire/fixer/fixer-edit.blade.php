<form wire:submit.prevent="submit">
    <div class="modal-body">
        <div class="row g-4">
            <div class="col-md-6">
                <label for="user_id" class="form-label fw-semibold">User</label>
                <select wire:model="user_id" id="user_id" class="form-control" disabled>
                    <option value="">-- Choose User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">
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
                <input type="text" class="form-control mb-2" placeholder="Search services…" oninput="filterServices(event)">
                <select wire:model.defer="selected_services" id="selected_services" class="form-control" multiple size="8" style="min-height: 180px;">
                    @foreach ($services as $service)
                        <option value="{{ (string) $service->id }}">
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted d-block mt-1">Use Ctrl/Cmd + click to select multiple. Search filters visible options only.</small>
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
    function filterServices(event) {
        const term = event.target.value.toLowerCase();
        const select = document.getElementById('selected_services');
        if (!select) return;
        Array.from(select.options).forEach(option => {
            const match = option.text.toLowerCase().includes(term);
            option.style.display = match ? '' : 'none';
        });
    }
</script>
