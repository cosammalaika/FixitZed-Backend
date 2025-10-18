<div class="container-fluid py-6">
    <div class="row justify-content-center">
        <div class="card p-4 shadow-sm w-100">
            <form wire:submit.prevent="submit" class="space-y-4">
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

                <div class="row mt-4">
                    <div class="col-12">
                        <label for="bio" class="form-label fw-semibold">Bio</label>
                        <textarea wire:model="bio" id="bio" rows="3" class="form-control"
                            placeholder="Short description or background"></textarea>
                        @error('bio')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <label for="selected_services" class="form-label fw-semibold">Services Skilled In</label>
                    <select wire:model="selected_services" id="selected_services" class="form-multi-select" multiple data-trigger>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected(in_array($service->id, $selected_services ?? []))>{{ $service->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple.</small>
                    @error('selected_services')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        Update Fixer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
