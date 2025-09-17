<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <h4 class="">User Profile</h4>
            <hr>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <div class="form-control-plaintext">{{ $user->first_name }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <div class="form-control-plaintext">{{ $user->last_name }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username</label>
                    <div class="form-control-plaintext">{{ $user->username }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <div class="form-control-plaintext">{{ $user->email }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number</label>
                    <div class="form-control-plaintext">{{ $user->contact_number }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address</label>
                    <div class="form-control-plaintext">{{ $user->address }}</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label><br>
                    @if ($user->status === 'Active')
                        <span class="badge rounded-pill badge-soft-success">Active</span>
                    @else
                        <span class="badge rounded-pill badge-soft-danger">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label">Profile Photo</label>
                    <div>
                        @if ($user->profile_photo_path)
                            <img src="{{ asset('storage/' . ltrim($user->profile_photo_path, '/')) }}"
                                alt="Profile Photo" class="img-thumbnail" style="max-height: 140px;">
                        @else
                            <span class="text-muted">No photo</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <label class="form-label">NRC Front</label>
                    <div>
                        @if ($user->nrc_front_path)
                            <img src="{{ Storage::disk('public')->url($user->nrc_front_path) }}" alt="NRC Front"
                                class="img-thumbnail" style="max-height: 140px;">
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="form-label">NRC Back</label>
                    <div>
                        @if ($user->nrc_back_path)
                            <img src="{{ Storage::disk('public')->url($user->nrc_back_path) }}" alt="NRC Back"
                                class="img-thumbnail" style="max-height: 140px;">
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Supporting Documents</label>
                    @php $docs = is_array($user->documents) ? $user->documents : []; @endphp
                    @if (count($docs))
                        <ul class="mb-0">
                            @foreach ($docs as $path)
                                <li><a href="{{ Storage::disk('public')->url($path) }}"
                                        target="_blank">{{ basename($path) }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="form-control-plaintext text-muted">No documents</div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
