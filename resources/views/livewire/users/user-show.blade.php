<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">

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

        </div>
    </div>
</div>
