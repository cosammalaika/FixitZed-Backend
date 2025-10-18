<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">First Name</label>
                        <input class="form-control" type="text" wire:model="first_name" placeholder="First Name"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Last Name</label>
                        <input class="form-control" type="text" wire:model="last_name" placeholder="Last Name"
                            required>
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Username</label>
                        <input class="form-control" type="username" wire:model="username" placeholder="Username"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Email</label>
                        <input class="form-control" type="email" wire:model="email" placeholder="Email" required>
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Contact Number</label>
                        <input class="form-control" type="text" wire:model="contact_number"
                            placeholder="Contact Number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="province">Province</label>
                        <select class="form-control" id="province" wire:model="province" required>
                            <option value="">-- Select Province --</option>
                            @foreach ($provinceOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('province')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="district">Area / District</label>
                        <select class="form-control" id="district" wire:model="district" required @disabled(empty($districtOptions))>
                            <option value="">-- Select Area --</option>
                            @foreach ($districtOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @if (empty($districtOptions) && $province)
                            <span class="text-muted small">No districts found for the selected province. Please try again later.</span>
                        @endif
                        @error('district')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select"  wire:model.defer="status">
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mt-6">
                    <div class="col-md-4">
                        <label class="form-label">Profile Photo</label>
                        <div class="mb-2">
                            @if ($user->profile_photo_path)
                                <img src="{{ Storage::disk('public')->url($user->profile_photo_path) }}" alt="Profile Photo" class="img-thumbnail" style="max-height: 120px;">
                            @else
                                <span class="text-muted">No photo</span>
                            @endif
                        </div>
                        <input class="form-control" type="file" wire:model="photo" accept="image/*">
                        @error('photo') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NRC Front</label>
                        <div class="mb-2">
                            @if ($user->nrc_front_path)
                                <img src="{{ Storage::disk('public')->url($user->nrc_front_path) }}" alt="NRC Front" class="img-thumbnail" style="max-height: 120px;">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </div>
                        <input class="form-control" type="file" wire:model="nrc_front" accept="image/*">
                        @error('nrc_front') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NRC Back</label>
                        <div class="mb-2">
                            @if ($user->nrc_back_path)
                                <img src="{{ Storage::disk('public')->url($user->nrc_back_path) }}" alt="NRC Back" class="img-thumbnail" style="max-height: 120px;">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </div>
                        <input class="form-control" type="file" wire:model="nrc_back" accept="image/*">
                        @error('nrc_back') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-12">
                        <label class="form-label">Supporting Documents</label>
                        <div class="mb-2">
                            @php $docs = is_array($user->documents) ? $user->documents : []; @endphp
                            @if (count($docs))
                                <ul class="mb-2">
                                    @foreach ($docs as $path)
                                        <li><a href="{{ Storage::disk('public')->url($path) }}" target="_blank">{{ basename($path) }}</a></li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted">No documents</span>
                            @endif
                        </div>
                        <input class="form-control" type="file" wire:model="documents" multiple accept=".pdf,image/*">
                        <small class="text-muted">Upload additional PDFs or images. Max 5MB each.</small>
                        @error('documents.*') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <label class="form-label">Roles</label>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($allRoles as $role)
                                <div class="form-check form-switch mb-1">
                                    <input type="checkbox" class="form-check-input" id="perm-{{ $role->id }}"
                                        value="{{ $role->name }}" wire:model="roles">
                                    <label class="form-check-label" for="perm-{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary waves-effect waves-light">
                    Update
                </button>
            </form>
        </div>
    </div>

</div>
