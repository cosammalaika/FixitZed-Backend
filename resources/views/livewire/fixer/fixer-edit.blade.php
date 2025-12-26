<style>
    .fixer-modal {
        max-width: 960px;
        margin: 0 auto;
    }
    .fixer-modal .card {
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.08);
    }
    .fixer-modal .card-header {
        background: linear-gradient(90deg, rgba(255, 147, 64, 0.08), rgba(255, 147, 64, 0.02));
        border-bottom: 1px solid #f3f3f3;
        padding: 18px 22px;
    }
    .fixer-modal .card-body {
        max-height: 70vh;
        overflow-y: auto;
        padding: 22px;
    }
    .fixer-modal label.form-label {
        font-weight: 600;
        font-size: 0.92rem;
        color: #2f2f2f;
    }
    .fixer-modal .form-control:focus,
    .fixer-modal .form-select:focus {
        box-shadow: 0 0 0 2px rgba(255, 147, 64, 0.25);
        border-color: #ff9340;
    }
    .fixer-modal .chips-area {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        max-height: 160px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        background: #fff;
    }
    .fixer-modal .chip {
        background: #fff7f0;
        border: 1px solid #ffd8b3;
        color: #8a4200;
        border-radius: 999px;
        padding: 4px 10px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }
    .fixer-modal .chip button {
        background: transparent;
        border: none;
        color: inherit;
        padding: 0;
        line-height: 1;
    }
    .fixer-modal .services-select-wrap {
        border: 1px solid #e6e6e6;
        border-radius: 10px;
        padding: 12px;
        background: #fff;
    }
    .fixer-modal .services-select-wrap .form-control {
        border: 1px solid #e0e0e0;
    }
    .fixer-modal .card-footer {
        border-top: 1px solid #f1f1f1;
        background: #fff;
        padding: 14px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        position: sticky;
        bottom: 0;
    }
</style>

<div class="container-fluid py-4 fixer-modal">
    <div class="row justify-content-center">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-semibold">Edit Fixer</h5>
                    <small class="text-muted">Update status, bio, and services</small>
                </div>
                <a href="{{ route('fixer.index') }}" class="btn btn-sm btn-outline-secondary">Close</a>
            </div>

            <form wire:submit.prevent="submit">
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">User</label>
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
                            <label for="status" class="form-label">Status</label>
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
                        <label for="bio" class="form-label">Bio</label>
                        <textarea wire:model="bio" id="bio" rows="4" class="form-control" placeholder="Short description or background"></textarea>
                        @error('bio')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <label for="selected_services" class="form-label mb-0">Services Skilled In</label>
                            <small class="text-muted">Selected ({{ count($selected_services ?? []) }})</small>
                        </div>
                        <div class="services-select-wrap mt-2">
                            <input type="text" class="form-control mb-2" placeholder="Search services…" oninput="filterServices(event)">
                            <select wire:model.defer="selected_services" id="selected_services" class="form-control" multiple size="8" style="min-height: 180px;">
                                @foreach ($services as $service)
                                    <option value="{{ (string) $service->id }}" @selected(in_array((string) $service->id, $selected_services ?? []))>
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

                <div class="card-footer">
                    <a href="{{ route('fixer.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Update Fixer</span>
                        <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
