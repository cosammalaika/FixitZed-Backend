<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
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
                        <label class="form-label" for="default-input">Email</label>
                        <input class="form-control" type="email" wire:model="email" placeholder="Email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Username</label>
                        <input class="form-control" type="text" wire:model="username" placeholder="Username"
                            required>
                    </div>

                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Contact Number</label>
                        <input class="form-control" type="text" wire:model="contact_number"
                            placeholder="Contact Number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Address</label>
                        <input class="form-control" type="text" wire:model="address" placeholder="address" required>
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Password</label>
                        <input class="form-control" type="password" wire:model="password" placeholder="Password"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Confirm Password</label>
                        <input class="form-control" type="password" wire:model="confirm_password"
                            placeholder="Confirm Password" required>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6">
                        <label class="form-label" for="user_type">User Type</label>
                        <select wire:model="user_type" class="form-control" required>
                            <option value="">-- Select User Type --</option>
                            <option value="Customer">Customer</option>
                            <option value="Fixer">Fixer</option>
                            <option value="Admin">Admin</option>
                            <option value="Support">Support</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select wire:model="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>


                <flux:checkbox.group wire:model="roles" label="roles">
                    @foreach ($allRoles as $role)
                        <div class="col-md-4">
                            <div class="form-check form-switch mb-2">
                                <input type="checkbox" class="form-check-input" id="perm-{{ $role->id }}"
                                    value="{{ $role->name }}" wire:model="roles">
                                <label class="form-check-label" for="perm-{{ $role->id }}">
                                    {{ $role->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </flux:checkbox.group>
                <button type="submit" class="btn btn-primary waves-effect waves-light">
                    Submit
                </button>
            </form>
        </div>
    </div>

</div>
