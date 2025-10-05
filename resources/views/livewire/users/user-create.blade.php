<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit" enctype="multipart/form-data">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">First Name</label>
                        <input class="form-control" type="text" wire:model="first_name" placeholder="First Name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Last Name</label>
                        <input class="form-control" type="text" wire:model="last_name" placeholder="Last Name" required>
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Email</label>
                        <input class="form-control" type="email" wire:model="email" placeholder="Email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Username</label>
                        <input class="form-control" type="text" wire:model="username" placeholder="Username" required>
                    </div>

                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Contact Number</label>
                        <input class="form-control" type="text" wire:model="contact_number" placeholder="Contact Number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="location_option_id">Location</label>
                        <select class="form-control" wire:model="location_option_id" required>
                            <option value="">-- Select Location --</option>
                            @foreach ($locationOptions as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                            @endforeach
                        </select>
                        @error('location_option_id')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Password</label>
                        <input class="form-control" type="password" wire:model="password" placeholder="Password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Confirm Password</label>
                        <input class="form-control" type="password" wire:model="confirm_password" placeholder="Confirm Password" required>
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-4">
                        <label class="form-label">Profile Photo (optional)</label>
                        <input class="form-control" type="file" wire:model="photo" accept="image/*">
                        @error('photo') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NRC Front (optional)</label>
                        <input class="form-control" type="file" wire:model="nrc_front" accept="image/*">
                        @error('nrc_front') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NRC Back (optional)</label>
                        <input class="form-control" type="file" wire:model="nrc_back" accept="image/*">
                        @error('nrc_back') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-12">
                        <label class="form-label">Supporting Documents (optional)</label>
                        <input class="form-control" type="file" wire:model="documents" multiple accept=".pdf,image/*">
                        <small class="text-muted">You may upload PDFs or images. Max 5MB each.</small>
                        @error('documents.*') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select wire:model="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                @if ($canAssignRoles)
                    <div class="row mt-4">
                        <div class="col-12">
                            <label class="form-label">Roles</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($allRoles as $role)
                                    <div class="form-check form-switch mb-1">
                                        <input type="checkbox" class="form-check-input"
                                            id="perm-{{ $role->id }}" value="{{ $role->name }}"
                                            wire:model="roles">
                                        <label class="form-check-label" for="perm-{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-4">
                        <span class="badge bg-light text-muted">Role: Customer (default)</span>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary waves-effect waves-light">
                    Submit
                </button>
            </form>
        </div>
    </div>

</div>
