@section('page-title', 'Create Fixer')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">

            <form wire:submit.prevent="submit" class="space-y-4">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="user_id">User</label>
                        <select wire:model="user_id" class="form-control" data-trigger required>
                            <option value="">-- Choose User --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-control" data-trigger>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('status')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                <div class="col-md-6">
                    <div>
                        <label class="form-label">Services Skilled In</label>
                        <select wire:model="selected_services" class="form-select" multiple size="8"
                            aria-describedby="services-help">
                            @foreach ($allServices as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                        <small id="services-help" class="text-muted">Hold Ctrl (Cmd on Mac) to select multiple
                            services.</small>
                    </div>
                </div>

                </div>

        </div>

        <div class="row">

            <div class="col-lg-12">
                <div class="mb-3">
                    <label for="progresspill-address-input">Bio</label>
                    <textarea id="progresspill-address-input" wire:model="bio" class="form-control" rows="2"></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary waves-effect waves-light">
            Create Fixer
        </button>
        </form>
    </div>
</div>
</div>
