<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Name</label>
                        <input class="form-control" type="text" wire:model="name" placeholder="Name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Email</label>
                        <input class="form-control" type="email" wire:model="email" placeholder="Email" required>
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
