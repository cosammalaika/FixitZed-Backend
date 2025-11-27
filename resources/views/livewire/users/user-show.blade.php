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

            @php
                $resolveUrl = function (?string $path) {
                    if (! $path) return null;
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                        return $path;
                    }
                    return route('files.show', ['path' => $path]);
                };
                $docs = is_array($user->documents) ? $user->documents : [];
            @endphp

            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="form-label">Profile Photo</label>
                    <div>
                        @php $profileUrl = $resolveUrl($user->profile_photo_path); @endphp
                        @if ($profileUrl)
                            <a href="{{ $profileUrl }}" target="_blank">
                                <img src="{{ $profileUrl }}"
                                    alt="Profile Photo" class="img-thumbnail" style="max-height: 140px;">
                            </a>
                        @else
                            <span class="text-muted">No photo</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 mb-4">
                    <label class="form-label">NRC Front</label>
                    <div>
                        @php $frontUrl = $resolveUrl($user->nrc_front_path); @endphp
                        @if ($frontUrl)
                            <a href="{{ $frontUrl }}" target="_blank">
                                <img src="{{ $frontUrl }}" alt="NRC Front"
                                    class="img-thumbnail" style="max-height: 140px;">
                            </a>
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="form-label">NRC Back</label>
                    <div>
                        @php $backUrl = $resolveUrl($user->nrc_back_path); @endphp
                        @if ($backUrl)
                            <a href="{{ $backUrl }}" target="_blank">
                                <img src="{{ $backUrl }}" alt="NRC Back"
                                    class="img-thumbnail" style="max-height: 140px;">
                            </a>
                        @else
                            <span class="text-muted">No image</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Supporting Documents</label>
                    @if (count($docs))
                        <ul class="mb-0">
                            @foreach ($docs as $path)
                                @php $url = $resolveUrl($path); @endphp
                                @if ($url)
                                    <li><a href="{{ $url }}" target="_blank">{{ basename($path) }}</a></li>
                                @endif
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
